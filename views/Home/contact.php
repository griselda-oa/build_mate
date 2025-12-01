<?php
/**
 * Contact Page with Live Chat
 */
$title = $title ?? 'Contact Us';
?>

<div class="container my-5">
    <div class="mb-3">
        <a href="<?= \App\View::url('/') ?>" class="back-button">
            <i class="icon-arrow-left"></i>
            <span>Back to Home</span>
        </a>
    </div>
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold mb-3">Get in Touch</h1>
                <p class="lead text-muted">We're here to help! Reach out to us through any of the channels below.</p>
            </div>

            <!-- Contact Information Cards -->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-telephone-fill text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="card-title">Phone</h5>
                            <p class="card-text text-muted">
                                <a href="tel:+233596211352" class="text-decoration-none">+233 596 211 352</a>
                            </p>
                            <small class="text-muted">Mon - Fri, 8AM - 6PM</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-envelope-fill text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="card-title">Email</h5>
                            <p class="card-text text-muted">
                                <a href="mailto:support@buildmate.com" class="text-decoration-none">support@buildmate.com</a>
                            </p>
                            <small class="text-muted">We respond within 24 hours</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-geo-alt-fill text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="card-title">Location</h5>
                            <p class="card-text text-muted">Accra, Ghana</p>
                            <small class="text-muted">Serving all of Ghana</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Live Chat Widget -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-chat-dots"></i> Live Chat Support
                    </h4>
                </div>
                <div class="card-body">
                    <div id="chat-container" style="min-height: 400px; max-height: 500px; overflow-y: auto; background: #f8f9fa; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                        <div id="chat-messages">
                            <div class="chat-message bot-message mb-3">
                                <div class="d-flex align-items-start">
                                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <i class="bi bi-robot"></i>
                                    </div>
                                    <div class="message-content bg-white p-3 rounded shadow-sm" style="max-width: 75%;">
                                        <p class="mb-0">Hello! 👋 Welcome to Build Mate support. How can I help you today?</p>
                                        <small class="text-muted">Just now</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <form id="chat-form" class="d-flex gap-2">
                        <input type="text" id="chat-input" class="form-control" placeholder="Type your message here..." autocomplete="off">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Send
                        </button>
                    </form>
                    <div id="chat-status" class="mt-2 text-muted small">
                        <i class="bi bi-circle-fill text-success"></i> Online
                    </div>
                </div>
            </div>

            <!-- Contact Form (Alternative) -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-envelope"></i> Send us a Message
                    </h5>
                </div>
                <div class="card-body">
                    <form id="contact-form" method="POST" action="<?= \App\View::url('/') ?>contact">
                        <?= \App\Csrf::field() ?>
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.chat-message {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.user-message {
    justify-content: flex-end;
}

.user-message .message-content {
    background: #007bff !important;
    color: white;
}

.user-message .message-content small {
    color: rgba(255, 255, 255, 0.8) !important;
}

#chat-container {
    scroll-behavior: smooth;
}

#chat-container::-webkit-scrollbar {
    width: 6px;
}

#chat-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

#chat-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

