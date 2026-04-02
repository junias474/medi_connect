// ===================================
// SPLASH SCREEN
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const splashScreen = document.getElementById('splash-screen');

    if (splashScreen) {
        setTimeout(function() {
            splashScreen.style.opacity = '0';
            splashScreen.style.transition = 'opacity 0.5s ease';
            setTimeout(function() {
                splashScreen.style.display = 'none';
            }, 500);
        }, 4500);
    }
});

// ===================================
// HERO BACKGROUND SLIDESHOW
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const images = [
        'design/assets/hero/slide1.jpg',
        'design/assets/hero/slide2.jpg',
        'design/assets/hero/slide3.jpg',
        'design/assets/hero/slide4.jpg',
    ];

    const heroSection = document.querySelector('.hero-section');
    if (!heroSection) return;

    // Créer le conteneur du slideshow
    const slideshowWrapper = document.createElement('div');
    slideshowWrapper.className = 'hero-slideshow';

    // Créer les slides
    images.forEach(function(src, index) {
        const slide = document.createElement('div');
        slide.className = 'hero-slide' + (index === 0 ? ' active' : '');
        slide.style.backgroundImage = "url('" + src + "')";
        slideshowWrapper.appendChild(slide);
    });

    // Overlay sombre pour lisibilité du texte
    const overlay = document.createElement('div');
    overlay.className = 'hero-slideshow-overlay';
    slideshowWrapper.appendChild(overlay);

    // Créer les points indicateurs
    const dotsContainer = document.createElement('div');
    dotsContainer.className = 'hero-slideshow-dots';

    images.forEach(function(_, index) {
        const dot = document.createElement('button');
        dot.className = 'hero-dot' + (index === 0 ? ' active' : '');
        dot.setAttribute('aria-label', 'Slide ' + (index + 1));
        dotsContainer.appendChild(dot);

        dot.addEventListener('click', function() {
            clearInterval(autoplayTimer);
            goToSlide(index);
            autoplayTimer = setInterval(nextSlide, 5000);
        });
    });

    slideshowWrapper.appendChild(dotsContainer);

    // Insérer en premier enfant de la section hero
    heroSection.insertBefore(slideshowWrapper, heroSection.firstChild);

    // Logique de défilement
    let currentSlide = 0;
    const slides = slideshowWrapper.querySelectorAll('.hero-slide');
    const dots = slideshowWrapper.querySelectorAll('.hero-dot');

    function goToSlide(n) {
        slides[currentSlide].classList.remove('active');
        dots[currentSlide].classList.remove('active');
        currentSlide = n;
        slides[currentSlide].classList.add('active');
        dots[currentSlide].classList.add('active');
    }

    function nextSlide() {
        goToSlide((currentSlide + 1) % slides.length);
    }

    var autoplayTimer = setInterval(nextSlide, 5000);

    // Pause au survol
    heroSection.addEventListener('mouseenter', function() {
        clearInterval(autoplayTimer);
    });

    heroSection.addEventListener('mouseleave', function() {
        autoplayTimer = setInterval(nextSlide, 5000);
    });
});

// ===================================
// MENU MOBILE TOGGLE
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const mobileNav = document.getElementById('mobile-nav');

    if (mobileMenuToggle && mobileNav) {
        mobileMenuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            this.classList.toggle('active');
            mobileNav.classList.toggle('active');
        });

        const mobileNavLinks = mobileNav.querySelectorAll('.mobile-nav-link');
        mobileNavLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                mobileMenuToggle.classList.remove('active');
                mobileNav.classList.remove('active');
            });
        });

        document.addEventListener('click', function(e) {
            if (!mobileMenuToggle.contains(e.target) && !mobileNav.contains(e.target)) {
                mobileMenuToggle.classList.remove('active');
                mobileNav.classList.remove('active');
            }
        });
    }
});

// ===================================
// EFFET D'OMBRE AU SCROLL
// ===================================
window.addEventListener('scroll', function() {
    const header = document.querySelector('.main-header');
    if (header) {
        if (window.pageYOffset > 10) {
            header.style.boxShadow = '0 4px 12px rgba(0, 102, 204, 0.15)';
        } else {
            header.style.boxShadow = '0 1px 0 rgba(0, 102, 204, 0.08)';
        }
    }
});

// ===================================
// SMOOTH SCROLL POUR LES ANCRES
// ===================================
document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
    anchor.addEventListener('click', function(e) {
        const targetId = this.getAttribute('href');

        if (targetId === '#' || targetId === '') return;

        const targetElement = document.querySelector(targetId);

        if (targetElement) {
            e.preventDefault();
            const headerHeight = document.querySelector('.main-header').offsetHeight;
            const targetPosition = targetElement.offsetTop - headerHeight - 20;

            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });

            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
            const mobileNav = document.getElementById('mobile-nav');
            if (mobileMenuToggle && mobileNav) {
                mobileMenuToggle.classList.remove('active');
                mobileNav.classList.remove('active');
            }
        }
    });
});

