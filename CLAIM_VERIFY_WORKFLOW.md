# Claim & Verify Workflow Documentation

This document explains how the **Claim & Verify** feature works in the system.

---

## Overview

The **Claim & Verify** page (`/claim-verify`) is where users can:
1. **View matched items** - Items from other users that match their reported items
2. **Claim items** - Request to claim a found item (if they have a lost item)
3. **Message owners** - Contact item owners to notify them about matches
4. **Verify claims** - Item owners verify claims made on their items

---

## 1. Page Load Flow

### When User Visits `/claim-verify`

#### Step 1: Frontend Loads
- Page displays loading state
- Calls API endpoint: `GET /api/items/other-users`

#### Step 2: Backend Processing (`getOtherUsersItems()`)

**A. Get User's Reported Items**
```php
$userItems = ImageMetadata::where('uploader_email', $user->email)
    ->orderBy('created_at', 'desc')
    ->get()
    ->groupBy('upload_id');
```

**B. Get All Other Users' Items**
```php
$allOtherItems = ImageMetadata::where('uploader_email', '!=', $user->email)
    ->whereNotNull('uploader_email')
    ->whereNotNull('file_path')
    ->whereNotNull('filename')
    ->orderBy('created_at', 'desc')
    ->get()
    ->groupBy('upload_id');
```

**C. Match Items Using Similarity**
- Compares user's items against all other items
- Only matches **Lost ↔ Found** (opposite types)
- Calculates visual similarity (all images vs all images)
- Calculates text similarity (description + tags)
- Overall similarity: `(visual × 0.7) + (text × 0.3)`
- **Threshold**: 0.5 (50%) - Lower than notification threshold (0.7-0.8)

**D. Create Notifications**
- Creates in-app notifications for **both users** if match found
- Prevents duplicate notifications
- Notification type: `item_matched`

**E. Return Matched Items**
- Returns all matched items with similarity scores
- Includes user's matched item for side-by-side comparison
- Filters out items that don't meet threshold

---

## 2. Display Logic

### Available Items Section

Each matched item shows:
- **Similarity Score** - Percentage match (e.g., "85.3%")
- **Your Reported Item** (left side) - User's item that matched
- **Found Item** (right side) - Other user's item that matched
- **Item Details** - Description, location, tags, images
- **Action Buttons** - Claim Item / Message Owner / View Details

### Item Status Indicators

| Status | Badge | Meaning |
|--------|-------|---------|
| **Lost** | Red badge | User lost this item |
| **Found** | Green badge | User found this item |
| **Pending** | Gray badge | Claim pending verification |
| **Verified** | Blue badge | Claim verified by owner |
| **Rejected** | Red badge | Claim rejected by owner |

---

## 3. Claim Item Flow

### Rules for Claiming

**✅ User CAN Claim If:**
- User has a **Lost** item
- Item to claim is **Found**
- Item is not already claimed/verified
- Item doesn't have pending claim

**❌ User CANNOT Claim If:**
- User has a **Found** item (can only message)
- Item is already claimed/verified
- Item has pending claim
- User is trying to claim their own item

### Claim Process

#### Step 1: User Clicks "Claim Item"
```javascript
async function claimItem(uploadId) {
    // Confirmation dialog
    if (!confirm('Are you sure you want to claim this item?')) {
        return;
    }
    
    // API call
    const response = await fetch(`/api/items/${uploadId}/claim`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json'
        }
    });
}
```

#### Step 2: Backend Validation (`claimItem()`)

**A. Check Authentication**
```php
$user = Auth::user();
if (!$user) {
    return error('User not authenticated');
}
```

**B. Check Item Exists**
```php
$itemExists = ImageMetadata::where('upload_id', $uploadId)->exists();
if (!$itemExists) {
    return error('Item not found');
}
```

**C. Check User Has Lost Item**
```php
$userItems = ImageMetadata::where('uploader_email', $user->email)->get();
$hasLostItem = false;
foreach ($userItems as $userItem) {
    if ($userItem->status === 'lost' && $itemToClaim->status === 'found') {
        $hasLostItem = true;
        break;
    }
}
if (!$hasLostItem) {
    return error('You can only claim items if you have a lost item');
}
```