#chat-container::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const chatMessages = document.getElementById('chat-messages');
    const chatContainer = document.getElementById('chat-container');
    const chatStatus = document.getElementById('chat-status');

    // Simple AI chat responses (you can replace this with actual AI integration)
    const responses = {
        'hello': 'Hello! How can I help you with Build Mate today?',
        'hi': 'Hi there! What can I assist you with?',
        'help': 'I can help you with:\n• Product inquiries\n• Order tracking\n• Supplier applications\n• Account issues\n• Payment questions\n\nWhat do you need help with?',
        'order': 'For order inquiries, please provide your order number or contact our support team at support@buildmate.com or call +233 596 211 352.',
        'supplier': 'To become a supplier, visit our supplier application page at <?= \App\View::url('/') ?>supplier/apply. You\'ll need to submit your business documents for verification.',
        'payment': 'We accept payments via Paystack. All transactions are secure and encrypted. If you have payment issues, contact support@buildmate.com.',
        'default': 'Thank you for your message! Our support team will get back to you soon. For immediate assistance, please call +233 596 211 352 or email support@buildmate.com.'
    };

    function addMessage(text, isUser = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${isUser ? 'user-message' : 'bot-message'} mb-3`;
        
        const messageContent = `
            <div class="d-flex align-items-start ${isUser ? 'flex-row-reverse' : ''}">
                <div class="avatar ${isUser ? 'bg-primary' : 'bg-secondary'} text-white rounded-circle d-flex align-items-center justify-content-center ${isUser ? 'ms-3' : 'me-3'}" style="width: 40px; height: 40px;">
                    <i class="bi ${isUser ? 'bi-person' : 'bi-robot'}"></i>
                </div>
                <div class="message-content ${isUser ? 'bg-primary text-white' : 'bg-white'} p-3 rounded shadow-sm" style="max-width: 75%;">
                    <p class="mb-0">${text.replace(/\n/g, '<br>')}</p>
                    <small class="${isUser ? 'text-white-50' : 'text-muted'}">${new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</small>
                </div>
            </div>
        `;
        
        messageDiv.innerHTML = messageContent;
        chatMessages.appendChild(messageDiv);
        
        // Scroll to bottom
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    function getResponse(userMessage) {
        const lowerMessage = userMessage.toLowerCase().trim();
        
        // Check for keywords
        for (const [keyword, response] of Object.entries(responses)) {
            if (keyword !== 'default' && lowerMessage.includes(keyword)) {
                return response;
            }
        }
        
        return responses.default;
    }

    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const message = chatInput.value.trim();
        if (!message) return;
        
        // Add user message
        addMessage(message, true);
        chatInput.value = '';
        
        // Show typing indicator
        chatStatus.innerHTML = '<i class="bi bi-circle-fill text-warning"></i> Typing...';
        
        // Simulate AI response delay
        setTimeout(() => {
            const response = getResponse(message);
            addMessage(response, false);
            chatStatus.innerHTML = '<i class="bi bi-circle-fill text-success"></i> Online';
        }, 1000 + Math.random() * 1000); // 1-2 second delay
    });

    // Auto-focus chat input
    chatInput.focus();
});
</script>




 * Contact Page with Live Chat
 */
$title = $title ?? 'Contact Us';
?>

<div class="container my-5">
    <div class="mb-3">
        <a href="<?= \App\View::url('/') ?>" class="back-button">
            <i class="icon-arrow-left"></i>
            <span>Back to Home</span>
        </a>
    </div>
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold mb-3">Get in Touch</h1>
                <p class="lead text-muted">We're here to help! Reach out to us through any of the channels below.</p>
            </div>

            <!-- Contact Information Cards -->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-telephone-fill text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="card-title">Phone</h5>
                            <p class="card-text text-muted">
                                <a href="tel:+233596211352" class="text-decoration-none">+233 596 211 352</a>
                            </p>
                            <small class="text-muted">Mon - Fri, 8AM - 6PM</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-envelope-fill text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="card-title">Email</h5>
                            <p class="card-text text-muted">
                                <a href="mailto:support@buildmate.com" class="text-decoration-none">support@buildmate.com</a>
                            </p>
                            <small class="text-muted">We respond within 24 hours</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-geo-alt-fill text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="card-title">Location</h5>
                            <p class="card-text text-muted">Accra, Ghana</p>
                            <small class="text-muted">Serving all of Ghana</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Live Chat Widget -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-chat-dots"></i> Live Chat Support
                    </h4>
                </div>
                <div class="card-body">
                    <div id="chat-container" style="min-height: 400px; max-height: 500px; overflow-y: auto; background: #f8f9fa; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                        <div id="chat-messages">
                            <div class="chat-message bot-message mb-3">
                                <div class="d-flex align-items-start">
                                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <i class="bi bi-robot"></i>
                                    </div>
                                    <div class="message-content bg-white p-3 rounded shadow-sm" style="max-width: 75%;">
                                        <p class="mb-0">Hello! 👋 Welcome to Build Mate support. How can I help you today?</p>
                                        <small class="text-muted">Just now</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <form id="chat-form" class="d-flex gap-2">
                        <input type="text" id="chat-input" class="form-control" placeholder="Type your message here..." autocomplete="off">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Send
                        </button>
                    </form>
                    <div id="chat-status" class="mt-2 text-muted small">
                        <i class="bi bi-circle-fill text-success"></i> Online
                    </div>
                </div>
            </div>

            <!-- Contact Form (Alternative) -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-envelope"></i> Send us a Message
                    </h5>
                </div>
                <div class="card-body">
                    <form id="contact-form" method="POST" action="<?= \App\View::url('/') ?>contact">
                        <?= \App\Csrf::field() ?>
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.chat-message {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.user-message {
    justify-content: flex-end;
}

.user-message .message-content {
    background: #007bff !important;
    color: white;
}

.user-message .message-content small {
    color: rgba(255, 255, 255, 0.8) !important;
}

#chat-container {
    scroll-behavior: smooth;
}

#chat-container::-webkit-scrollbar {
    width: 6px;
}

#chat-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

#chat-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

#chat-container::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const chatMessages = document.getElementById('chat-messages');
    const chatContainer = document.getElementById('chat-container');
    const chatStatus = document.getElementById('chat-status');

    // Simple AI chat responses (you can replace this with actual AI integration)
    const responses = {
        'hello': 'Hello! How can I help you with Build Mate today?',
        'hi': 'Hi there! What can I assist you with?',
        'help': 'I can help you with:\n• Product inquiries\n• Order tracking\n• Supplier applications\n• Account issues\n• Payment questions\n\nWhat do you need help with?',
        'order': 'For order inquiries, please provide your order number or contact our support team at support@buildmate.com or call +233 596 211 352.',
        'supplier': 'To become a supplier, visit our supplier application page at <?= \App\View::url('/') ?>supplier/apply. You\'ll need to submit your business documents for verification.',
        'payment': 'We accept payments via Paystack. All transactions are secure and encrypted. If you have payment issues, contact support@buildmate.com.',
        'default': 'Thank you for your message! Our support team will get back to you soon. For immediate assistance, please call +233 596 211 352 or email support@buildmate.com.'
    };

    function addMessage(text, isUser = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${isUser ? 'user-message' : 'bot-message'} mb-3`;
        
        const messageContent = `
            <div class="d-flex align-items-start ${isUser ? 'flex-row-reverse' : ''}">
                <div class="avatar ${isUser ? 'bg-primary' : 'bg-secondary'} text-white rounded-circle d-flex align-items-center justify-content-center ${isUser ? 'ms-3' : 'me-3'}" style="width: 40px; height: 40px;">
                    <i class="bi ${isUser ? 'bi-person' : 'bi-robot'}"></i>
                </div>
                <div class="message-content ${isUser ? 'bg-primary text-white' : 'bg-white'} p-3 rounded shadow-sm" style="max-width: 75%;">
                    <p class="mb-0">${text.replace(/\n/g, '<br>')}</p>
                    <small class="${isUser ? 'text-white-50' : 'text-muted'}">${new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</small>
                </div>
            </div>
        `;
        
        messageDiv.innerHTML = messageContent;
        chatMessages.appendChild(messageDiv);
        
        // Scroll to bottom
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    function getResponse(userMessage) {
        const lowerMessage = userMessage.toLowerCase().trim();
        
        // Check for keywords
        for (const [keyword, response] of Object.entries(responses)) {
            if (keyword !== 'default' && lowerMessage.includes(keyword)) {
                return response;
            }
        }
        
        return responses.default;
    }

    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const message = chatInput.value.trim();
        if (!message) return;
        
        // Add user message
        addMessage(message, true);
        chatInput.value = '';
        
        // Show typing indicator
        chatStatus.innerHTML = '<i class="bi bi-circle-fill text-warning"></i> Typing...';
        
        // Simulate AI response delay
        setTimeout(() => {
            const response = getResponse(message);
            addMessage(response, false);
            chatStatus.innerHTML = '<i class="bi bi-circle-fill text-success"></i> Online';
        }, 1000 + Math.random() * 1000); // 1-2 second delay
    });

    // Auto-focus chat input
    chatInput.focus();
});
</script>


 * Contact Page with Live Chat
 */
