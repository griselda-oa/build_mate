/**
 * Build Mate - Floating Chat Widget
 * Context-aware chat with building materials knowledge
 */

class ChatWidget {
    constructor() {
        this.isOpen = false;
        this.messages = [];
        this.sessionId = null;
        this.pageContext = this.detectPageContext();
        this.userRole = this.detectUserRole();
        this.init();
    }

    detectPageContext() {
        const path = window.location.pathname;
        const context = {
            page: 'home',
            section: null,
            status: null
        };

        // Detect page type
        if (path.includes('/supplier')) {
            context.page = 'supplier';
            if (path.includes('/pending')) {
                context.section = 'pending_application';
                context.status = 'pending';
            } else if (path.includes('/kyc')) {
                context.section = 'kyc_application';
            } else if (path.includes('/dashboard')) {
                context.section = 'dashboard';
            } else if (path.includes('/products')) {
                context.section = 'products';
            } else if (path.includes('/orders')) {
                context.section = 'orders';
            }
        } else if (path.includes('/buyer') || path.includes('/dashboard') || path.includes('/orders')) {
            context.page = 'buyer';
            if (path.includes('/orders')) {
                context.section = 'orders';
            } else if (path.includes('/cart')) {
                context.section = 'cart';
            } else if (path.includes('/checkout')) {
                context.section = 'checkout';
            }
        } else if (path.includes('/logistics')) {
            context.page = 'logistics';
            if (path.includes('/dashboard')) {
                context.section = 'dashboard';
            } else if (path.includes('/assignments')) {
                context.section = 'assignments';
            }
        } else if (path.includes('/admin')) {
            context.page = 'admin';
        } else if (path.includes('/catalog') || path.includes('/product/')) {
            context.page = 'catalog';
        }

        return context;
    }

    detectUserRole() {
        // Try to detect from page elements or session
        const path = window.location.pathname;
        if (path.includes('/supplier')) return 'supplier';
        if (path.includes('/logistics')) return 'logistics';
        if (path.includes('/admin')) return 'admin';
        return 'buyer';
    }

    init() {
        this.createWidget();
        this.loadInitialMessage();
    }

