/**
 * GrihGO Digital & Tech — Animations Engine
 * Pure Vanilla JavaScript (Zero external libraries)
 * Features: Pseudo-3D Tilt, Particle Canvas, Scroll Reveal, Stats Counter
 */

(function () {
    'use strict';

    // Respect user's motion preferences
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* --------------------------------------------------------------------------
       1. Pseudo-3D Interactive Hero Tilt & Canvas Particles
       -------------------------------------------------------------------------- */
    function initHeroPseudo3D() {
        const scene = document.querySelector('.hero-3d-scene');
        const container = document.querySelector('.hero-visual-wrapper');
        const canvas = document.getElementById('heroParticleCanvas');

        if (!scene || prefersReducedMotion) return;

        // Mouse Parallax / 3D Tilt
        let targetRotateX = 0;
        let targetRotateY = 0;
        let currentRotateX = 0;
        let currentRotateY = 0;
        const ease = 0.08;

        if (container) {
            window.addEventListener('mousemove', function (e) {
                const rect = container.getBoundingClientRect();
                const centerX = rect.left + rect.width / 2;
                const centerY = rect.top + rect.height / 2;

                const deltaX = (e.clientX - centerX) / (window.innerWidth / 2);
                const deltaY = (e.clientY - centerY) / (window.innerHeight / 2);

                targetRotateY = deltaX * 18; // Max 18 deg Y tilt
                targetRotateX = -deltaY * 18; // Max 18 deg X tilt
            }, { passive: true });

            // Smooth interpolation loop
            function updateTilt() {
                currentRotateX += (targetRotateX - currentRotateX) * ease;
                currentRotateY += (targetRotateY - currentRotateY) * ease;

                scene.style.transform = `rotateX(${currentRotateX.toFixed(2)}deg) rotateY(${currentRotateY.toFixed(2)}deg)`;
                requestAnimationFrame(updateTilt);
            }
            requestAnimationFrame(updateTilt);
        }

        // Particle Canvas
        if (canvas && canvas.getContext) {
            const ctx = canvas.getContext('2d');
            let width, height;
            let particles = [];
            const particleCount = window.innerWidth < 768 ? 18 : 45;

            function resizeCanvas() {
                width = canvas.width = canvas.offsetWidth;
                height = canvas.height = canvas.offsetHeight;
            }
            resizeCanvas();
            window.addEventListener('resize', resizeCanvas, { passive: true });

            class Particle {
                constructor() {
                    this.reset();
                }

                reset() {
                    this.x = Math.random() * width;
                    this.y = Math.random() * height;
                    this.radius = Math.random() * 2 + 1;
                    this.color = Math.random() > 0.4 ? 'rgba(0, 153, 255, ' : 'rgba(16, 185, 129, ';
                    this.alpha = Math.random() * 0.6 + 0.2;
                    this.vx = (Math.random() - 0.5) * 0.6;
                    this.vy = (Math.random() - 0.5) * 0.6;
                }

                update() {
                    this.x += this.vx;
                    this.y += this.vy;

                    if (this.x < 0 || this.x > width || this.y < 0 || this.y > height) {
                        this.reset();
                    }
                }

                draw() {
                    ctx.beginPath();
                    ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
                    ctx.fillStyle = this.color + this.alpha + ')';
                    ctx.fill();
                }
            }

            for (let i = 0; i < particleCount; i++) {
                particles.push(new Particle());
            }

            function drawConnections() {
                for (let i = 0; i < particles.length; i++) {
                    for (let j = i + 1; j < particles.length; j++) {
                        const dx = particles[i].x - particles[j].x;
                        const dy = particles[i].y - particles[j].y;
                        const dist = Math.sqrt(dx * dx + dy * dy);

                        if (dist < 90) {
                            ctx.beginPath();
                            ctx.moveTo(particles[i].x, particles[i].y);
                            ctx.lineTo(particles[j].x, particles[j].y);
                            ctx.strokeStyle = `rgba(0, 153, 255, ${0.15 * (1 - dist / 90)})`;
                            ctx.lineWidth = 0.75;
                            ctx.stroke();
                        }
                    }
                }
            }

            function animateParticles() {
                ctx.clearRect(0, 0, width, height);

                particles.forEach(p => {
                    p.update();
                    p.draw();
                });

                drawConnections();
                requestAnimationFrame(animateParticles);
            }

            requestAnimationFrame(animateParticles);
        }
    }

    /* --------------------------------------------------------------------------
       2. Scroll Reveal Animations (IntersectionObserver)
       -------------------------------------------------------------------------- */
    function initScrollReveal() {
        if (!('IntersectionObserver' in window) || prefersReducedMotion) return;

        const observerOptions = {
            root: null,
            rootMargin: '0px 0px -40px 0px',
            threshold: 0.12
        };

        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    // If target has counter attribute, animate counter
                    if (entry.target.hasAttribute('data-counter')) {
                        animateCounter(entry.target);
                    }
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        const animElements = document.querySelectorAll(
            '.biz-card, .service-card, .ai-card, .product-card, .why-card, ' +
            '.process-step-item, .portfolio-card, .testimonial-card, .faq-item, [data-counter]'
        );

        animElements.forEach((el, index) => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(24px)';
            el.style.transition = `opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1) ${index % 4 * 0.1}s, transform 0.6s cubic-bezier(0.16, 1, 0.3, 1) ${index % 4 * 0.1}s`;
            revealObserver.observe(el);
        });

        // Add class on reveal
        document.addEventListener('animation-reveal', function () {
            // Callback placeholder if needed
        });
    }

    // Helper for revealing elements
    document.addEventListener('DOMContentLoaded', function () {
        const style = document.createElement('style');
        style.innerHTML = `
            .revealed {
                opacity: 1 !important;
                transform: translateY(0) !important;
            }
        `;
        document.head.appendChild(style);
    });

    /* --------------------------------------------------------------------------
       3. Stats Counter Animation
       -------------------------------------------------------------------------- */
    function animateCounter(el) {
        const target = parseInt(el.getAttribute('data-counter'), 10);
        const prefix = el.getAttribute('data-prefix') || '';
        const suffix = el.getAttribute('data-suffix') || '';
        const duration = 1600; // ms
        const startTime = performance.now();

        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            // EaseOutQuad
            const current = Math.floor(progress * (2 - progress) * target);

            el.textContent = `${prefix}${current}${suffix}`;

            if (progress < 1) {
                requestAnimationFrame(update);
            } else {
                el.textContent = `${prefix}${target}${suffix}`;
            }
        }

        requestAnimationFrame(update);
    }

    /* --------------------------------------------------------------------------
       Initialize on DOM Content Loaded
       -------------------------------------------------------------------------- */
    document.addEventListener('DOMContentLoaded', function () {
        initHeroPseudo3D();
        initScrollReveal();
    });
})();