$title = $title ?? 'Contact Us';
?>

<div class="container my-5">
    <div class="mb-3">
        <a href="<?= \App\View::url('/') ?>" class="back-button">
            <i class="icon-arrow-left"></i>
            <span>Back to Home</span>
        </a>
    </div>
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold mb-3">Get in Touch</h1>
                <p class="lead text-muted">We're here to help! Reach out to us through any of the channels below.</p>
            </div>

            <!-- Contact Information Cards -->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-telephone-fill text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="card-title">Phone</h5>
                            <p class="card-text text-muted">
                                <a href="tel:+233596211352" class="text-decoration-none">+233 596 211 352</a>
                            </p>
                            <small class="text-muted">Mon - Fri, 8AM - 6PM</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-envelope-fill text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="card-title">Email</h5>
                            <p class="card-text text-muted">
                                <a href="mailto:support@buildmate.com" class="text-decoration-none">support@buildmate.com</a>
                            </p>
                            <small class="text-muted">We respond within 24 hours</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-geo-alt-fill text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="card-title">Location</h5>
                            <p class="card-text text-muted">Accra, Ghana</p>
                            <small class="text-muted">Serving all of Ghana</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Live Chat Widget -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-chat-dots"></i> Live Chat Support
                    </h4>
                </div>
                <div class="card-body">
                    <div id="chat-container" style="min-height: 400px; max-height: 500px; overflow-y: auto; background: #f8f9fa; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                        <div id="chat-messages">
                            <div class="chat-message bot-message mb-3">
                                <div class="d-flex align-items-start">
                                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <i class="bi bi-robot"></i>
                                    </div>
                                    <div class="message-content bg-white p-3 rounded shadow-sm" style="max-width: 75%;">
                                        <p class="mb-0">Hello! 👋 Welcome to Build Mate support. How can I help you today?</p>
                                        <small class="text-muted">Just now</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <form id="chat-form" class="d-flex gap-2">
                        <input type="text" id="chat-input" class="form-control" placeholder="Type your message here..." autocomplete="off">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Send
                        </button>
                    </form>
                    <div id="chat-status" class="mt-2 text-muted small">
                        <i class="bi bi-circle-fill text-success"></i> Online
                    </div>
                </div>
            </div>

            <!-- Contact Form (Alternative) -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-envelope"></i> Send us a Message
                    </h5>
                </div>
                <div class="card-body">
                    <form id="contact-form" method="POST" action="<?= \App\View::url('/') ?>contact">
                        <?= \App\Csrf::field() ?>
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.chat-message {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.user-message {
    justify-content: flex-end;
}

.user-message .message-content {
    background: #007bff !important;
    color: white;
}

.user-message .message-content small {
    color: rgba(255, 255, 255, 0.8) !important;
}

#chat-container {
    scroll-behavior: smooth;
}

#chat-container::-webkit-scrollbar {
    width: 6px;
}

#chat-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

#chat-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

