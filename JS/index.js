// ===================================
// SPLASH SCREEN
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const splashScreen = document.getElementById('splash-screen');
    
    // Cacher l'écran de démarrage après 5 secondes
    setTimeout(function() {
        splashScreen.style.display = 'none';
    }, 5000);
});

// ===================================
// MENU MOBILE TOGGLE
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const mobileNav = document.getElementById('mobile-nav');
    
    if (mobileMenuToggle && mobileNav) {
        // Toggle menu au clic
        mobileMenuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            this.classList.toggle('active');
            mobileNav.classList.toggle('active');
        });
        
        // Fermer le menu en cliquant sur un lien
        const mobileNavLinks = mobileNav.querySelectorAll('.mobile-nav-link');
        mobileNavLinks.forEach(link => {
            link.addEventListener('click', function() {
                mobileMenuToggle.classList.remove('active');
                mobileNav.classList.remove('active');
            });
        });
        
        // Fermer le menu en cliquant à l'extérieur
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
            header.style.boxShadow = '0 4px 12px rgba(0, 102, 204, 0.08)';
        } else {
            header.style.boxShadow = '0 1px 0 rgba(0, 102, 204, 0.08)';
        }
    }
});

// ===================================
// SMOOTH SCROLL POUR LES ANCRES
// ===================================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
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
            
            // Fermer le menu mobile si ouvert
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
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observer les sections pour les animations
document.addEventListener('DOMContentLoaded', function() {
    const animatedElements = document.querySelectorAll('.service-card, .step-item, .testimonial-card, .value-item');
    
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
        observer.observe(el);
    });
});

// ===================================
// RECHERCHE (FONCTIONNALITÉ DE BASE)
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search_input');
    
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const searchTerm = this.value.trim();
                
                if (searchTerm) {
                    // Pour l'instant, redirection vers une page de recherche
                    console.log('Recherche pour:', searchTerm);
                    alert('Fonctionnalité de recherche - Terme: ' + searchTerm);
                    // TODO: Implémenter la recherche réelle
                    // window.location.href = 'search.php?q=' + encodeURIComponent(searchTerm);
                }
            }
        });
    }
});

// ===================================
// BOUTONS DE NAVIGATION
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    // Bouton Connexion
    const loginButtons = document.querySelectorAll('#login_button, .btn-secondary');
    loginButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Redirection vers la page de connexion
            window.location.href = 'login.php';
        });
    });
    
    // Bouton Commencer / Inscription
    const signupButtons = document.querySelectorAll('#signup_button, .btn-primary, .btn-cta-primary');
    signupButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!button.type || button.type !== 'submit') {
                e.preventDefault();
                // Redirection vers la page d'inscription
                window.location.href = 'signup.php';
            }
        });
    });
    
    // Bouton Prendre rendez-vous
    const appointmentButtons = document.querySelectorAll('.btn-hero-primary, .btn-cta-secondary');
    appointmentButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Vérifier si l'utilisateur est connecté
            // Pour l'instant, redirection simple
            window.location.href = 'signup.php';
        });
    });
    
    // Bouton Voir la démo
    const demoButtons = document.querySelectorAll('.btn-hero-secondary');
    demoButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            // Scroll vers la section "Comment ça marche"
            const howItWorksSection = document.querySelector('.how-it-works');
            if (howItWorksSection) {
                const headerHeight = document.querySelector('.main-header').offsetHeight;
                const targetPosition = howItWorksSection.offsetTop - headerHeight - 20;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
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
            
            // Récupérer les valeurs du formulaire
            const formData = new FormData(this);
            const name = this.querySelector('input[type="text"]').value.trim();
            const email = this.querySelector('input[type="email"]').value.trim();
            const message = this.querySelector('textarea').value.trim();
            
            // Validation de base
            if (!name || !email || !message) {
                alert('Veuillez remplir tous les champs.');
                return;
            }
            
            // Validation de l'email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Veuillez entrer une adresse email valide.');
                return;
            }
            
            // Simulation d'envoi (à remplacer par un vrai appel AJAX)
            console.log('Message envoyé:', { name, email, message });
            
            // Feedback utilisateur
            const submitButton = this.querySelector('.btn-submit');
            const originalText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="bi bi-check-circle"></i> Message envoyé !';
            submitButton.style.background = 'linear-gradient(135deg, #10B981, #059669)';
            
            // Réinitialiser le formulaire
            this.reset();
            
            // Restaurer le bouton après 3 secondes
            setTimeout(() => {
                submitButton.innerHTML = originalText;
                submitButton.style.background = '';
            }, 3000);
            
            // TODO: Implémenter l'envoi réel via AJAX
            /*
            fetch('contact_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Succès
                } else {
                    // Erreur
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
            */
        });
    }
});