**D. Check Claim Status**
```php
// Check if already pending
$pendingClaim = ImageMetadata::where('upload_id', $uploadId)
    ->where('claim_verification_status', 'pending')
    ->exists();
if ($pendingClaim) {
    return error('This item already has a pending claim');
}

// Check if already verified
$verifiedClaim = ImageMetadata::where('upload_id', $uploadId)
    ->where('is_claimed', true)
    ->where('claim_verification_status', 'verified')
    ->exists();
if ($verifiedClaim) {
    return error('This item has already been claimed and verified');
}
```

**E. Create Pending Claim**
```php
ImageMetadata::where('upload_id', $uploadId)
    ->where('uploader_email', '!=', $user->email)
    ->update([
        'claimed_by_email' => $user->email,
        'claimed_at' => now(),
        'claim_verification_status' => 'pending'
        // Note: is_claimed remains false until owner verifies
    ]);
```

**F. Send Notification Message**
```php
$claimMessage = "Hello! I believe I found your {$item->status} item. 
                 Please verify if this item belongs to me so I can return it to you.";

Message::create([
    'sender_id' => $user->id,
    'receiver_id' => $itemOwner->id,
    'message' => $claimMessage,
    'item_upload_id' => $uploadId,
    'item_context' => json_encode($itemContext),
]);
```

**G. Create In-App Notification**
```php
Notification::create([
    'user_id' => $itemOwner->id,
    'type' => 'item_claimed',
    'title' => 'Someone claimed your item',
    'message' => $user->name . ' requested to claim your ' . $item->status . '.',
    'data' => [
        'upload_id' => $uploadId,
        'claimer_id' => $user->id,
        'claimer_name' => $user->name,
    ],
]);
```

**H. Send Email Notification** (if enabled)
```php
Mail::to($itemOwnerEmail)->send(new ItemClaimedNotification([
    'item_owner_name' => $itemOwner->name,
    'claimer_name' => $user->name,
    'item_description' => $item->description,
    'item_type' => $item->status,
    'upload_id' => $uploadId,
    'claim_link' => url('/user/pending-claims'),
]));
```

#### Step 3: Redirect to Chat
```javascript
if (data.success) {
    showToast('Item claimed successfully! Redirecting to chat...', 'success');
    
    // Redirect to chat with item owner
    window.location.href = `/chat?user=${data.owner_id}&item=${data.upload_id}`;
}
```

---

## 4. Verify Claim Flow

### When Item Owner Verifies Claim

#### Step 1: Owner Visits Pending Claims Page
- Page: `/user/pending-claims`
- Shows all items with `claim_verification_status = 'pending'`
- Owner can see who claimed their item

#### Step 2: Owner Clicks "Verify Claim"
```javascript
async function verifyClaim(uploadId) {
    const response = await fetch(`/api/items/${uploadId}/verify-claim`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json'
        }
    });
}
```

#### Step 3: Backend Verification (`verifyClaim()`)

**A. Check Item Belongs to Owner**
```php
$items = ImageMetadata::where('upload_id', $uploadId)
    ->where('uploader_email', $user->email)
    ->where('claim_verification_status', 'pending')
    ->get();
```

**B. Mark as Verified**
```php
ImageMetadata::where('upload_id', $uploadId)
    ->where('uploader_email', $user->email)
    ->update([
        'is_claimed' => true, // Now officially claimed
        'claim_verification_status' => 'verified',
        'claim_verified_at' => now()
    ]);
```

**C. Update Messages**
```php
// Update all messages with this item to mark as verified
Message::where('item_upload_id', $uploadId)
    ->whereNotNull('item_context')
    ->get()
    ->each(function($message) {
        $existingContext = json_decode($message->item_context, true);
        if ($existingContext) {
            $existingContext['claim_status'] = 'verified';
            $message->update(['item_context' => json_encode($existingContext)]);
        }
    });
```

**D. Send Confirmation Message**
```php
Message::create([
    'sender_id' => $user->id, // Owner
    'receiver_id' => $claimer->id, // Claimer
    'message' => "Thank you! I've verified that the item belongs to you. Let's arrange the return.",
    'item_upload_id' => $uploadId,
]);
```

**E. Broadcast Event**
```php
broadcast(new ItemClaimVerified($uploadId, $claimer->id, $user->id))->toOthers();
```