#chat-container::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const chatMessages = document.getElementById('chat-messages');
    const chatContainer = document.getElementById('chat-container');
    const chatStatus = document.getElementById('chat-status');

    // Simple AI chat responses (you can replace this with actual AI integration)
    const responses = {
        'hello': 'Hello! How can I help you with Build Mate today?',
        'hi': 'Hi there! What can I assist you with?',
        'help': 'I can help you with:\n• Product inquiries\n• Order tracking\n• Supplier applications\n• Account issues\n• Payment questions\n\nWhat do you need help with?',
        'order': 'For order inquiries, please provide your order number or contact our support team at support@buildmate.com or call +233 596 211 352.',
        'supplier': 'To become a supplier, visit our supplier application page at <?= \App\View::url('/') ?>supplier/apply. You\'ll need to submit your business documents for verification.',
        'payment': 'We accept payments via Paystack. All transactions are secure and encrypted. If you have payment issues, contact support@buildmate.com.',
        'default': 'Thank you for your message! Our support team will get back to you soon. For immediate assistance, please call +233 596 211 352 or email support@buildmate.com.'
    };

    function addMessage(text, isUser = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${isUser ? 'user-message' : 'bot-message'} mb-3`;
        
        const messageContent = `
            <div class="d-flex align-items-start ${isUser ? 'flex-row-reverse' : ''}">
                <div class="avatar ${isUser ? 'bg-primary' : 'bg-secondary'} text-white rounded-circle d-flex align-items-center justify-content-center ${isUser ? 'ms-3' : 'me-3'}" style="width: 40px; height: 40px;">
                    <i class="bi ${isUser ? 'bi-person' : 'bi-robot'}"></i>
                </div>
                <div class="message-content ${isUser ? 'bg-primary text-white' : 'bg-white'} p-3 rounded shadow-sm" style="max-width: 75%;">
                    <p class="mb-0">${text.replace(/\n/g, '<br>')}</p>
                    <small class="${isUser ? 'text-white-50' : 'text-muted'}">${new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</small>
                </div>
            </div>
        `;
        
        messageDiv.innerHTML = messageContent;
        chatMessages.appendChild(messageDiv);
        
        // Scroll to bottom
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    function getResponse(userMessage) {
        const lowerMessage = userMessage.toLowerCase().trim();
        
        // Check for keywords
        for (const [keyword, response] of Object.entries(responses)) {
            if (keyword !== 'default' && lowerMessage.includes(keyword)) {
                return response;
            }
        }
        
        return responses.default;
    }

    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const message = chatInput.value.trim();
        if (!message) return;
        
        // Add user message
        addMessage(message, true);
        chatInput.value = '';
        
        // Show typing indicator
        chatStatus.innerHTML = '<i class="bi bi-circle-fill text-warning"></i> Typing...';
        
        // Simulate AI response delay
        setTimeout(() => {
            const response = getResponse(message);
            addMessage(response, false);
            chatStatus.innerHTML = '<i class="bi bi-circle-fill text-success"></i> Online';
        }, 1000 + Math.random() * 1000); // 1-2 second delay
    });

    // Auto-focus chat input
    chatInput.focus();
});
</script>




 * Contact Page with Live Chat
 */
$title = $title ?? 'Contact Us';
?>

<div class="container my-5">
    <div class="mb-3">
        <a href="<?= \App\View::url('/') ?>" class="back-button">
            <i class="icon-arrow-left"></i>
            <span>Back to Home</span>
        </a>
    </div>
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold mb-3">Get in Touch</h1>
                <p class="lead text-muted">We're here to help! Reach out to us through any of the channels below.</p>
            </div>

            <!-- Contact Information Cards -->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-telephone-fill text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="card-title">Phone</h5>
                            <p class="card-text text-muted">
                                <a href="tel:+233596211352" class="text-decoration-none">+233 596 211 352</a>
                            </p>
                            <small class="text-muted">Mon - Fri, 8AM - 6PM</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-envelope-fill text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="card-title">Email</h5>
                            <p class="card-text text-muted">
                                <a href="mailto:support@buildmate.com" class="text-decoration-none">support@buildmate.com</a>
                            </p>
                            <small class="text-muted">We respond within 24 hours</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-geo-alt-fill text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="card-title">Location</h5>
                            <p class="card-text text-muted">Accra, Ghana</p>
                            <small class="text-muted">Serving all of Ghana</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Live Chat Widget -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-chat-dots"></i> Live Chat Support
                    </h4>
                </div>
                <div class="card-body">
                    <div id="chat-container" style="min-height: 400px; max-height: 500px; overflow-y: auto; background: #f8f9fa; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                        <div id="chat-messages">
                            <div class="chat-message bot-message mb-3">
                                <div class="d-flex align-items-start">
                                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <i class="bi bi-robot"></i>
                                    </div>
                                    <div class="message-content bg-white p-3 rounded shadow-sm" style="max-width: 75%;">
                                        <p class="mb-0">Hello! 👋 Welcome to Build Mate support. How can I help you today?</p>
                                        <small class="text-muted">Just now</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <form id="chat-form" class="d-flex gap-2">
                        <input type="text" id="chat-input" class="form-control" placeholder="Type your message here..." autocomplete="off">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Send
                        </button>
                    </form>
                    <div id="chat-status" class="mt-2 text-muted small">
                        <i class="bi bi-circle-fill text-success"></i> Online
                    </div>
                </div>
            </div>

            <!-- Contact Form (Alternative) -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-envelope"></i> Send us a Message
                    </h5>
                </div>
                <div class="card-body">
                    <form id="contact-form" method="POST" action="<?= \App\View::url('/') ?>contact">
                        <?= \App\Csrf::field() ?>
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.chat-message {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.user-message {
    justify-content: flex-end;
}

.user-message .message-content {
    background: #007bff !important;
    color: white;
}

.user-message .message-content small {
    color: rgba(255, 255, 255, 0.8) !important;
}

#chat-container {
    scroll-behavior: smooth;
}

#chat-container::-webkit-scrollbar {
    width: 6px;
}

#chat-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

#chat-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

#chat-container::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const chatMessages = document.getElementById('chat-messages');
    const chatContainer = document.getElementById('chat-container');
    const chatStatus = document.getElementById('chat-status');

    // Simple AI chat responses (you can replace this with actual AI integration)
    const responses = {
        'hello': 'Hello! How can I help you with Build Mate today?',
        'hi': 'Hi there! What can I assist you with?',
        'help': 'I can help you with:\n• Product inquiries\n• Order tracking\n• Supplier applications\n• Account issues\n• Payment questions\n\nWhat do you need help with?',
        'order': 'For order inquiries, please provide your order number or contact our support team at support@buildmate.com or call +233 596 211 352.',
        'supplier': 'To become a supplier, visit our supplier application page at <?= \App\View::url('/') ?>supplier/apply. You\'ll need to submit your business documents for verification.',
        'payment': 'We accept payments via Paystack. All transactions are secure and encrypted. If you have payment issues, contact support@buildmate.com.',
        'default': 'Thank you for your message! Our support team will get back to you soon. For immediate assistance, please call +233 596 211 352 or email support@buildmate.com.'
    };

    function addMessage(text, isUser = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${isUser ? 'user-message' : 'bot-message'} mb-3`;
        
        const messageContent = `
            <div class="d-flex align-items-start ${isUser ? 'flex-row-reverse' : ''}">
                <div class="avatar ${isUser ? 'bg-primary' : 'bg-secondary'} text-white rounded-circle d-flex align-items-center justify-content-center ${isUser ? 'ms-3' : 'me-3'}" style="width: 40px; height: 40px;">
                    <i class="bi ${isUser ? 'bi-person' : 'bi-robot'}"></i>
                </div>
                <div class="message-content ${isUser ? 'bg-primary text-white' : 'bg-white'} p-3 rounded shadow-sm" style="max-width: 75%;">
                    <p class="mb-0">${text.replace(/\n/g, '<br>')}</p>
                    <small class="${isUser ? 'text-white-50' : 'text-muted'}">${new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</small>
                </div>
            </div>
        `;
        
        messageDiv.innerHTML = messageContent;
        chatMessages.appendChild(messageDiv);
        
        // Scroll to bottom
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    function getResponse(userMessage) {
        const lowerMessage = userMessage.toLowerCase().trim();
        
        // Check for keywords
        for (const [keyword, response] of Object.entries(responses)) {
            if (keyword !== 'default' && lowerMessage.includes(keyword)) {
                return response;
            }
        }
        
        return responses.default;
    }

    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const message = chatInput.value.trim();
        if (!message) return;
        
        // Add user message
        addMessage(message, true);
        chatInput.value = '';
        
        // Show typing indicator
        chatStatus.innerHTML = '<i class="bi bi-circle-fill text-warning"></i> Typing...';
        
        // Simulate AI response delay
        setTimeout(() => {
            const response = getResponse(message);
            addMessage(response, false);
            chatStatus.innerHTML = '<i class="bi bi-circle-fill text-success"></i> Online';
        }, 1000 + Math.random() * 1000); // 1-2 second delay
    });

    // Auto-focus chat input
    chatInput.focus();
});
</script>
 * Contact Page with Live Chat
 */
