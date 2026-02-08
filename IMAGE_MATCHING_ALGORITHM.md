# Image Matching Algorithm Documentation

This document describes all the image matching algorithms and logic used in the system.

## Overview

The system uses a **multi-layered similarity matching approach** that combines:
1. **Visual Similarity** - Image comparison using perceptual hashing
2. **Text Similarity** - Description and tag matching using multiple string algorithms
3. **Overall Similarity** - Weighted combination of visual and text similarity

---

## 1. Visual Similarity Calculation

### Implementation
- **Library**: `SapientPro\ImageComparator\ImageComparator`
- **Method**: Perceptual hashing algorithm (compares image fingerprints)
- **Returns**: Similarity score (0-100 or 0-1)

### Code Locations
- `app/Http/Controllers/ImageComparisonController.php` - Basic comparison API
- `app/Services/SimilarityNotificationService.php::calculateVisualSimilarity()`
- `app/Http/Controllers/Api/UserItemController.php::calculateImageSimilarity()`

### Algorithm Flow
```php
// Normalize similarity to 0-1 range
$similarity = $imageComparator->compare($image1Path, $image2Path);
$normalizedSimilarity = $similarity > 1 ? $similarity / 100 : $similarity;
```

### Multi-Image Comparison
When comparing items with multiple images:
- Compares **all images** from Item A against **all images** from Item B
- Takes the **maximum similarity** found
- Ensures best match is found even if items have different numbers of images

```php
// From UserItemController::getOtherUsersItems()
foreach ($userItemImages as $userImg) {
    foreach ($otherItemImages as $otherImg) {
        $visualSimilarity = $this->calculateImageSimilarity($userItemPath, $otherItemPath);
        $maxVisualSimilarity = max($maxVisualSimilarity, $visualSimilarity);
    }
}
```

---

## 2. Text Similarity Calculation

### Algorithms Used

The system uses **three string similarity algorithms** combined with weighted averaging:

#### A. Jaro-Winkler Similarity
- **Weight**: 40% (default)
- **Purpose**: Measures similarity based on common characters and prefix matching
- **Best for**: Short strings, names, tags

```php
// Simplified implementation using character frequency
$jaro = ($common1 / $len1 + $common2 / $len2) / 2;
$prefix = // Calculate prefix similarity (max 4 chars)
return $jaro + (0.1 * $prefix * (1 - $jaro));
```

#### B. Levenshtein Similarity
- **Weight**: 30% (default)
- **Purpose**: Measures edit distance (how many changes needed)
- **Best for**: Typos, variations in spelling

```php
$distance = levenshtein($s1, $s2);
$maxLen = max(strlen($s1), strlen($s2));
return 1 - ($distance / $maxLen);
```

#### C. Word Overlap Similarity
- **Weight**: 30% (default)
- **Purpose**: Measures word-level similarity (Jaccard index)
- **Best for**: Descriptions with multiple words

```php
$intersection = array_intersect($words1, $words2);
$union = array_unique(array_merge($words1, $words2));
return count($intersection) / count($union);
```

### Combined Text Similarity
```php
$textSimilarity = ($jaroWinkler * 0.4) + ($levenshtein * 0.3) + ($wordOverlap * 0.3);
```

### Description vs Tags
- Compares both **description** and **tags** separately
- Returns higher score if both are similar
- If only one aspect is similar, returns lower score (50% penalty)

```php
$descriptionSimilarity = calculateTextSimilarityScore($desc1, $desc2);
$tagsSimilarity = calculateTextSimilarityScore($tags1, $tags2);

if ($descriptionSimilarity > 0.5 && $tagsSimilarity > 0.5) {
    return max($descriptionSimilarity, $tagsSimilarity);
}
return max($descriptionSimilarity, $tagsSimilarity) * 0.5; // Penalty
```

---

## 3. Overall Similarity Calculation

### Weighted Combination
```php
$overallSimilarity = ($visualSimilarity * 0.7) + ($textSimilarity * 0.3);
```

**Default Weights:**
- Visual: **70%**
- Text: **30%**

### Minimum Thresholds
Even if overall similarity is high, both components must meet minimums:
- **Visual**: Minimum 60% similarity required
- **Text**: Minimum 30% similarity required

