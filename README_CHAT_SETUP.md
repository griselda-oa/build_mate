# Build Mate Chat System - Setup Guide

## Features Implemented

✅ **OpenAI Integration** - AI-powered chat responses  
✅ **Backend Message Storage** - All messages saved to database  
✅ **Real-time Updates** - Polling-based real-time chat  
✅ **Admin Chat Management** - View and manage all chat sessions  
✅ **Context-Aware** - Chat understands page context and user role  
✅ **Building Materials Knowledge** - AI trained on construction materials  

## Setup Instructions

### 1. Database Setup

Run the SQL schema to create chat tables:

```bash
mysql -u root -p buildmate_db < db/chat_schema.sql
```

Or import `db/chat_schema.sql` via phpMyAdmin.

### 2. OpenAI API Key (Optional but Recommended)

Add your OpenAI API key to `.env` file:

```env
OPENAI_API_KEY=sk-your-api-key-here
OPENAI_MODEL=gpt-3.5-turbo
OPENAI_MAX_TOKENS=500
OPENAI_TEMPERATURE=0.7
```

**Note:** If no API key is provided, the system will use a simple keyword-based fallback.

### 3. WebSocket Setup (Optional - For True Real-time)

For true real-time chat with WebSockets, you have two options:

#### Option A: Using Ratchet (PHP WebSocket Server)

1. Install Ratchet via Composer:
```bash
composer require cboden/ratchet
```

2. Create WebSocket server at `websocket/chat-server.php`:
```php
<?php
require __DIR__ . '/../vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class ChatServer implements MessageComponentInterface {
    protected $clients;
    
    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }
    
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
    }
    
    public function onMessage(ConnectionInterface $from, $msg) {
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($msg);
            }
        }
    }
    
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();
    }
}

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new ChatServer()
        )
    ),
    8080
);

$server->run();
```

3. Run WebSocket server:
```bash
php websocket/chat-server.php
```

#### Option B: Using Node.js + Socket.io (Recommended for Production)

1. Install dependencies:
```bash
npm install socket.io express
```

2. Create `websocket/server.js`:
```javascript
const io = require('socket.io')(3000, {
  cors: { origin: "*" }
});

io.on('connection', (socket) => {
  socket.on('chat-message', (data) => {
    io.emit('chat-message', data);
  });
});
```

3. Run:
```bash
node websocket/server.js
```

### 4. Update Chat Widget for WebSockets

If using WebSockets, update `assets/js/chat-widget.js` to connect:

```javascript
// Add to constructor
this.socket = io('http://localhost:3000'); // or your WebSocket URL

// Update sendMessage to emit via WebSocket
this.socket.emit('chat-message', {
    message: message,
    session_id: this.sessionId
});
```

## Current Implementation

The current system uses **HTTP polling** for real-time updates, which works well for most use cases:

- Messages are sent via AJAX POST to `/build_mate/api/chat/send`
- Chat history loads on widget open
- Admin dashboard auto-refreshes every 30 seconds
- All messages are stored in database

## Admin Access

Admins can access chat management at:
- `/build_mate/admin/chat` - Chat dashboard with statistics
- `/build_mate/admin/chat/session/{session_id}` - View individual conversations

## API Endpoints

- `POST /build_mate/api/chat/send` - Send a chat message
- `GET /build_mate/api/chat/history?session_id={id}` - Get chat history
- `GET /build_mate/admin/chat/sessions` - Get all sessions (admin only)

## Features

### Context Awareness
The chat widget automatically detects:
- Current page (supplier, buyer, logistics, admin)
- Page section (pending, kyc, dashboard, products, orders)
- User role and status
- Provides context-aware responses

### Building Materials Knowledge
The AI is trained on:
- Cement types (OPC, PPC, Rapid Hardening)
- Building blocks (4", 6", 8" - hollow/solid)
- Iron rods (various diameters and grades)
- Roofing materials
- Sand, stone, paint, tiles
- Plumbing and electrical supplies

### Message Storage
All messages are stored with:
- User ID (if logged in)
- Session ID
- Role (user/assistant/system)
- Context information
- AI model used
- Token usage
- Response time

## Troubleshooting

### Chat not working?
1. Check browser console for errors
2. Verify database tables exist
3. Check API routes are registered
4. Ensure session is working

### OpenAI not responding?
1. Verify API key in `.env`
2. Check API key is valid
3. Verify you have credits
4. System will fallback to simple responses

### Admin can't see chats?
1. Ensure admin is logged in
2. Check admin routes are registered
3. Verify database has chat data

## Next Steps

- [ ] Add email notifications for admin on new chats
- [ ] Add chat export functionality
- [ ] Add chat analytics dashboard
- [ ] Implement chat ratings/feedback
- [ ] Add multi-language support
- [ ] Add file upload support in chat