$title = $title ?? 'Contact Us';
?>

<div class="container my-5">
    <div class="mb-3">
        <a href="<?= \App\View::url('/') ?>" class="back-button">
            <i class="icon-arrow-left"></i>
            <span>Back to Home</span>
        </a>
    </div>
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold mb-3">Get in Touch</h1>
                <p class="lead text-muted">We're here to help! Reach out to us through any of the channels below.</p>
            </div>

            <!-- Contact Information Cards -->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-telephone-fill text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="card-title">Phone</h5>
                            <p class="card-text text-muted">
                                <a href="tel:+233596211352" class="text-decoration-none">+233 596 211 352</a>
                            </p>
                            <small class="text-muted">Mon - Fri, 8AM - 6PM</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-envelope-fill text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="card-title">Email</h5>
                            <p class="card-text text-muted">
                                <a href="mailto:support@buildmate.com" class="text-decoration-none">support@buildmate.com</a>
                            </p>
                            <small class="text-muted">We respond within 24 hours</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-geo-alt-fill text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="card-title">Location</h5>
                            <p class="card-text text-muted">Accra, Ghana</p>
                            <small class="text-muted">Serving all of Ghana</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Live Chat Widget -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-chat-dots"></i> Live Chat Support
                    </h4>
                </div>
                <div class="card-body">
                    <div id="chat-container" style="min-height: 400px; max-height: 500px; overflow-y: auto; background: #f8f9fa; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                        <div id="chat-messages">
                            <div class="chat-message bot-message mb-3">
                                <div class="d-flex align-items-start">
                                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <i class="bi bi-robot"></i>
                                    </div>
                                    <div class="message-content bg-white p-3 rounded shadow-sm" style="max-width: 75%;">
                                        <p class="mb-0">Hello! 👋 Welcome to Build Mate support. How can I help you today?</p>
                                        <small class="text-muted">Just now</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <form id="chat-form" class="d-flex gap-2">
                        <input type="text" id="chat-input" class="form-control" placeholder="Type your message here..." autocomplete="off">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Send
                        </button>
                    </form>
                    <div id="chat-status" class="mt-2 text-muted small">
                        <i class="bi bi-circle-fill text-success"></i> Online
                    </div>
                </div>
            </div>

            <!-- Contact Form (Alternative) -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-envelope"></i> Send us a Message
                    </h5>
                </div>
                <div class="card-body">
                    <form id="contact-form" method="POST" action="<?= \App\View::url('/') ?>contact">
                        <?= \App\Csrf::field() ?>
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.chat-message {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.user-message {
    justify-content: flex-end;
}

.user-message .message-content {
    background: #007bff !important;
    color: white;
}

.user-message .message-content small {
    color: rgba(255, 255, 255, 0.8) !important;
}

#chat-container {
    scroll-behavior: smooth;
}

#chat-container::-webkit-scrollbar {
    width: 6px;
}

#chat-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

#chat-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