If either threshold is not met, overall score is **reduced by 70%**:
```php
if ($visualSimilarity < 0.6 || $textSimilarity < 0.3) {
    return $overallSimilarity * 0.3; // Severe penalty
}
```

---

## 4. Matching Thresholds

### Different Thresholds for Different Contexts

#### A. Similarity Notification Service
- **Visual Threshold**: **0.8** (80%) - Very strict
- **Purpose**: Only notify users of highly similar items
- **Location**: `config/similarity.php` or `SimilarityNotificationService`

#### B. Claim-Verify Page Matching
- **Visual Threshold**: **0.5** (50%) - More lenient
- **Purpose**: Show all potentially matching items to users
- **Location**: `UserItemController::getOtherUsersItems()`
- **Note**: Ensures items that triggered notifications (≥0.7) will definitely show (≥0.5)

#### C. High Similarity Match
- **Threshold**: **0.75** (75%)
- **Purpose**: Notify users even if item types don't match perfectly
- **Location**: `SimilarityNotificationService::checkAndNotifySimilarities()`

---

## 5. Item Type Matching Rules

### Lost ↔ Found Matching
The system **only matches opposite item types**:
- ✅ **Lost** items match with **Found** items
- ✅ **Found** items match with **Lost** items
- ❌ **Lost** items do NOT match with **Lost** items
- ❌ **Found** items do NOT match with **Found** items

### Implementation
```php
// Skip if both items have the same status
if ($userItemStatus === $otherItemStatus) {
    continue;
}

// Ensure opposite types: Lost ↔ Found
if (!(($userItemStatus === 'lost' && $otherItemStatus === 'found') || 
      ($userItemStatus === 'found' && $otherItemStatus === 'lost'))) {
    continue;
}
```

### Exception: High Similarity
If similarity ≥ 0.75, users are notified even if types match (for potential duplicates).

---

## 6. Matching Flow

### When User Uploads Item
1. **Get all existing items** with opposite status
2. **Compare all images** from new item against all images from existing items
3. **Calculate visual similarity** (max of all comparisons)
4. **Calculate text similarity** (description + tags)
5. **Calculate overall similarity** (weighted average)
6. **Check threshold** (≥0.7 for notifications, ≥0.5 for claim-verify)
7. **Create notifications** for both users if match found

### When User Visits Claim-Verify Page
1. **Get user's reported items** (Lost or Found)
2. **Get all other users' items** (opposite type)
3. **Compare all images** between user items and other items
4. **Calculate similarities** (visual + text)
5. **Filter by threshold** (≥0.5)
6. **Create notifications** if matches found (prevents duplicates)
7. **Return matched items** for display

---

## 7. Configuration

### Config File: `config/similarity.php`
```php
'enabled' => true,
'threshold' => 0.7,  // Overall threshold
'weights' => [
    'visual' => 0.7,
    'text' => 0.3,
],
'thresholds' => [
    'visual' => 0.8,  // Visual threshold
    'text' => 0.3,    // Text threshold
],
'algorithms' => [
    'jaro_winkler_weight' => 0.4,
    'levenshtein_weight' => 0.3,
    'word_overlap_weight' => 0.3,
],
```

### Environment Variables
- `SIMILARITY_ENABLED` - Enable/disable similarity checking
- `SIMILARITY_THRESHOLD` - Overall similarity threshold
- `SIMILARITY_NOTIFICATION_ENABLED` - Enable notifications

---

## 8. Performance Optimizations

### Chunking
- Processes items in **chunks of 50** to avoid memory issues
- Limits to **500 items** maximum per check
- **25-second timeout** to prevent long-running processes

### Early Exit
- Stops processing if execution time exceeds limit
- Skips items without valid file paths
- Skips items with same status (Lost-Lost or Found-Found)

### Caching
- Groups similar images by uploader email
- Prevents duplicate notifications
- Checks for existing notifications before creating new ones

---

## 9. Notification System

### Types of Notifications

#### A. Item Match Found
- **Trigger**: Overall similarity ≥ threshold AND opposite types (Lost ↔ Found)
- **Recipients**: Both users (item owner and matcher)
- **Type**: `item_matched`

#### B. Similar Items Found
- **Trigger**: Overall similarity ≥ threshold but same type OR high similarity (≥0.75)
- **Recipients**: Both users
- **Type**: `item_match` or `similar_item_found`

