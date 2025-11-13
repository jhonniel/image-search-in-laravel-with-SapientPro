# WebSocket Real-Time Messaging Setup Guide

This guide will help you set up real-time messaging using WebSockets with Laravel Echo and Pusher.

## Prerequisites

- Laravel application with broadcasting configured
- Pusher account (free tier available at https://pusher.com)

## Installation Steps

### 1. Environment Configuration

Add the following environment variables to your `.env` file:

```env
BROADCAST_CONNECTION=pusher

PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

### 2. Get Pusher Credentials

1. Sign up for a free Pusher account at https://pusher.com
2. Create a new app in your Pusher dashboard
3. Copy your App ID, Key, Secret, and Cluster
4. Add them to your `.env` file as shown above

### 3. Build Frontend Assets

Run the following command to build the frontend assets with Vite:

```bash
npm run build
```

Or for development:

```bash
npm run dev
```

### 4. Queue Configuration (Optional but Recommended)

For better performance, you can configure Laravel to use queues for broadcasting:

```env
QUEUE_CONNECTION=database
```

Then run:

```bash
php artisan queue:work
```

## How It Works

### Backend

1. **MessageSent Event** (`app/Events/MessageSent.php`):
   - Implements `ShouldBroadcast` interface
   - Broadcasts to private channels for both sender and receiver
   - Includes full message data with sender/receiver information

2. **ChatController** (`app/Http/Controllers/ChatController.php`):
   - When a message is sent, it broadcasts the `MessageSent` event
   - Uses `broadcast()->toOthers()` to prevent the sender from receiving their own message via WebSocket

3. **Broadcasting Channels** (`routes/channels.php`):
   - Private channel `user.{userId}` is authorized for authenticated users
   - Only the user with matching ID can subscribe to their channel

### Frontend

1. **Laravel Echo Setup** (`resources/js/bootstrap.js`):
   - Initializes Echo with Pusher configuration
   - Sets up authentication endpoint for private channels

2. **Chat View** (`resources/views/user/chat.blade.php`):
   - Listens to private channel `user.{currentUserId}`
   - Receives `.message.sent` events in real-time
   - Updates UI automatically when new messages arrive
   - Updates conversation list and unread counts

## Features

- ✅ Real-time message delivery
- ✅ Automatic UI updates
- ✅ Conversation list updates
- ✅ Unread count updates
- ✅ Message read status
- ✅ No page refresh needed
- ✅ Prevents duplicate messages

## Testing

1. Open two browser windows/tabs
2. Log in as different users in each window
3. Start a conversation between the two users
4. Send a message from one window
5. The message should appear instantly in the other window without refreshing

## Troubleshooting

### Messages not appearing in real-time

1. Check browser console for errors
2. Verify Pusher credentials in `.env`
3. Ensure `npm run build` or `npm run dev` is running
4. Check that broadcasting is enabled: `BROADCAST_CONNECTION=pusher`
5. Verify WebSocket connection in browser DevTools → Network → WS

### Authentication errors

1. Ensure user is authenticated
2. Check that CSRF token is present in the page
3. Verify broadcasting auth route is accessible

### Build errors

1. Run `npm install` to ensure all packages are installed
2. Check that `laravel-echo` and `pusher-js` are in `package.json`
3. Clear Vite cache: `rm -rf node_modules/.vite`

## Alternative: Using Laravel Reverb (Self-Hosted)

If you prefer a self-hosted solution, you can use Laravel Reverb instead of Pusher:

1. Install Reverb: `composer require laravel/reverb`
2. Install Reverb: `php artisan reverb:install`
3. Update `.env` to use `reverb` instead of `pusher`
4. Start Reverb server: `php artisan reverb:start`

## Notes

- The polling mechanism has been removed in favor of WebSockets
- Messages are still saved to the database
- The system gracefully falls back if WebSocket connection fails (messages will still be saved)
- Private channels ensure only authorized users receive messages