// ===================================
// ANIMATIONS AU SCROLL
// ===================================
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

document.addEventListener('DOMContentLoaded', function() {
    const animatedElements = document.querySelectorAll('.service-card, .step-item, .testimonial-card, .value-item');

    animatedElements.forEach(function(el) {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
        observer.observe(el);
    });
});

// ===================================
// RECHERCHE
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search_input');

    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const searchTerm = this.value.trim();
                if (searchTerm) {
                    console.log('Recherche pour:', searchTerm);
                    // TODO: window.location.href = 'search.php?q=' + encodeURIComponent(searchTerm);
                }
            }
        });
    }
});

// ===================================
// BOUTONS DE NAVIGATION
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    // Bouton Connexion (uniquement #login_button, pas tous les .btn-secondary)
    const loginButton = document.getElementById('login_button');
    if (loginButton) {
        loginButton.addEventListener('click', function() {
            window.location.href = 'auth/login.php';
        });
    }

    // Bouton Inscription
    const signupButton = document.getElementById('signup_button');
    if (signupButton) {
        signupButton.addEventListener('click', function() {
            window.location.href = 'auth/register.php';
        });
    }

    // Bouton CTA - Créer un compte
    const ctaPrimary = document.querySelector('.btn-cta-primary');
    if (ctaPrimary) {
        ctaPrimary.addEventListener('click', function() {
            window.location.href = 'auth/register.php';
        });
    }

    // Boutons Prendre rendez-vous
    const appointmentButtons = document.querySelectorAll('.btn-hero-primary, .btn-cta-secondary');
    appointmentButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            window.location.href = 'auth/register.php';
        });
    });

    // Bouton Voir la démo — scroll vers "How it works"
    const demoButtons = document.querySelectorAll('.btn-hero-secondary');
    demoButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const howItWorksSection = document.querySelector('.how-it-works');
            if (howItWorksSection) {
                const headerHeight = document.querySelector('.main-header').offsetHeight;
                const targetPosition = howItWorksSection.offsetTop - headerHeight - 20;
                window.scrollTo({ top: targetPosition, behavior: 'smooth' });
            }
        });
    });
});

// ===================================
// FORMULAIRE DE CONTACT RAPIDE
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const quickContactForm = document.getElementById('quick-contact-form');

    if (quickContactForm) {
        quickContactForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const name    = this.querySelector('input[type="text"]').value.trim();
            const email   = this.querySelector('input[type="email"]').value.trim();
            const message = this.querySelector('textarea').value.trim();

            if (!name || !email || !message) {
                showNotification('Veuillez remplir tous les champs.', 'error');
                return;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showNotification('Veuillez entrer une adresse email valide.', 'error');
                return;
            }

            console.log('Message envoyé:', { name, email, message });

            const submitButton = this.querySelector('.btn-submit');
            const originalHTML = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="bi bi-check-circle"></i> Message envoyé !';
            submitButton.style.background = 'linear-gradient(135deg, #10B981, #059669)';
            submitButton.disabled = true;

            this.reset();

            setTimeout(function() {
                submitButton.innerHTML = originalHTML;
                submitButton.style.background = '';
                submitButton.disabled = false;
            }, 3000);

            showNotification('Votre message a été envoyé avec succès !', 'success');

            // TODO: appel AJAX réel
            /*
            fetch('contact_handler.php', { method: 'POST', body: new FormData(this) })
                .then(r => r.json())
                .then(data => {
                    if (data.success) showNotification('Message envoyé !', 'success');
                    else showNotification('Erreur lors de l\'envoi.', 'error');
                })
                .catch(() => showNotification('Une erreur est survenue.', 'error'));
            */
        });
    }
});

// ===================================
// ANIMATION DES STATISTIQUES
// ===================================
function animateCounter(element, target, duration) {
    duration = duration || 2000;
    let start = 0;
    const increment = target / (duration / 16);

    const timer = setInterval(function() {
        start += increment;
        if (start >= target) {
            element.textContent = target.toLocaleString();
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(start).toLocaleString();
        }
    }, 16);
}