#chat-container::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const chatMessages = document.getElementById('chat-messages');
    const chatContainer = document.getElementById('chat-container');
    const chatStatus = document.getElementById('chat-status');

    // Simple AI chat responses (you can replace this with actual AI integration)
    const responses = {
        'hello': 'Hello! How can I help you with Build Mate today?',
        'hi': 'Hi there! What can I assist you with?',
        'help': 'I can help you with:\n• Product inquiries\n• Order tracking\n• Supplier applications\n• Account issues\n• Payment questions\n\nWhat do you need help with?',
        'order': 'For order inquiries, please provide your order number or contact our support team at support@buildmate.com or call +233 596 211 352.',
        'supplier': 'To become a supplier, visit our supplier application page at <?= \App\View::url('/') ?>supplier/apply. You\'ll need to submit your business documents for verification.',
        'payment': 'We accept payments via Paystack. All transactions are secure and encrypted. If you have payment issues, contact support@buildmate.com.',
        'default': 'Thank you for your message! Our support team will get back to you soon. For immediate assistance, please call +233 596 211 352 or email support@buildmate.com.'
    };

    function addMessage(text, isUser = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${isUser ? 'user-message' : 'bot-message'} mb-3`;
        
        const messageContent = `
            <div class="d-flex align-items-start ${isUser ? 'flex-row-reverse' : ''}">
                <div class="avatar ${isUser ? 'bg-primary' : 'bg-secondary'} text-white rounded-circle d-flex align-items-center justify-content-center ${isUser ? 'ms-3' : 'me-3'}" style="width: 40px; height: 40px;">
                    <i class="bi ${isUser ? 'bi-person' : 'bi-robot'}"></i>
                </div>
                <div class="message-content ${isUser ? 'bg-primary text-white' : 'bg-white'} p-3 rounded shadow-sm" style="max-width: 75%;">
                    <p class="mb-0">${text.replace(/\n/g, '<br>')}</p>
                    <small class="${isUser ? 'text-white-50' : 'text-muted'}">${new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</small>
                </div>
            </div>
        `;
        
        messageDiv.innerHTML = messageContent;
        chatMessages.appendChild(messageDiv);
        
        // Scroll to bottom
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    function getResponse(userMessage) {
        const lowerMessage = userMessage.toLowerCase().trim();
        
        // Check for keywords
        for (const [keyword, response] of Object.entries(responses)) {
            if (keyword !== 'default' && lowerMessage.includes(keyword)) {
                return response;
            }
        }
        
        return responses.default;
    }

    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const message = chatInput.value.trim();
        if (!message) return;
        
        // Add user message
        addMessage(message, true);
        chatInput.value = '';
        
        // Show typing indicator
        chatStatus.innerHTML = '<i class="bi bi-circle-fill text-warning"></i> Typing...';
        
        // Simulate AI response delay
        setTimeout(() => {
            const response = getResponse(message);
            addMessage(response, false);
            chatStatus.innerHTML = '<i class="bi bi-circle-fill text-success"></i> Online';
        }, 1000 + Math.random() * 1000); // 1-2 second delay
    });

    // Auto-focus chat input
    chatInput.focus();
});
</script>




 * Contact Page with Live Chat
 */
$title = $title ?? 'Contact Us';
?>

<div class="container my-5">
    <div class="mb-3">
        <a href="<?= \App\View::url('/') ?>" class="back-button">
            <i class="icon-arrow-left"></i>
            <span>Back to Home</span>
        </a>
    </div>
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold mb-3">Get in Touch</h1>
                <p class="lead text-muted">We're here to help! Reach out to us through any of the channels below.</p>
            </div>

            <!-- Contact Information Cards -->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-telephone-fill text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="card-title">Phone</h5>
                            <p class="card-text text-muted">
                                <a href="tel:+233596211352" class="text-decoration-none">+233 596 211 352</a>
                            </p>
                            <small class="text-muted">Mon - Fri, 8AM - 6PM</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-envelope-fill text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="card-title">Email</h5>
                            <p class="card-text text-muted">
                                <a href="mailto:support@buildmate.com" class="text-decoration-none">support@buildmate.com</a>
                            </p>
                            <small class="text-muted">We respond within 24 hours</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-geo-alt-fill text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="card-title">Location</h5>
                            <p class="card-text text-muted">Accra, Ghana</p>
                            <small class="text-muted">Serving all of Ghana</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Live Chat Widget -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-chat-dots"></i> Live Chat Support
                    </h4>
                </div>
                <div class="card-body">
                    <div id="chat-container" style="min-height: 400px; max-height: 500px; overflow-y: auto; background: #f8f9fa; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                        <div id="chat-messages">
                            <div class="chat-message bot-message mb-3">
                                <div class="d-flex align-items-start">
                                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <i class="bi bi-robot"></i>
                                    </div>
                                    <div class="message-content bg-white p-3 rounded shadow-sm" style="max-width: 75%;">
                                        <p class="mb-0">Hello! 👋 Welcome to Build Mate support. How can I help you today?</p>
                                        <small class="text-muted">Just now</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <form id="chat-form" class="d-flex gap-2">
                        <input type="text" id="chat-input" class="form-control" placeholder="Type your message here..." autocomplete="off">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Send
                        </button>
                    </form>
                    <div id="chat-status" class="mt-2 text-muted small">
                        <i class="bi bi-circle-fill text-success"></i> Online
                    </div>
                </div>
            </div>

            <!-- Contact Form (Alternative) -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-envelope"></i> Send us a Message
                    </h5>
                </div>
                <div class="card-body">
                    <form id="contact-form" method="POST" action="<?= \App\View::url('/') ?>contact">
                        <?= \App\Csrf::field() ?>
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.chat-message {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.user-message {
    justify-content: flex-end;
}

.user-message .message-content {
    background: #007bff !important;
    color: white;
}

.user-message .message-content small {
    color: rgba(255, 255, 255, 0.8) !important;
}

#chat-container {
    scroll-behavior: smooth;
}

#chat-container::-webkit-scrollbar {
    width: 6px;
}

#chat-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

#chat-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

#chat-container::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const chatMessages = document.getElementById('chat-messages');
    const chatContainer = document.getElementById('chat-container');
    const chatStatus = document.getElementById('chat-status');

    // Simple AI chat responses (you can replace this with actual AI integration)
    const responses = {
        'hello': 'Hello! How can I help you with Build Mate today?',
        'hi': 'Hi there! What can I assist you with?',
        'help': 'I can help you with:\n• Product inquiries\n• Order tracking\n• Supplier applications\n• Account issues\n• Payment questions\n\nWhat do you need help with?',
        'order': 'For order inquiries, please provide your order number or contact our support team at support@buildmate.com or call +233 596 211 352.',
        'supplier': 'To become a supplier, visit our supplier application page at <?= \App\View::url('/') ?>supplier/apply. You\'ll need to submit your business documents for verification.',
        'payment': 'We accept payments via Paystack. All transactions are secure and encrypted. If you have payment issues, contact support@buildmate.com.',
        'default': 'Thank you for your message! Our support team will get back to you soon. For immediate assistance, please call +233 596 211 352 or email support@buildmate.com.'
    };

    function addMessage(text, isUser = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${isUser ? 'user-message' : 'bot-message'} mb-3`;
        
        const messageContent = `
            <div class="d-flex align-items-start ${isUser ? 'flex-row-reverse' : ''}">
                <div class="avatar ${isUser ? 'bg-primary' : 'bg-secondary'} text-white rounded-circle d-flex align-items-center justify-content-center ${isUser ? 'ms-3' : 'me-3'}" style="width: 40px; height: 40px;">
                    <i class="bi ${isUser ? 'bi-person' : 'bi-robot'}"></i>
                </div>
                <div class="message-content ${isUser ? 'bg-primary text-white' : 'bg-white'} p-3 rounded shadow-sm" style="max-width: 75%;">
                    <p class="mb-0">${text.replace(/\n/g, '<br>')}</p>
                    <small class="${isUser ? 'text-white-50' : 'text-muted'}">${new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</small>
                </div>
            </div>
        `;
        
        messageDiv.innerHTML = messageContent;
        chatMessages.appendChild(messageDiv);
        
        // Scroll to bottom
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    function getResponse(userMessage) {
        const lowerMessage = userMessage.toLowerCase().trim();
        
        // Check for keywords
        for (const [keyword, response] of Object.entries(responses)) {
            if (keyword !== 'default' && lowerMessage.includes(keyword)) {
                return response;
            }
        }
        
        return responses.default;
    }

    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const message = chatInput.value.trim();
        if (!message) return;
        
        // Add user message
        addMessage(message, true);
        chatInput.value = '';
        
        // Show typing indicator
        chatStatus.innerHTML = '<i class="bi bi-circle-fill text-warning"></i> Typing...';
        
        // Simulate AI response delay
        setTimeout(() => {
            const response = getResponse(message);
            addMessage(response, false);
            chatStatus.innerHTML = '<i class="bi bi-circle-fill text-success"></i> Online';
        }, 1000 + Math.random() * 1000); // 1-2 second delay
    });

    // Auto-focus chat input
    chatInput.focus();
});
</script>


 * Contact Page with Live Chat
 */