#### C. No Match Found
- **Trigger**: No similar items found
- **Recipients**: New uploader only
- **Type**: Upload confirmation

### Notification Prevention
- Checks for existing notifications before creating new ones
- Prevents duplicate notifications for same match
- Uses `upload_id` and `matched_item_upload_id` to identify duplicates

---

## 10. Current Thresholds Summary

| Context | Visual Threshold | Text Threshold | Overall Threshold | Notes |
|---------|-----------------|----------------|-------------------|-------|
| **Notification Service** | 0.8 (80%) | 0.3 (30%) | 0.7 (70%) | Strict matching |
| **Claim-Verify Page** | 0.5 (50%) | 0.3 (30%) | 0.5 (50%) | Lenient matching |
| **High Similarity** | 0.75 (75%) | 0.3 (30%) | 0.75 (75%) | Override for same-type matches |
| **Minimum Requirements** | 0.6 (60%) | 0.3 (30%) | - | Both must meet minimums |

---

## 11. Code References

### Main Files
- `app/Services/SimilarityNotificationService.php` - Core similarity logic
- `app/Http/Controllers/Api/UserItemController.php` - Claim-verify matching
- `app/Http/Controllers/ImageComparisonController.php` - Basic comparison API
- `config/similarity.php` - Configuration

### Key Methods
- `calculateVisualSimilarity()` - Visual comparison
- `calculateTextSimilarity()` - Text comparison
- `calculateTextSimilarityScore()` - String algorithms
- `calculateOverallSimilarity()` - Weighted combination
- `jaroWinklerSimilarity()` - Jaro-Winkler algorithm
- `levenshteinSimilarity()` - Levenshtein algorithm
- `wordOverlapSimilarity()` - Word overlap algorithm

---

## 12. Example Calculation

### Scenario
- **Item A**: Lost item with description "Red backpack" and tags ["backpack", "red"]
- **Item B**: Found item with description "Red backpack found" and tags ["backpack", "red", "found"]

### Step 1: Visual Similarity
- Compare all images from Item A vs Item B
- Maximum visual similarity: **0.85** (85%)

### Step 2: Text Similarity
- **Description**: "Red backpack" vs "Red backpack found"
  - Jaro-Winkler: 0.92
  - Levenshtein: 0.88
  - Word Overlap: 0.67
  - Combined: (0.92 × 0.4) + (0.88 × 0.3) + (0.67 × 0.3) = **0.83**

- **Tags**: ["backpack", "red"] vs ["backpack", "red", "found"]
  - Word Overlap: 0.67 (2 common / 3 total)
  - Combined tags: **0.67**

- **Text Similarity**: Both description and tags > 0.5, so: max(0.83, 0.67) = **0.83**

### Step 3: Overall Similarity
- Visual: 0.85 × 0.7 = 0.595
- Text: 0.83 × 0.3 = 0.249
- **Overall**: 0.595 + 0.249 = **0.844** (84.4%)

### Step 4: Threshold Check
- Visual (0.85) ≥ 0.6 ✅
- Text (0.83) ≥ 0.3 ✅
- Overall (0.844) ≥ 0.7 ✅
- **Result**: **MATCH FOUND** - Both users notified!

---

## 13. Debugging

### Logging
The system logs similarity calculations at multiple levels:
- `Log::debug()` - Detailed similarity scores
- `Log::info()` - Match found notifications
- `Log::warning()` - File path issues, skipped items
- `Log::error()` - Comparison failures

### Key Log Messages
- `"Similarity calculation"` - Shows visual, text, and overall scores
- `"Match found on claim-verify"` - Successful match
- `"Similarity below threshold"` - Match didn't meet requirements
- `"Skipping similarity check - no valid images"` - Missing files

---

## Summary

The image matching system uses a sophisticated multi-algorithm approach:
1. **Visual comparison** using perceptual hashing (70% weight)
2. **Text comparison** using 3 string algorithms (30% weight)
3. **Strict thresholds** for notifications (0.7-0.8)
4. **Lenient thresholds** for display (0.5)
5. **Type matching** (Lost ↔ Found only)
6. **Bidirectional notifications** (both users notified)
7. **Performance optimizations** (chunking, timeouts, early exit)

This ensures accurate matching while maintaining good performance and user experience.
