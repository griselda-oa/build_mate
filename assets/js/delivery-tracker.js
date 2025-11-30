/**
 * Simplified Delivery Tracker - Updates timeline based on order.status only
 * Status values: placed, paid, processing, out_for_delivery, delivered
 */

function updateTimeline(orderStatus) {
    const tracker = document.getElementById('deliveryTracker');
    const progressLine = document.getElementById('trackerProgress');
    
    if (!tracker) return;
    
    // Get all step elements
    const steps = tracker.querySelectorAll('.step');
    
    // Check which steps were marked as completed by server (PHP) BEFORE removing classes
    const serverCompletedSteps = new Set();
    steps.forEach(step => {
        if (step.classList.contains('completed')) {
            const stepName = step.dataset.step;
            if (stepName) {
                serverCompletedSteps.add(stepName);
            }
        }
    });
    
    // Store which steps are clickable (preserve clickable state from server-rendered HTML)
    const clickableSteps = new Set();
    const lockedSteps = new Set();
    steps.forEach(step => {
        const stepName = step.dataset.step;
        if (stepName) {
            if (step.classList.contains('clickable')) {
                clickableSteps.add(stepName);
            }
            if (step.classList.contains('locked')) {
                lockedSteps.add(stepName);
            }
        }
    });
    
    // Remove only status classes (active, completed, pending) but preserve clickable and locked
    steps.forEach(step => {
        step.classList.remove('active', 'completed', 'pending');
        // Keep 'clickable' and 'locked' classes - they're set by server PHP
    });
    
    // Helper function to mark a step
    function mark(stepName, className) {
        const el = tracker.querySelector(`.step[data-step="${stepName}"]`);
        if (el) {
            // If server marked it as completed, always use completed (don't downgrade)
            if (serverCompletedSteps.has(stepName) && className !== 'completed') {
                className = 'completed';
                console.log(`Step "${stepName}" was marked completed by server - preserving`);
            }
            
            // Remove status classes but preserve clickable/locked
            el.classList.remove('active', 'completed', 'pending');
            el.classList.add(className);
            
            // Restore clickable state if it was clickable before and step is pending
            if (clickableSteps.has(stepName) && className === 'pending' && !lockedSteps.has(stepName)) {
                el.classList.add('clickable');
                el.classList.remove('locked');
            }
            
            // Restore locked state if it was locked before
            if (lockedSteps.has(stepName)) {
                el.classList.add('locked');
                el.classList.remove('clickable');
            }
            
            console.log(`Marked step "${stepName}" as "${className}" (clickable: ${clickableSteps.has(stepName)}, locked: ${lockedSteps.has(stepName)})`);
        } else {
            console.error(`Step element not found for: ${stepName}`);
        }
    }
    
    // Step 1 – Order Placed (always completed)
    mark('placed', 'completed');
    
    // Step 2 – Payment Successful
    // ALWAYS mark as completed - if order exists on this page, payment was successful
    // Buyer received receipt = payment successful
    mark('paid', 'completed');
    
    // Step 3 – Supplier Processing
    if (['processing', 'out_for_delivery', 'delivered'].includes(orderStatus)) {
        mark('processing', 'completed');
    } else if (orderStatus === 'paid') {
        mark('processing', 'pending');
    } else {
        mark('processing', 'pending');
    }
    
    // Step 4 – Out for Delivery
    if (['out_for_delivery', 'delivered'].includes(orderStatus)) {
        mark('out_for_delivery', 'completed');
    } else if (orderStatus === 'processing') {
        mark('out_for_delivery', 'pending');
    } else {
        mark('out_for_delivery', 'pending');
    }
    
    // Step 5 – Delivered
    if (orderStatus === 'delivered') {
        mark('delivered', 'completed');
    } else {
        mark('delivered', 'pending');
    }
    
    // Update progress line
    if (progressLine) {
        const statusOrder = ['placed', 'paid', 'processing', 'out_for_delivery', 'delivered'];
        const currentIndex = statusOrder.indexOf(orderStatus);
        const totalSteps = 5;
        
        let progress = 0;
        if (currentIndex >= 0) {
            // Progress up to current step (add 1 to include current step)
            progress = ((currentIndex + 1) / totalSteps) * 100;
        } else {
            // Default to step 1 (placed)
            progress = (1 / totalSteps) * 100;
        }
        
        progressLine.style.width = `${progress}%`;
    }
}

// Auto-update on page load
document.addEventListener('DOMContentLoaded', function() {
    const tracker = document.getElementById('deliveryTracker');
    if (!tracker) return;
    
    // Get order status from data attribute
    const orderStatus = tracker.dataset.orderStatus || 'placed';
    
    // Store original clickable state from server-rendered HTML BEFORE any updates
    const originalClickableState = new Map();
    tracker.querySelectorAll('.tracker-step').forEach(step => {
        const stepName = step.dataset.step;
        if (stepName) {
            originalClickableState.set(stepName, {
                isClickable: step.classList.contains('clickable'),
                isLocked: step.classList.contains('locked'),
                hasOrderId: !!step.dataset.orderId,
                hasNewStatus: !!step.dataset.newStatus,
                orderId: step.dataset.orderId || '',
                newStatus: step.dataset.newStatus || ''
            });
        }
    });
    
    // Initial update
    updateTimeline(orderStatus);
    
    // CRITICAL: Restore clickable state and data attributes after updateTimeline runs
    originalClickableState.forEach((state, stepName) => {
        const step = tracker.querySelector(`.step[data-step="${stepName}"]`);
        if (step && state.isClickable && !step.classList.contains('completed')) {
            step.classList.add('clickable');
            step.classList.remove('locked');
            // Restore data attributes if they exist
            if (state.orderId) {
                step.dataset.orderId = state.orderId;
            }
            if (state.newStatus) {
                step.dataset.newStatus = state.newStatus;
            }
        }
    });
    
    // Auto-refresh every 30 seconds if not delivered
    if (orderStatus !== 'delivered') {
        setInterval(function() {
            // Fetch updated status via AJAX
            const orderId = tracker.dataset.orderId;
            if (orderId) {
                fetch(window.buildUrl(`/orders/${orderId}/status`), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status && data.status !== tracker.dataset.orderStatus) {
                        tracker.dataset.orderStatus = data.status;
                        updateTimeline(data.status);
                        
                        // Restore clickable state after update
                        originalClickableState.forEach((state, stepName) => {
                            const step = tracker.querySelector(`.step[data-step="${stepName}"]`);
                            if (step && state.isClickable && !step.classList.contains('completed')) {
                                step.classList.add('clickable');
                                step.classList.remove('locked');
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching status:', error);
                });
            }
        }, 30000);
    }
});