$title = $title ?? 'Contact Us';
?>

<div class="container my-5">
    <div class="mb-3">
        <a href="<?= \App\View::url('/') ?>" class="back-button">
            <i class="icon-arrow-left"></i>
            <span>Back to Home</span>
        </a>
    </div>
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold mb-3">Get in Touch</h1>
                <p class="lead text-muted">We're here to help! Reach out to us through any of the channels below.</p>
            </div>

            <!-- Contact Information Cards -->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-telephone-fill text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="card-title">Phone</h5>
                            <p class="card-text text-muted">
                                <a href="tel:+233596211352" class="text-decoration-none">+233 596 211 352</a>
                            </p>
                            <small class="text-muted">Mon - Fri, 8AM - 6PM</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-envelope-fill text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="card-title">Email</h5>
                            <p class="card-text text-muted">
                                <a href="mailto:support@buildmate.com" class="text-decoration-none">support@buildmate.com</a>
                            </p>
                            <small class="text-muted">We respond within 24 hours</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-geo-alt-fill text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="card-title">Location</h5>
                            <p class="card-text text-muted">Accra, Ghana</p>
                            <small class="text-muted">Serving all of Ghana</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Live Chat Widget -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-chat-dots"></i> Live Chat Support
                    </h4>
                </div>
                <div class="card-body">
                    <div id="chat-container" style="min-height: 400px; max-height: 500px; overflow-y: auto; background: #f8f9fa; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                        <div id="chat-messages">
                            <div class="chat-message bot-message mb-3">
                                <div class="d-flex align-items-start">
                                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <i class="bi bi-robot"></i>
                                    </div>
                                    <div class="message-content bg-white p-3 rounded shadow-sm" style="max-width: 75%;">
                                        <p class="mb-0">Hello! 👋 Welcome to Build Mate support. How can I help you today?</p>
                                        <small class="text-muted">Just now</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <form id="chat-form" class="d-flex gap-2">
                        <input type="text" id="chat-input" class="form-control" placeholder="Type your message here..." autocomplete="off">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Send
                        </button>
                    </form>
                    <div id="chat-status" class="mt-2 text-muted small">
                        <i class="bi bi-circle-fill text-success"></i> Online
                    </div>
                </div>
            </div>

            <!-- Contact Form (Alternative) -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-envelope"></i> Send us a Message
                    </h5>
                </div>
                <div class="card-body">
                    <form id="contact-form" method="POST" action="<?= \App\View::url('/') ?>contact">
                        <?= \App\Csrf::field() ?>
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.chat-message {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.user-message {
    justify-content: flex-end;
}

.user-message .message-content {
    background: #007bff !important;
    color: white;
}

.user-message .message-content small {
    color: rgba(255, 255, 255, 0.8) !important;
}

#chat-container {
    scroll-behavior: smooth;
}

#chat-container::-webkit-scrollbar {
    width: 6px;
}

#chat-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

#chat-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