    createWidget() {
        // Check if widget already exists to prevent duplicates
        if (document.getElementById('chat-widget-button') || document.getElementById('chat-widget-window')) {
            console.warn('Chat widget already exists, skipping creation');
            return;
        }
        
        // Create chat button
        const chatButton = document.createElement('div');
        chatButton.id = 'chat-widget-button';
        chatButton.className = 'chat-widget-button';
        chatButton.innerHTML = '<i class="bi bi-chat-dots"></i>';
        chatButton.addEventListener('click', () => this.toggle());

        // Create chat window
        const chatWindow = document.createElement('div');
        chatWindow.id = 'chat-widget-window';
        chatWindow.className = 'chat-widget-window';
        chatWindow.innerHTML = `
            <div class="chat-widget-header">
                <div class="d-flex align-items-center">
                    <div class="chat-avatar me-2">
                        <i class="bi bi-robot"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">Build Mate Support</h6>
                        <small class="text-muted" id="chat-status">Online</small>
                    </div>
                </div>
                <button class="btn-close-chat" id="chat-close-btn">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="chat-widget-messages" id="chat-messages">
                <!-- Messages will be added here -->
            </div>
            <div class="chat-widget-input">
                <input type="text" id="chat-input" placeholder="Type your message..." autocomplete="off">
                <button id="chat-send-btn">
                    <i class="bi bi-send"></i>
                </button>
            </div>
        `;

        document.body.appendChild(chatButton);
        document.body.appendChild(chatWindow);

        // Event listeners
        document.getElementById('chat-close-btn').addEventListener('click', () => this.toggle());
        document.getElementById('chat-send-btn').addEventListener('click', () => this.sendMessage());
        document.getElementById('chat-input').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.sendMessage();
        });

        // Close on outside click
        chatWindow.addEventListener('click', (e) => {
            if (e.target === chatWindow) this.close();
        });
    }

    async loadInitialMessage() {
        // Try to load chat history first
        await this.loadHistory();
        
        // If no history, show greeting
        if (this.messages.length === 0) {
            let greeting = "Hello! 👋 Welcome to Build Mate support. How can I help you today?";
            
            // Context-aware greeting
            if (this.pageContext.page === 'supplier') {
                if (this.pageContext.status === 'pending') {
                    greeting = "Hi! I see you're waiting for supplier approval. I can help with:\n• Application status questions\n• Document requirements\n• What to expect during review\n• Building materials information\n\nWhat would you like to know?";
                } else if (this.pageContext.section === 'kyc_application') {
                    greeting = "Hello! I can help you with your KYC application:\n• Document requirements\n• Form questions\n• Verification process\n• Building materials categories\n\nHow can I assist?";
                } else {
                    greeting = "Hello! I'm here to help with your supplier account:\n• Product management\n• Order handling\n• Building materials\n• Account questions\n\nWhat do you need?";
                }
            } else if (this.pageContext.page === 'buyer') {
                greeting = "Hi! I can help you with:\n• Finding building materials\n• Placing orders\n• Order tracking\n• Product questions\n• Payment & delivery\n\nWhat can I help with?";
            } else if (this.pageContext.page === 'logistics') {
                greeting = "Hello! Logistics support here. I can help with:\n• Delivery assignments\n• Status updates\n• Route planning\n• Building materials handling\n\nHow can I assist?";
            }

            this.addMessage(greeting, false);
        }
    }

    toggle() {
        this.isOpen = !this.isOpen;
        const window = document.getElementById('chat-widget-window');
        const button = document.getElementById('chat-widget-button');
        
        if (this.isOpen) {
            window.classList.add('open');
            button.classList.add('hidden');
            document.getElementById('chat-input').focus();
        } else {
            window.classList.remove('open');
            button.classList.remove('hidden');
        }
    }

    open() {
        if (!this.isOpen) this.toggle();
    }

    close() {
        if (this.isOpen) this.toggle();
    }

    addMessage(text, isUser = false) {
        const messagesContainer = document.getElementById('chat-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${isUser ? 'user' : 'bot'}`;
        
        const time = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        
        messageDiv.innerHTML = `
            <div class="chat-message-content">
                ${!isUser ? '<div class="chat-avatar-small"><i class="bi bi-robot"></i></div>' : ''}
                <div class="message-bubble ${isUser ? 'user-bubble' : 'bot-bubble'}">
                    <p>${text.replace(/\n/g, '<br>')}</p>
                    <small class="message-time">${time}</small>
                </div>
                ${isUser ? '<div class="chat-avatar-small"><i class="bi bi-person"></i></div>' : ''}
            </div>
        `;
        
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        this.messages.push({ text, isUser, time });
    }

    async sendMessage() {
        const input = document.getElementById('chat-input');
        const message = input.value.trim();
        
        if (!message) return;
        
        this.addMessage(message, true);
        input.value = '';
        
        // Show typing indicator
        this.showTyping();
        
        try {
            // Get or create session ID
            if (!this.sessionId) {
                this.sessionId = this.getOrCreateSessionId();
            }
            
            // Send to API
            const response = await fetch(window.buildUrl('/api/chat/send'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: message,
                    session_id: this.sessionId,
                    context: this.pageContext
                })
            });
            
            const data = await response.json();
            
            this.hideTyping();
            
            if (data.success) {
                this.addMessage(data.message, false);
                // Store session ID if returned
                if (data.session_id) {
                    this.sessionId = data.session_id;
                    this.saveSessionId(data.session_id);
                }
            } else {
                this.addMessage('Sorry, I encountered an error. Please try again or contact support at +233 596 211 352.', false);
            }
        } catch (error) {
            console.error('Chat error:', error);
            this.hideTyping();
            // Fallback to local response
            const response = this.getResponse(message);
            this.addMessage(response, false);
        }
    }
    
    getOrCreateSessionId() {
        // Try to get from sessionStorage
        let sessionId = sessionStorage.getItem('chat_session_id');
        if (!sessionId) {
            sessionId = 'chat_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            sessionStorage.setItem('chat_session_id', sessionId);
        }
        return sessionId;
    }
    
    saveSessionId(sessionId) {
        sessionStorage.setItem('chat_session_id', sessionId);
        this.sessionId = sessionId;
    }
    
    async loadHistory() {
        if (!this.sessionId) {
            this.sessionId = this.getOrCreateSessionId();
        }
        
        try {
            const response = await fetch(`${window.buildUrl('/api/chat/history')}?session_id=${this.sessionId}`);
            
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                // If not JSON, likely an error page - skip loading history silently
                return;
            }
            
            const data = await response.json();
            
            if (data.success && data.messages && Array.isArray(data.messages) && data.messages.length > 0) {
                // Clear current messages
                const messagesContainer = document.getElementById('chat-messages');
                if (messagesContainer) {
                    messagesContainer.innerHTML = '';
                    this.messages = [];
                    
                    // Load history
                    data.messages.forEach(msg => {
                        this.addMessage(msg.message, msg.role === 'user');
                    });
                }
            }
        } catch (error) {
            // Silently fail - chat history is optional and shouldn't break the page
            // Only log in debug mode
            if (window.DEBUG) {
                console.debug('Chat history not available:', error.message);
            }
        }
    }

    showTyping() {
        const messagesContainer = document.getElementById('chat-messages');
        const typingDiv = document.createElement('div');
        typingDiv.id = 'typing-indicator';
        typingDiv.className = 'chat-message bot typing';
        typingDiv.innerHTML = `
            <div class="chat-message-content">
                <div class="chat-avatar-small"><i class="bi bi-robot"></i></div>
                <div class="message-bubble bot-bubble">
                    <div class="typing-dots">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            </div>
        `;
        messagesContainer.appendChild(typingDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    hideTyping() {
        const typing = document.getElementById('typing-indicator');
        if (typing) typing.remove();
    }

    getResponse(userMessage) {
        const lowerMessage = userMessage.toLowerCase().trim();
        const context = this.pageContext;
        
        // Building materials knowledge base
        const materials = {
            'cement': 'Cement is a binding material used in construction. Common types include:\n• Ordinary Portland Cement (OPC)\n• Portland Pozzolana Cement (PPC)\n• Rapid Hardening Cement\n\nWe have various brands available. What type are you looking for?',
            'blocks': 'Building blocks come in different sizes:\n• 4-inch blocks (100mm)\n• 6-inch blocks (150mm)\n• 8-inch blocks (200mm)\n\nWe have hollow and solid blocks from verified suppliers. Need help finding specific dimensions?',
            'iron rods': 'Iron rods (reinforcement bars) are essential for concrete structures:\n• 8mm, 10mm, 12mm, 16mm, 20mm diameters\n• Y12, Y16, Y20 grades\n• Available in bundles or by weight\n\nWhat size do you need for your project?',
            'roofing': 'Roofing materials we offer:\n• Metal sheets (aluminum, galvanized)\n• Roofing tiles\n• Wooden trusses\n• Insulation materials\n\nWhat type of roofing are you planning?',
            'sand': 'We have various types of sand:\n• River sand (fine, for plastering)\n• Sharp sand (coarse, for concrete)\n• Quarry dust\n• Available in truckloads or bags\n\nWhat quantity do you need?',
            'stone': 'Building stones available:\n• Quarry stones (various sizes)\n• Chippings (for concrete)\n• Gravel\n• Available by volume or weight\n\nWhat size and quantity?',
            'paint': 'We stock quality paints:\n• Emulsion paint (interior/exterior)\n• Oil-based paint\n• Primer and undercoats\n• Various brands and colors\n\nWhat type of paint do you need?',
            'tiles': 'Tile options:\n• Ceramic tiles\n• Porcelain tiles\n• Floor tiles\n• Wall tiles\n• Various sizes and designs\n\nWhat area are you tiling?',
            'plumbing': 'Plumbing materials:\n• PVC pipes (various diameters)\n• Fittings and connectors\n• Taps and faucets\n• Water tanks\n• Sanitary ware\n\nWhat plumbing materials do you need?',
            'electrical': 'Electrical supplies:\n• Wires and cables\n• Switches and sockets\n• Circuit breakers\n• Conduits\n• Lighting fixtures\n\nWhat electrical work are you doing?'
        };

        // Check for building materials keywords
        for (const [material, response] of Object.entries(materials)) {
            if (lowerMessage.includes(material)) {
                return response;
            }
        }

        // Context-specific responses
        if (context.page === 'supplier' && context.status === 'pending') {
            if (lowerMessage.includes('status') || lowerMessage.includes('approval') || lowerMessage.includes('when')) {
                return 'Your supplier application is currently pending review. Our admin team typically reviews applications within 24-48 hours. You\'ll receive an email notification once your application is approved. In the meantime, feel free to ask about building materials or the verification process!';
            }
            if (lowerMessage.includes('document') || lowerMessage.includes('kyc') || lowerMessage.includes('required')) {
                return 'For supplier verification, you need:\n• Business Registration Certificate\n• Tax Identification Number (TIN)\n• Owner/Director ID Card\n• Proof of Business Address\n\nAll documents should be clear and valid. Need help with any specific document?';
            }
        }

        if (context.page === 'supplier' && context.section === 'products') {
            if (lowerMessage.includes('add') || lowerMessage.includes('create') || lowerMessage.includes('product')) {
                return 'To add a product:\n1. Click "Add Product" button\n2. Fill in product details (name, description, price)\n3. Upload product images\n4. Set stock quantity\n5. Select category\n\nNeed help with a specific step?';
            }
        }

        if (context.page === 'buyer' && context.section === 'cart') {
            if (lowerMessage.includes('checkout') || lowerMessage.includes('pay') || lowerMessage.includes('order')) {
                return 'To complete your order:\n1. Review items in your cart\n2. Select delivery logistics for each supplier\n3. Proceed to checkout\n4. Pay securely via Paystack\n5. Track your order\n\nNeed help with any step?';
            }
        }

        // General responses
        if (lowerMessage.includes('hello') || lowerMessage.includes('hi') || lowerMessage.includes('hey')) {
            return 'Hello! 👋 How can I help you with Build Mate today? I can assist with building materials, orders, account questions, and more!';
        }

        if (lowerMessage.includes('help')) {
            return `I can help you with:\n• Building materials information\n• ${context.page === 'supplier' ? 'Product management, orders, account status' : context.page === 'buyer' ? 'Finding products, placing orders, tracking' : 'Delivery assignments, logistics'}\n• Payment and delivery questions\n• Account support\n\nWhat do you need help with?`;
        }

        if (lowerMessage.includes('price') || lowerMessage.includes('cost') || lowerMessage.includes('how much')) {
            return 'Prices vary by product, supplier, and quantity. You can:\n• Browse our catalog to see current prices\n• Filter by price range\n• Contact suppliers directly for bulk pricing\n• Check product pages for detailed pricing\n\nWhat material are you interested in?';
        }

        if (lowerMessage.includes('delivery') || lowerMessage.includes('shipping') || lowerMessage.includes('transport')) {
            return 'We offer multiple delivery options:\n• Build Mate Internal Logistics\n• External logistics partners\n• Delivery fees vary by location and supplier\n• Real-time tracking available\n\nNeed help with a specific delivery?';
        }

        if (lowerMessage.includes('payment') || lowerMessage.includes('pay') || lowerMessage.includes('money')) {
            return 'Payment options:\n• Secure payments via Paystack\n• Paystack secure payment (funds held until delivery)\n• Safe and encrypted transactions\n\nHave a payment question?';
        }

        if (lowerMessage.includes('order') || lowerMessage.includes('track')) {
            return 'To track your order:\n• Go to "My Orders" in your dashboard\n• Click on the order you want to track\n• View real-time status updates\n• See delivery progress\n\nNeed help finding your order?';
        }

        if (lowerMessage.includes('supplier') && context.page !== 'supplier') {
            return `To become a supplier:\n• Visit ${window.buildUrl('/supplier/apply')}\n• Complete the application form\n• Submit KYC documents\n• Wait for admin approval (24-48 hours)\n• Start listing products once approved\n\nWant to know more about the process?`;
        }

        // Default response
        return `Thanks for your message! I'm here to help with:\n• Building materials (cement, blocks, iron rods, roofing, sand, stone, paint, tiles, plumbing, electrical)\n• Orders and deliveries\n• Account questions\n• Supplier/buyer support\n\nFor immediate help, call +233 596 211 352 or email support@buildmate.com.\n\nWhat specific question can I answer?`;
    }
}

// Initialize chat widget when DOM is ready
// Prevent multiple initializations
if (!window.chatWidgetInitialized) {
    document.addEventListener('DOMContentLoaded', function() {
        if (!window.chatWidget) {
            window.chatWidget = new ChatWidget();
            window.chatWidgetInitialized = true;
        }
    });
    
    // Also try to initialize immediately if DOM is already loaded
    if (document.readyState === 'loading') {
        // DOM is still loading, wait for DOMContentLoaded
    } else {
        // DOM is already loaded, initialize immediately
        if (!window.chatWidget) {
            window.chatWidget = new ChatWidget();
            window.chatWidgetInitialized = true;
        }
    }
}