**F. Auto-Verify User** (if 50+ verified returns)
```php
$verifiedReturns = ImageMetadata::where('uploader_email', $user->email)
    ->where('claim_verification_status', 'verified')
    ->select('upload_id')
    ->distinct()
    ->count();

if ($verifiedReturns >= 50 && !$user->is_verified) {
    $user->is_verified = true;
    $user->save();
}
```

---

## 5. Cancel Claim Flow

### When User Cancels Their Claim

#### Step 1: User Clicks "Cancel Claim"
```javascript
async function cancelClaim(uploadId) {
    if (!confirm('Are you sure you want to cancel this claim?')) {
        return;
    }
    
    const response = await fetch(`/api/items/${uploadId}/cancel-claim`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json'
        }
    });
}
```

#### Step 2: Backend Cancellation
```php
// Remove claim status
ImageMetadata::where('upload_id', $uploadId)
    ->where('claimed_by_email', $user->email)
    ->update([
        'claimed_by_email' => null,
        'claimed_at' => null,
        'claim_verification_status' => null,
        'is_claimed' => false
    ]);
```

---

## 6. Reject Claim Flow

### When Owner Rejects Claim

#### Step 1: Owner Clicks "Reject Claim"
```javascript
async function rejectClaim(uploadId) {
    const response = await fetch(`/api/items/${uploadId}/reject-claim`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json'
        }
    });
}
```

#### Step 2: Backend Rejection
```php
ImageMetadata::where('upload_id', $uploadId)
    ->where('uploader_email', $user->email)
    ->update([
        'claim_verification_status' => 'rejected',
        'claim_rejected_at' => now()
        // Note: is_claimed remains false
    ]);
```

**Note**: Rejected items can be claimed again by the same or different user.

---

## 7. Button Logic on Claim-Verify Page

### Claim Item Button
```javascript
// User with LOST item can CLAIM FOUND items
const userItemType = item.user_matched_item?.item_type || '';
const matchedItemType = item.item_type || '';
const canClaim = userItemType === 'lost' && matchedItemType === 'found';

if (item.user_has_claimed) {
    // Show "Cancel Claim" button
} else if (item.claim_status === 'verified') {
    // Show "Already Claimed & Verified" (disabled)
} else if (item.claim_status === 'pending') {
    // Show "Pending Verification" (disabled)
} else if (!canClaim) {
    // Show "Message to Notify" (disabled) - User has Found item
} else {
    // Show "Claim Item" button - User has Lost item
}
```

### Message Owner Button
- Always visible for Found items
- Opens chat with item owner
- Includes item context in chat

---

## 8. Database Schema

### ImageMetadata Table Fields

| Field | Type | Description |
|-------|------|-------------|
| `upload_id` | string | Unique identifier for item group |
| `uploader_email` | string | Email of item owner |
| `claimed_by_email` | string | Email of user who claimed |
| `is_claimed` | boolean | Whether item is officially claimed |
| `claim_verification_status` | enum | `pending`, `verified`, `rejected`, `null` |
| `claimed_at` | timestamp | When claim was made |
| `claim_verified_at` | timestamp | When claim was verified |
| `claim_rejected_at` | timestamp | When claim was rejected |

### Claim Status Flow

```
NULL (no claim)
    ↓
pending (claim made)
    ↓
verified (owner verified) OR rejected (owner rejected)
    ↓
If rejected: Can be claimed again (back to pending)
If verified: Item is officially claimed (is_claimed = true)
```

---

## 9. Notification Types

### Item Matched (`item_matched`)
- **Trigger**: Similarity match found on claim-verify page
- **Recipients**: Both users (item owner and matcher)
- **Message**: "Found item matches your lost item! (Similarity: 85%)"

### Item Claimed (`item_claimed`)
- **Trigger**: User claims an item
- **Recipients**: Item owner only
- **Message**: "John Doe requested to claim your found item."

### Claim Verified (`claim_verified`)
- **Trigger**: Owner verifies claim
- **Recipients**: Claimer only
- **Message**: "Your claim has been verified!"

---

## 10. Real-Time Updates

### Broadcasting Events

**ItemClaimed Event**
```php
broadcast(new ItemClaimed($uploadId, $claimerId, $ownerId, 'pending'))->toOthers();
```

**ItemClaimVerified Event**
```php
broadcast(new ItemClaimVerified($uploadId, $claimerId, $ownerId))->toOthers();
```