#chat-container::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const chatMessages = document.getElementById('chat-messages');
    const chatContainer = document.getElementById('chat-container');
    const chatStatus = document.getElementById('chat-status');

    // Simple AI chat responses (you can replace this with actual AI integration)
    const responses = {
        'hello': 'Hello! How can I help you with Build Mate today?',
        'hi': 'Hi there! What can I assist you with?',
        'help': 'I can help you with:\n• Product inquiries\n• Order tracking\n• Supplier applications\n• Account issues\n• Payment questions\n\nWhat do you need help with?',
        'order': 'For order inquiries, please provide your order number or contact our support team at support@buildmate.com or call +233 596 211 352.',
        'supplier': 'To become a supplier, visit our supplier application page at <?= \App\View::url('/') ?>supplier/apply. You\'ll need to submit your business documents for verification.',
        'payment': 'We accept payments via Paystack. All transactions are secure and encrypted. If you have payment issues, contact support@buildmate.com.',
        'default': 'Thank you for your message! Our support team will get back to you soon. For immediate assistance, please call +233 596 211 352 or email support@buildmate.com.'
    };

    function addMessage(text, isUser = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${isUser ? 'user-message' : 'bot-message'} mb-3`;
        
        const messageContent = `
            <div class="d-flex align-items-start ${isUser ? 'flex-row-reverse' : ''}">
                <div class="avatar ${isUser ? 'bg-primary' : 'bg-secondary'} text-white rounded-circle d-flex align-items-center justify-content-center ${isUser ? 'ms-3' : 'me-3'}" style="width: 40px; height: 40px;">
                    <i class="bi ${isUser ? 'bi-person' : 'bi-robot'}"></i>
                </div>
                <div class="message-content ${isUser ? 'bg-primary text-white' : 'bg-white'} p-3 rounded shadow-sm" style="max-width: 75%;">
                    <p class="mb-0">${text.replace(/\n/g, '<br>')}</p>
                    <small class="${isUser ? 'text-white-50' : 'text-muted'}">${new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</small>
                </div>
            </div>
        `;
        
        messageDiv.innerHTML = messageContent;
        chatMessages.appendChild(messageDiv);
        
        // Scroll to bottom
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    function getResponse(userMessage) {
        const lowerMessage = userMessage.toLowerCase().trim();
        
        // Check for keywords
        for (const [keyword, response] of Object.entries(responses)) {
            if (keyword !== 'default' && lowerMessage.includes(keyword)) {
                return response;
            }
        }
        
        return responses.default;
    }

    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const message = chatInput.value.trim();
        if (!message) return;
        
        // Add user message
        addMessage(message, true);
        chatInput.value = '';
        
        // Show typing indicator
        chatStatus.innerHTML = '<i class="bi bi-circle-fill text-warning"></i> Typing...';
        
        // Simulate AI response delay
        setTimeout(() => {
            const response = getResponse(message);
            addMessage(response, false);
            chatStatus.innerHTML = '<i class="bi bi-circle-fill text-success"></i> Online';
        }, 1000 + Math.random() * 1000); // 1-2 second delay
    });

    // Auto-focus chat input
    chatInput.focus();
});
</script>




 * Contact Page with Live Chat
 */
$title = $title ?? 'Contact Us';
?>

<div class="container my-5">
    <div class="mb-3">
        <a href="<?= \App\View::url('/') ?>" class="back-button">
            <i class="icon-arrow-left"></i>
            <span>Back to Home</span>
        </a>
    </div>
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold mb-3">Get in Touch</h1>
                <p class="lead text-muted">We're here to help! Reach out to us through any of the channels below.</p>
            </div>

            <!-- Contact Information Cards -->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-telephone-fill text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="card-title">Phone</h5>
                            <p class="card-text text-muted">
                                <a href="tel:+233596211352" class="text-decoration-none">+233 596 211 352</a>
                            </p>
                            <small class="text-muted">Mon - Fri, 8AM - 6PM</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-envelope-fill text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="card-title">Email</h5>
                            <p class="card-text text-muted">
                                <a href="mailto:support@buildmate.com" class="text-decoration-none">support@buildmate.com</a>
                            </p>
                            <small class="text-muted">We respond within 24 hours</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-geo-alt-fill text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="card-title">Location</h5>
                            <p class="card-text text-muted">Accra, Ghana</p>
                            <small class="text-muted">Serving all of Ghana</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Live Chat Widget -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-chat-dots"></i> Live Chat Support
                    </h4>
                </div>
                <div class="card-body">
                    <div id="chat-container" style="min-height: 400px; max-height: 500px; overflow-y: auto; background: #f8f9fa; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                        <div id="chat-messages">
                            <div class="chat-message bot-message mb-3">
                                <div class="d-flex align-items-start">
                                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <i class="bi bi-robot"></i>
                                    </div>
                                    <div class="message-content bg-white p-3 rounded shadow-sm" style="max-width: 75%;">
                                        <p class="mb-0">Hello! 👋 Welcome to Build Mate support. How can I help you today?</p>
                                        <small class="text-muted">Just now</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <form id="chat-form" class="d-flex gap-2">
                        <input type="text" id="chat-input" class="form-control" placeholder="Type your message here..." autocomplete="off">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Send
                        </button>
                    </form>
                    <div id="chat-status" class="mt-2 text-muted small">
                        <i class="bi bi-circle-fill text-success"></i> Online
                    </div>
                </div>
            </div>

            <!-- Contact Form (Alternative) -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-envelope"></i> Send us a Message
                    </h5>
                </div>
                <div class="card-body">
                    <form id="contact-form" method="POST" action="<?= \App\View::url('/') ?>contact">
                        <?= \App\Csrf::field() ?>
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.chat-message {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.user-message {
    justify-content: flex-end;
}

.user-message .message-content {
    background: #007bff !important;
    color: white;
}

.user-message .message-content small {
    color: rgba(255, 255, 255, 0.8) !important;
}

#chat-container {
    scroll-behavior: smooth;
}

#chat-container::-webkit-scrollbar {
    width: 6px;
}

#chat-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

#chat-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

#chat-container::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const chatMessages = document.getElementById('chat-messages');
    const chatContainer = document.getElementById('chat-container');
    const chatStatus = document.getElementById('chat-status');

    // Simple AI chat responses (you can replace this with actual AI integration)
    const responses = {
        'hello': 'Hello! How can I help you with Build Mate today?',
        'hi': 'Hi there! What can I assist you with?',
        'help': 'I can help you with:\n• Product inquiries\n• Order tracking\n• Supplier applications\n• Account issues\n• Payment questions\n\nWhat do you need help with?',
        'order': 'For order inquiries, please provide your order number or contact our support team at support@buildmate.com or call +233 596 211 352.',
        'supplier': 'To become a supplier, visit our supplier application page at <?= \App\View::url('/') ?>supplier/apply. You\'ll need to submit your business documents for verification.',
        'payment': 'We accept payments via Paystack. All transactions are secure and encrypted. If you have payment issues, contact support@buildmate.com.',
        'default': 'Thank you for your message! Our support team will get back to you soon. For immediate assistance, please call +233 596 211 352 or email support@buildmate.com.'
    };

    function addMessage(text, isUser = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${isUser ? 'user-message' : 'bot-message'} mb-3`;
        
        const messageContent = `
            <div class="d-flex align-items-start ${isUser ? 'flex-row-reverse' : ''}">
                <div class="avatar ${isUser ? 'bg-primary' : 'bg-secondary'} text-white rounded-circle d-flex align-items-center justify-content-center ${isUser ? 'ms-3' : 'me-3'}" style="width: 40px; height: 40px;">
                    <i class="bi ${isUser ? 'bi-person' : 'bi-robot'}"></i>
                </div>
                <div class="message-content ${isUser ? 'bg-primary text-white' : 'bg-white'} p-3 rounded shadow-sm" style="max-width: 75%;">
                    <p class="mb-0">${text.replace(/\n/g, '<br>')}</p>
                    <small class="${isUser ? 'text-white-50' : 'text-muted'}">${new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</small>
                </div>
            </div>
        `;
        
        messageDiv.innerHTML = messageContent;
        chatMessages.appendChild(messageDiv);
        
        // Scroll to bottom
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    function getResponse(userMessage) {
        const lowerMessage = userMessage.toLowerCase().trim();
        
        // Check for keywords
        for (const [keyword, response] of Object.entries(responses)) {
            if (keyword !== 'default' && lowerMessage.includes(keyword)) {
                return response;
            }
        }
        
        return responses.default;
    }

    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const message = chatInput.value.trim();
        if (!message) return;
        
        // Add user message
        addMessage(message, true);
        chatInput.value = '';
        
        // Show typing indicator
        chatStatus.innerHTML = '<i class="bi bi-circle-fill text-warning"></i> Typing...';
        
        // Simulate AI response delay
        setTimeout(() => {
            const response = getResponse(message);
            addMessage(response, false);
            chatStatus.innerHTML = '<i class="bi bi-circle-fill text-success"></i> Online';
        }, 1000 + Math.random() * 1000); // 1-2 second delay
    });

    // Auto-focus chat input
    chatInput.focus();
});
</script>