// ===================================
// ANIMATION DES STATISTIQUES
// ===================================
function animateCounter(element, target, duration = 2000) {
    let start = 0;
    const increment = target / (duration / 16);
    
    const timer = setInterval(() => {
        start += increment;
        if (start >= target) {
            element.textContent = target.toLocaleString();
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(start).toLocaleString();
        }
    }, 16);
}

// Observer pour démarrer l'animation quand les stats sont visibles
const statsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
            entry.target.classList.add('animated');
            
            // Animer le compteur
            const h4 = entry.target.querySelector('h4');
            const text = h4.textContent;
            const number = parseInt(text.replace(/\D/g, ''));
            
            if (!isNaN(number)) {
                h4.textContent = '0';
                setTimeout(() => {
                    animateCounter(h4, number);
                }, 200);
            }
        }
    });
}, { threshold: 0.5 });

document.addEventListener('DOMContentLoaded', function() {
    const statItems = document.querySelectorAll('.stat-info');
    statItems.forEach(item => statsObserver.observe(item));
});

// ===================================
// EFFET PARALLAXE SIMPLE
// ===================================
window.addEventListener('scroll', function() {
    const scrolled = window.pageYOffset;
    const parallaxElements = document.querySelectorAll('.decoration-circle');
    
    parallaxElements.forEach((element, index) => {
        const speed = 0.3 + (index * 0.1);
        const yPos = -(scrolled * speed);
        element.style.transform = `translateY(${yPos}px)`;
    });
});

// ===================================
// GESTION DES CARTES FLOTTANTES
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const floatingCards = document.querySelectorAll('.floating-card');
    
    floatingCards.forEach((card, index) => {
        // Animation de flottement avec des délais différents
        card.style.animationDelay = `${index * 0.5}s`;
        
        // Effet de hover interactif
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
// LAZY LOADING DES IMAGES (SI AJOUTÉES)
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
});

// ===================================
// GESTION DU LOGO (RETOUR EN HAUT)
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const logoSection = document.querySelector('.logo-section');
    
    if (logoSection) {
        logoSection.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
});

// ===================================
// PRÉCHARGEMENT DES PAGES
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    // Précharger les pages importantes
    const pagesToPreload = ['services.php', 'about.php', 'contact.php'];
    
    pagesToPreload.forEach(page => {
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = page;
        document.head.appendChild(link);
    });
});

// ===================================
// GESTION DES ERREURS GLOBALES
// ===================================
window.addEventListener('error', function(e) {
    console.error('Erreur détectée:', e.message);
    // Vous pouvez ajouter un système de logging ici
});

// ===================================
// DÉTECTION DE LA CONNEXION INTERNET
// ===================================
window.addEventListener('online', function() {
    console.log('Connexion internet rétablie');
});

window.addEventListener('offline', function() {
    console.log('Connexion internet perdue');
    alert('Attention: Vous êtes hors ligne. Certaines fonctionnalités peuvent ne pas être disponibles.');
});

// ===================================
// SYSTÈME DE NOTIFICATION (OPTIONNEL)
// ===================================
function showNotification(message, type = 'info') {
    // Créer l'élément de notification
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        padding: 1rem 1.5rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 102, 204, 0.2);
        z-index: 10000;
        animation: slideInRight 0.3s ease-out;
        max-width: 350px;
    `;
    
    notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'info-circle-fill'}" 
               style="font-size: 1.5rem; color: ${type === 'success' ? '#10B981' : '#0066CC'};"></i>
            <span style="color: #1E293B; font-weight: 500;">${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Retirer après 4 secondes
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease-out';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 4000);
}

// ===================================
// ANIMATION CSS POUR LES NOTIFICATIONS
// ===================================
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// ===================================
// EXPORT DES FONCTIONS UTILITAIRES
// ===================================
window.mediConnectUtils = {
    showNotification: showNotification,
    animateCounter: animateCounter
};