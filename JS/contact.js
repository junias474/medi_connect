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
        mobileNavLinks.forEach(link => {
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
            header.style.boxShadow = '0 4px 12px rgba(0, 102, 204, 0.08)';
        } else {
            header.style.boxShadow = '0 1px 0 rgba(0, 102, 204, 0.08)';
        }
    }
});

// ===================================
// SMOOTH SCROLL
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
        }
    });
});

// ===================================
// FAQ ACCORDÉON
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        question.addEventListener('click', function() {
            // Fermer les autres items
            faqItems.forEach(otherItem => {
                if (otherItem !== item && otherItem.classList.contains('active')) {
                    otherItem.classList.remove('active');
                }
            });
            
            // Toggle l'item actuel
            item.classList.toggle('active');
        });
    });
});

// ===================================
// VALIDATION ET SOUMISSION DU FORMULAIRE
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contact-form');
    const formMessage = document.getElementById('form-message');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Réinitialiser les messages
            formMessage.className = 'form-message';
            formMessage.textContent = '';
            
            // Récupérer les valeurs
            const firstName = document.getElementById('first-name').value.trim();
            const lastName = document.getElementById('last-name').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const subject = document.getElementById('subject').value;
            const message = document.getElementById('message').value.trim();
            const consent = document.getElementById('consent').checked;
            
            // Validation
            if (!firstName || !lastName || !email || !subject || !message) {
                showMessage('Veuillez remplir tous les champs obligatoires.', 'error');
                return;
            }
            
            // Validation de l'email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showMessage('Veuillez entrer une adresse email valide.', 'error');
                return;
            }
            
            // Validation du consentement
            if (!consent) {
                showMessage('Vous devez accepter que vos données soient utilisées.', 'error');
                return;
            }
            
            // Validation du téléphone (optionnel mais si rempli)
            if (phone) {
                const phoneRegex = /^[\d\s\+\-\(\)]+$/;
                if (!phoneRegex.test(phone) || phone.length < 8) {
                    showMessage('Veuillez entrer un numéro de téléphone valide.', 'error');
                    return;
                }
            }
            
            // Validation de la longueur du message
            if (message.length < 10) {
                showMessage('Le message doit contenir au moins 10 caractères.', 'error');
                return;
            }
            
            // Désactiver le bouton de soumission
            const submitButton = contactForm.querySelector('.btn-submit');
            const originalText = submitButton.querySelector('.btn-text').textContent;
            submitButton.disabled = true;
            submitButton.querySelector('.btn-text').textContent = 'Envoi en cours...';
            
            // Simulation d'envoi (à remplacer par un vrai appel AJAX)
            setTimeout(() => {
                // Succès
                showMessage('Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.', 'success');
                
                // Réinitialiser le formulaire
                contactForm.reset();
                
                // Restaurer le bouton
                submitButton.disabled = false;
                submitButton.querySelector('.btn-text').textContent = originalText;
                
                // Scroll vers le message
                formMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                
            }, 1500);
            
            // TODO: Implémenter l'envoi réel via AJAX
            /*
            const formData = {
                firstName: firstName,
                lastName: lastName,
                email: email,
                phone: phone,
                subject: subject,
                message: message
            };
            
            fetch('contact_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    contactForm.reset();
                } else {
                    showMessage(data.message || 'Une erreur est survenue.', 'error');
                }
                submitButton.disabled = false;
                submitButton.querySelector('.btn-text').textContent = originalText;
            })
            .catch(error => {
                showMessage('Erreur de connexion. Veuillez réessayer.', 'error');
                submitButton.disabled = false;
                submitButton.querySelector('.btn-text').textContent = originalText;
            });
            */
        });
    }
    
    function showMessage(text, type) {
        formMessage.textContent = text;
        formMessage.className = `form-message ${type}`;
    }
});

// ===================================
// VALIDATION EN TEMPS RÉEL
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    
    // Validation de l'email en temps réel
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && !emailRegex.test(this.value)) {
                this.style.borderColor = '#EF4444';
            } else {
                this.style.borderColor = '';
            }
        });
        
        emailInput.addEventListener('input', function() {
            this.style.borderColor = '';
        });
    }
    
    // Validation du téléphone en temps réel
    if (phoneInput) {
        phoneInput.addEventListener('blur', function() {
            if (this.value) {
                const phoneRegex = /^[\d\s\+\-\(\)]+$/;
                if (!phoneRegex.test(this.value) || this.value.length < 8) {
                    this.style.borderColor = '#EF4444';
                } else {
                    this.style.borderColor = '';
                }
            }
        });
        
        phoneInput.addEventListener('input', function() {
            this.style.borderColor = '';
        });
    }
});

// ===================================
// ANIMATIONS AU SCROLL
// ===================================
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

document.addEventListener('DOMContentLoaded', function() {
    const animatedElements = document.querySelectorAll('.contact-card, .faq-item');
    
    animatedElements.forEach((el, index) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = `opacity 0.6s ease-out ${index * 0.05}s, transform 0.6s ease-out ${index * 0.05}s`;
        observer.observe(el);
    });
});

// ===================================
// BOUTONS D'ACTION
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const loginButtons = document.querySelectorAll('#login_button, .btn-secondary');
    loginButtons.forEach(button => {
        button.addEventListener('click', function() {
            window.location.href = 'login.php';
        });
    });
    
    const signupButtons = document.querySelectorAll('#signup_button, .btn-primary, .btn-cta');
    signupButtons.forEach(button => {
        button.addEventListener('click', function() {
            window.location.href = 'signup.php';
        });
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
                }
            }
        });
    }
});

// ===================================
// LOGO RETOUR EN HAUT
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
// COMPTEUR DE CARACTÈRES POUR LE MESSAGE
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    const messageTextarea = document.getElementById('message');
    
    if (messageTextarea) {
        const charCount = document.createElement('div');
        charCount.style.cssText = 'text-align: right; font-size: 0.875rem; color: #64748B; margin-top: 0.25rem;';
        messageTextarea.parentElement.appendChild(charCount);
        
        function updateCharCount() {
            const length = messageTextarea.value.length;
            charCount.textContent = `${length} caractères`;
            
            if (length > 0 && length < 10) {
                charCount.style.color = '#EF4444';
            } else if (length >= 10) {
                charCount.style.color = '#10B981';
            } else {
                charCount.style.color = '#64748B';
            }
        }
        
        messageTextarea.addEventListener('input', updateCharCount);
        updateCharCount();
    }
});