const statsObserver = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
        if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
            entry.target.classList.add('animated');

            const h4 = entry.target.querySelector('h4');
            if (!h4) return;

            const originalText = h4.textContent;
            const number = parseInt(originalText.replace(/\D/g, ''));
            const suffix = originalText.replace(/[0-9,]/g, '').trim();

            if (!isNaN(number) && number > 0) {
                h4.textContent = '0';
                setTimeout(function() {
                    animateCounter(h4, number);
                    if (suffix) {
                        setTimeout(function() {
                            h4.textContent = number.toLocaleString() + suffix;
                        }, 2050);
                    }
                }, 200);
            }
        }
    });
}, { threshold: 0.5 });

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.stat-info').forEach(function(item) {
        statsObserver.observe(item);
    });
});

// ===================================
// EFFET PARALLAXE SIMPLE
// ===================================
window.addEventListener('scroll', function() {
    const scrolled = window.pageYOffset;
    document.querySelectorAll('.decoration-circle').forEach(function(element, index) {
        const speed = 0.3 + (index * 0.1);
        element.style.transform = 'translateY(' + (-(scrolled * speed)) + 'px)';
    });
});

// ===================================
// CARTES FLOTTANTES
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.floating-card').forEach(function(card, index) {
        card.style.animationDelay = (index * 0.5) + 's';

        card.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05) translateY(-10px)';
            this.style.transition = 'transform 0.3s ease';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });
});

// ===================================
// LAZY LOADING DES IMAGES
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const lazyImages = document.querySelectorAll('img[data-src]');

    const imageObserver = new IntersectionObserver(function(entries, obs) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                obs.unobserve(img);
            }
        });
    });

    lazyImages.forEach(function(img) { imageObserver.observe(img); });
});

// ===================================
// LOGO — RETOUR EN HAUT
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const logoSection = document.querySelector('.logo-section');
    if (logoSection) {
        logoSection.style.cursor = 'pointer';
        logoSection.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
});

// ===================================
// PRÉCHARGEMENT DES PAGES
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    ['services.php', 'about.php', 'contact.php'].forEach(function(page) {
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = page;
        document.head.appendChild(link);
    });
});

// ===================================
// DÉTECTION CONNEXION INTERNET
// ===================================
window.addEventListener('online', function() {
    showNotification('Connexion internet rétablie.', 'success');
});

window.addEventListener('offline', function() {
    showNotification('Vous êtes hors ligne. Certaines fonctionnalités peuvent ne pas être disponibles.', 'error');
});

// ===================================
// GESTION DES ERREURS GLOBALES
// ===================================
window.addEventListener('error', function(e) {
    console.error('Erreur détectée:', e.message);
});

// ===================================
// SYSTÈME DE NOTIFICATION
// ===================================
(function() {
    const notifStyle = document.createElement('style');
    notifStyle.textContent =
        '@keyframes mcSlideIn{from{transform:translateX(420px);opacity:0}to{transform:translateX(0);opacity:1}}' +
        '@keyframes mcSlideOut{from{transform:translateX(0);opacity:1}to{transform:translateX(420px);opacity:0}}';
    document.head.appendChild(notifStyle);
})();

function showNotification(message, type) {
    type = type || 'info';

    const colorMap = {
        success: { bg: '#ECFDF5', border: '#10B981', icon: '#10B981', iconClass: 'check-circle-fill' },
        error:   { bg: '#FEF2F2', border: '#EF4444', icon: '#EF4444', iconClass: 'x-circle-fill' },
        info:    { bg: '#EFF6FF', border: '#0066CC', icon: '#0066CC', iconClass: 'info-circle-fill' }
    };

    const c = colorMap[type] || colorMap.info;

    const notif = document.createElement('div');
    notif.style.cssText =
        'position:fixed;top:100px;right:20px;padding:1rem 1.25rem;' +
        'background:' + c.bg + ';border:1px solid ' + c.border + ';' +
        'border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,0.1);' +
        'z-index:10000;animation:mcSlideIn 0.35s ease-out;max-width:360px;' +
        'display:flex;align-items:center;gap:0.75rem;font-family:inherit;';

    notif.innerHTML =
        '<i class="bi bi-' + c.iconClass + '" style="font-size:1.4rem;color:' + c.icon + ';flex-shrink:0;"></i>' +
        '<span style="color:#1E293B;font-size:0.9rem;font-weight:500;line-height:1.4;">' + message + '</span>';

    document.body.appendChild(notif);

    setTimeout(function() {
        notif.style.animation = 'mcSlideOut 0.3s ease-out forwards';
        setTimeout(function() {
            if (notif.parentNode) notif.parentNode.removeChild(notif);
        }, 300);
    }, 4000);
}

// ===================================
// EXPORT DES UTILITAIRES
// ===================================
window.mediConnectUtils = {
    showNotification: showNotification,
    animateCounter: animateCounter
};

console.log('Medi-Connect — Application initialisée avec succès ✓');