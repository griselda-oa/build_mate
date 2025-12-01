/**
 * Pending Dashboard Animations
 * Premium animations for supplier pending dashboard
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        initHeroAnimations();
        initProgressBar();
        initCardAnimations();
        initTimelineAnimations();
    });

    /**
     * Hero Entry Sequence
     * 1. Fade in background
     * 2. Scale in checkmark (600ms, delay 200ms, bounce)
     * 3. Slide up title (600ms, delay 400ms)
     * 4. Fade in subtitle (600ms, delay 600ms)
     * 5. Slide up buttons (600ms, delay 800ms)
     */
    function initHeroAnimations() {
        const hero = document.getElementById('pendingHero');
        const successIcon = document.getElementById('successIcon');
        const heroTitle = document.getElementById('heroTitle');
        const heroSubtitle = document.getElementById('heroSubtitle');
        const heroActions = document.getElementById('heroActions');

        if (!hero) return;

        // Fade in background
        hero.style.opacity = '0';
        setTimeout(() => {
            hero.style.transition = 'opacity 0.8s ease-out';
            hero.style.opacity = '1';
        }, 100);

        // Scale in checkmark icon
        setTimeout(() => {
            if (successIcon) {
                successIcon.classList.add('visible');
            }
        }, 200);

        // Slide up title
        setTimeout(() => {
            if (heroTitle) {
                heroTitle.classList.add('visible');
            }
        }, 400);

        // Fade in subtitle
        setTimeout(() => {
            if (heroSubtitle) {
                heroSubtitle.classList.add('visible');
            }
        }, 600);

        // Slide up buttons
        setTimeout(() => {
            if (heroActions) {
                heroActions.classList.add('visible');
            }
        }, 800);
    }

    /**
     * Progress Bar Animation
     * Animate width from 0% to 50% (under review stage)
     * Duration: 1500ms, Easing: ease-out
     * Pulse dot at current stage
     */
    function initProgressBar() {
        const progressLine = document.getElementById('progressLine');
        const activeStep = document.getElementById('activeStep');

        if (!progressLine) return;

        setTimeout(() => {
            progressLine.classList.add('animated');
        }, 300);

        // Pulse animation for active step
        if (activeStep) {
            const stepDot = activeStep.querySelector('.step-dot');
            if (stepDot) {
                setInterval(() => {
                    stepDot.style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        stepDot.style.transform = 'scale(1)';
                    }, 200);
                }, 2000);
            }
        }
    }

    /**
     * Feature Cards Stagger Animation
     * Cards fade in one by one
     * Delay: 100ms between each
     * Transform: translateY(20px) → 0
     */
    function initCardAnimations() {
        const cards = [
            document.getElementById('card1'),
            document.getElementById('card2'),
            document.getElementById('card3')
        ];

        cards.forEach((card, index) => {
            if (!card) return;

            setTimeout(() => {
                card.classList.add('visible');
            }, 1000 + (index * 100));
        });
    }

    /**
     * Timeline Scroll Animation
     * Activate when timeline enters viewport
     * Animate connecting line left to right
     * Icons pop in sequentially
     */
    function initTimelineAnimations() {
        const timelineSection = document.getElementById('timelineSection');
        const timelineLine = document.getElementById('timelineLine');
        const steps = [
            document.getElementById('timelineStep1'),
            document.getElementById('timelineStep2'),
            document.getElementById('timelineStep3')
        ];

        if (!timelineSection) return;

        // Intersection Observer for scroll-triggered animation
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Animate timeline line
                    if (timelineLine) {
                        setTimeout(() => {
                            timelineLine.classList.add('animated');
                        }, 200);
                    }

                    // Animate steps sequentially
                    steps.forEach((step, index) => {
                        if (step) {
                            setTimeout(() => {
                                step.classList.add('visible');
                            }, 400 + (index * 200));
                        }
                    });

                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.3
        });

        observer.observe(timelineSection);
    }

    /**
     * Status Card Animation
     * Fade in when page loads
     */
    function initStatusCard() {
        const statusCard = document.getElementById('statusCard');
        if (statusCard) {
            setTimeout(() => {
                statusCard.classList.add('visible');
            }, 500);
        }
    }

    // Initialize status card
    initStatusCard();

    /**
     * Smooth scroll for anchor links
     */
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

})();






