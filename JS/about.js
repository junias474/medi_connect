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
    const animatedElements = document.querySelectorAll(
        '.value-card, .pillar-card, .team-stat-card, .story-image-card, .commitment-card'
    );
    
    animatedElements.forEach((el, index) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = `opacity 0.6s ease-out ${index * 0.05}s, transform 0.6s ease-out ${index * 0.05}s`;
        observer.observe(el);
    });
});

// ===================================
// ANIMATION DES COMPTEURS
// ===================================
function animateCounter(element, target, duration = 2000) {
    let start = 0;
    const increment = target / (duration / 16);
    const isPlus = element.textContent.includes('+');
    
    const timer = setInterval(() => {
        start += increment;
        if (start >= target) {
            element.textContent = target.toLocaleString() + (isPlus ? '+' : '');
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(start).toLocaleString() + (isPlus ? '+' : '');
        }
    }, 16);
}

// Observer pour démarrer les animations
const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
            entry.target.classList.add('animated');
            
            const target = parseInt(entry.target.getAttribute('data-target'));
            if (!isNaN(target)) {
                entry.target.textContent = '0';
                setTimeout(() => {
                    animateCounter(entry.target, target, 2000);
                }, 200);
            }
        }
    });
}, { threshold: 0.5 });

document.addEventListener('DOMContentLoaded', function() {
    // Animer les compteurs de statistiques
    const numberValues = document.querySelectorAll('.number-value');
    numberValues.forEach(el => counterObserver.observe(el));
    
    // Animer les statistiques d'équipe
    const teamStats = document.querySelectorAll('.team-stat-card h3');
    teamStats.forEach(el => {
        const text = el.textContent.trim();
        const match = text.match(/(\d+)/);
        if (match) {
            el.setAttribute('data-target', match[1]);
            counterObserver.observe(el);
        }
    });
    
    // Animer les statistiques de l'histoire
    const storyStats = document.querySelectorAll('.story-stat h4');
    storyStats.forEach(el => {
        const text = el.textContent.trim();
        const match = text.match(/(\d+)/);
        if (match) {
            el.setAttribute('data-target', match[1]);
            counterObserver.observe(el);
        }
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
    
    const signupButtons = document.querySelectorAll('#signup_button, .btn-primary, .btn-cta.primary, .btn-commitment');
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