**ItemDeleted Event**
```php
broadcast(new ItemDeleted($uploadId))->toOthers();
```

These events update the UI in real-time without page refresh.

---

## 11. Similarity Matching on Claim-Verify

### Every Page Visit Triggers Matching

When user visits `/claim-verify`:
1. **Gets user's reported items**
2. **Gets all other users' items**
3. **Compares all images** (user's items vs other items)
4. **Calculates similarity** (visual + text)
5. **Filters by threshold** (≥0.5)
6. **Creates notifications** (if match found and not duplicate)
7. **Returns matched items** for display

### Why Lower Threshold (0.5)?

- **Notification Service**: Uses 0.7-0.8 (strict)
- **Claim-Verify Page**: Uses 0.5 (lenient)
- **Reason**: Ensures items that triggered notifications (≥0.7) will definitely show (≥0.5)
- **Benefit**: Users see all potentially matching items, not just high-confidence matches

---

## 12. Example Scenarios

### Scenario 1: Lost Item Owner Claims Found Item

1. **User A** reports Lost item: "Red backpack"
2. **User B** reports Found item: "Red backpack found"
3. **System** matches items (similarity: 85%)
4. **User A** visits `/claim-verify`
5. **User A** sees User B's Found item
6. **User A** clicks "Claim Item"
7. **System** creates pending claim
8. **User B** receives notification
9. **User B** visits `/user/pending-claims`
10. **User B** clicks "Verify Claim"
11. **System** marks item as verified
12. **User A** receives confirmation
13. **Both users** chat to arrange return

### Scenario 2: Found Item Owner Messages Lost Item Owner

1. **User A** reports Found item: "Black wallet"
2. **User B** reports Lost item: "Black wallet lost"
3. **System** matches items (similarity: 90%)
4. **User A** visits `/claim-verify`
5. **User A** sees User B's Lost item
6. **User A** clicks "Message Owner" (cannot claim - has Found item)
7. **System** opens chat with item context
8. **User A** sends message: "I found your wallet!"
9. **User B** receives notification
10. **User B** can then claim User A's Found item

---

## 13. API Endpoints

### GET `/api/items/other-users`
- **Purpose**: Get matched items for claim-verify page
- **Returns**: Array of matched items with similarity scores
- **Triggers**: Similarity matching and notifications

### POST `/api/items/{uploadId}/claim`
- **Purpose**: Claim an item
- **Returns**: Success message with owner_id and upload_id
- **Side Effects**: Creates pending claim, sends notifications

### POST `/api/items/{uploadId}/verify-claim`
- **Purpose**: Verify a claim
- **Returns**: Success message
- **Side Effects**: Marks item as verified, sends confirmation

### POST `/api/items/{uploadId}/cancel-claim`
- **Purpose**: Cancel a claim
- **Returns**: Success message
- **Side Effects**: Removes claim status

### POST `/api/items/{uploadId}/reject-claim`
- **Purpose**: Reject a claim
- **Returns**: Success message
- **Side Effects**: Marks claim as rejected

---

## 14. Security & Validation

### Claim Validation Rules

1. **Authentication**: User must be logged in
2. **Ownership**: Cannot claim own items
3. **Item Type**: Lost items can only claim Found items
4. **Status Check**: Cannot claim already verified items
5. **Pending Check**: Cannot claim items with pending claims
6. **Rejection**: Rejected items can be claimed again

### Authorization Checks

```php
// Check user owns item (for verification)
$items = ImageMetadata::where('upload_id', $uploadId)
    ->where('uploader_email', $user->email)
    ->where('claim_verification_status', 'pending')
    ->get();

if ($items->isEmpty()) {
    return error('Item not found or claim already processed');
}
```

---

## Summary

The **Claim & Verify** system provides a complete workflow for:
1. **Finding matches** - Automatic similarity matching
2. **Claiming items** - Lost item owners can claim Found items
3. **Verifying claims** - Item owners verify claims
4. **Messaging** - Communication between users
5. **Notifications** - Real-time alerts for matches and claims

The system ensures:
- ✅ Only Lost ↔ Found matching
- ✅ Bidirectional notifications
- ✅ Secure claim validation
- ✅ Real-time updates
- ✅ Duplicate prevention
- ✅ User-friendly interface
