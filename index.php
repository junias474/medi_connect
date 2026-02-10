<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Medi-Connect - Plateforme de consultation médicale en ligne. Prenez rendez-vous avec des médecins qualifiés facilement.">
    <link rel="stylesheet" href="design/index.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Medi-Connect - Votre santé, notre priorité</title>
</head>
<body>
    <!-- Écran de démarrage -->
    <div class="splash-screen" id="splash-screen">
        <div class="splash-content">
            <div class="splash-logo">
                <img src="design/assets/medi_connect_logo.png" alt="Medi-Connect Logo">
            </div>
            <h1 class="splash-title">
                <span class="splash-main">Medi</span><span class="splash-accent">Connect</span>
            </h1>
            <p class="splash-tagline">Votre santé, notre priorité</p>
            <div class="splash-loader">
                <div class="loader-bar"></div>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="main-content" id="main-content">
        <!-- HEADER -->
        <header class="main-header">
            <div class="header-container">
                <!-- Logo -->
                <div class="logo-section">
                    <div class="logo-icon">
                        <img src="design/assets/medi_connect_logo.png" alt="Medi-Connect Logo">
                    </div>
                    <h1 class="logo-name">
                        <span class="logo-main">Medi</span><span class="logo-accent">Connect</span>
                    </h1>
                </div>
                
                <!-- Recherche -->
                <div class="search-area">
                    <i class="bi bi-search search-icon"></i>
                    <input type="search" id="search_input" placeholder="Rechercher un médecin, spécialité...">
                </div>
                
                <!-- Navigation -->
                <nav class="main-nav">
                    <a href="#services" class="nav-link">Services</a>
                    <a href="#apropos" class="nav-link">À Propos</a>
                    <a href="#contact" class="nav-link">Contact</a>
                </nav>
                
                <!-- Boutons utilisateur -->
                <div class="user-actions">
                    <button class="btn btn-secondary" id="login_button">
                        <i class="bi bi-person"></i>
                        Connexion
                    </button>
                    <button class="btn btn-primary" id="signup_button">
                        Commencer
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
                
                <!-- Menu mobile toggle -->
                <button class="mobile-menu-toggle" id="mobile-menu-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
            
            <!-- Navigation mobile -->
            <nav class="mobile-nav" id="mobile-nav">
                <a href="#services" class="mobile-nav-link">Services</a>
                <a href="#apropos" class="mobile-nav-link">À Propos</a>
                <a href="#contact" class="mobile-nav-link">Contact</a>
                <div class="mobile-nav-actions">
                    <button class="btn btn-secondary btn-block">
                        <i class="bi bi-person"></i>
                        Connexion
                    </button>
                    <button class="btn btn-primary btn-block">
                        Commencer
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </nav>
        </header>

        <!-- HERO SECTION -->
        <section class="hero-section">
            <div class="hero-container">
                <div class="hero-content">
                    <div class="hero-badge">
                        <i class="bi bi-shield-check"></i>
                        <span>Plateforme Sécurisée & Certifiée</span>
                    </div>
                    
                    <h2 class="hero-title">
                        Consultez un médecin 
                        <span class="hero-highlight">où que vous soyez</span>
                    </h2>
                    
                    <p class="hero-description">
                        Accédez à des consultations médicales de qualité en ligne, prenez rendez-vous facilement 
                        et trouvez le centre de santé le plus proche de vous.
                    </p>
                    
                    <div class="hero-actions">
                        <button class="btn-hero btn-hero-primary">
                            <i class="bi bi-calendar-check"></i>
                            Prendre rendez-vous
                        </button>
                        <button class="btn-hero btn-hero-secondary">
                            <i class="bi bi-play-circle"></i>
                            Voir la démo
                        </button>
                    </div>
                    
                    <div class="hero-stats">
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="stat-info">
                                <h4>5000+</h4>
                                <p>Patients satisfaits</p>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="bi bi-person-badge"></i>
                            </div>
                            <div class="stat-info">
                                <h4>150+</h4>
                                <p>Médecins qualifiés</p>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div class="stat-info">
                                <h4>24/7</h4>
                                <p>Disponibilité</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="hero-visual">
                    <div class="hero-image-wrapper">
                        <div class="floating-card card-1">
                            <i class="bi bi-heart-pulse"></i>
                            <div>
                                <h5>Consultation rapide</h5>
                                <p>En moins de 15 min</p>
                            </div>
                        </div>
                        <div class="floating-card card-2">
                            <i class="bi bi-shield-check"></i>
                            <div>
                                <h5>100% Sécurisé</h5>
                                <p>Données protégées</p>
                            </div>
                        </div>
                        <div class="floating-card card-3">
                            <i class="bi bi-star-fill"></i>
                            <div>
                                <h5>4.9/5</h5>
                                <p>Note moyenne</p>
                            </div>
                        </div>
                        <!-- Placeholder pour image principale -->
                        <div class="hero-main-image">
                            <i class="bi bi-hospital"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Décoration de fond -->
            <div class="hero-bg-decoration">
                <div class="decoration-circle circle-1"></div>
                <div class="decoration-circle circle-2"></div>
                <div class="decoration-circle circle-3"></div>
            </div>
        </section>

        <!-- SERVICES PREVIEW SECTION -->
        <section class="services-preview" id="services">
            <div class="section-container">
                <div class="section-header">
                    <span class="section-label">Nos Services</span>
                    <h2 class="section-title">Ce que nous offrons</h2>
                    <p class="section-description">
                        Des solutions complètes pour votre santé et votre bien-être
                    </p>
                </div>
                
                <div class="services-grid">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="bi bi-camera-video"></i>
                        </div>
                        <h3>Consultation en ligne</h3>
                        <p>Consultez un médecin par vidéo depuis le confort de votre domicile. Rapide, pratique et sécurisé.</p>
                        <ul class="service-features">
                            <li><i class="bi bi-check-circle"></i> Vidéoconférence HD</li>
                            <li><i class="bi bi-check-circle"></i> Ordonnance numérique</li>
                            <li><i class="bi bi-check-circle"></i> Historique médical</li>
                        </ul>
                    </div>
                    
                    <div class="service-card featured">
                        <div class="featured-badge">Populaire</div>
                        <div class="service-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <h3>Prise de rendez-vous</h3>
                        <p>Planifiez vos consultations selon vos disponibilités et celles des médecins. Simple et rapide.</p>
                        <ul class="service-features">
                            <li><i class="bi bi-check-circle"></i> Disponibilités en temps réel</li>
                            <li><i class="bi bi-check-circle"></i> Rappels automatiques</li>
                            <li><i class="bi bi-check-circle"></i> Reprogrammation facile</li>
                        </ul>
                    </div>
                    
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <h3>Orientation médicale</h3>
                        <p>Trouvez le centre de santé le plus proche de vous grâce à notre système de géolocalisation.</p>
                        <ul class="service-features">
                            <li><i class="bi bi-check-circle"></i> Carte interactive</li>
                            <li><i class="bi bi-check-circle"></i> Itinéraires optimisés</li>
                            <li><i class="bi bi-check-circle"></i> Informations détaillées</li>
                        </ul>
                    </div>
                </div>
                
                <div class="section-cta">
                    <a href="services.php" class="btn-link">
                        Découvrir tous nos services
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- HOW IT WORKS SECTION -->
        <section class="how-it-works">
            <div class="section-container">
                <div class="section-header">
                    <span class="section-label">Comment ça marche</span>
                    <h2 class="section-title">Consultez un médecin en 3 étapes</h2>
                </div>
                
                <div class="steps-container">
                    <div class="step-item">
                        <div class="step-number">01</div>
                        <div class="step-icon">
                            <i class="bi bi-person-plus"></i>
                        </div>
                        <h3>Créez votre compte</h3>
                        <p>Inscrivez-vous gratuitement en quelques secondes avec vos informations de base.</p>
                    </div>
                    
                    <div class="step-connector"></div>
                    
                    <div class="step-item">
                        <div class="step-number">02</div>
                        <div class="step-icon">
                            <i class="bi bi-clipboard2-pulse"></i>
                        </div>
                        <h3>Décrivez vos symptômes</h3>
                        <p>Renseignez vos symptômes et prenez rendez-vous avec un médecin disponible.</p>
                    </div>
                    
                    <div class="step-connector"></div>
                    
                    <div class="step-item">
                        <div class="step-number">03</div>
                        <div class="step-icon">
                            <i class="bi bi-camera-video"></i>
                        </div>
                        <h3>Consultez en ligne</h3>
                        <p>Connectez-vous à votre consultation vidéo et recevez votre ordonnance.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ABOUT PREVIEW SECTION -->
        <section class="about-preview" id="apropos">
            <div class="section-container">
                <div class="about-grid">
                    <div class="about-content">
                        <span class="section-label">À Propos de Nous</span>
                        <h2 class="section-title">Une plateforme pensée pour votre santé</h2>
                        <p class="about-text">
                            Medi-Connect est né de la volonté de rendre les soins de santé plus accessibles à tous. 
                            Nous croyons que chacun mérite un accès facile et rapide à des professionnels de santé qualifiés, 
                            quel que soit l'endroit où il se trouve.
                        </p>
                        <p class="about-text">
                            Notre mission est de transformer l'expérience de consultation médicale en combinant technologie 
                            moderne et expertise médicale de qualité, tout en garantissant la sécurité et la confidentialité 
                            de vos données.
                        </p>
                        
                        <div class="about-values">
                            <div class="value-item">
                                <i class="bi bi-shield-check"></i>
                                <div>
                                    <h4>Sécurité</h4>
                                    <p>Vos données sont protégées</p>
                                </div>
                            </div>
                            <div class="value-item">
                                <i class="bi bi-award"></i>
                                <div>
                                    <h4>Qualité</h4>
                                    <p>Médecins certifiés et expérimentés</p>
                                </div>
                            </div>
                            <div class="value-item">
                                <i class="bi bi-clock"></i>
                                <div>
                                    <h4>Disponibilité</h4>
                                    <p>Service accessible 24/7</p>
                                </div>
                            </div>
                        </div>
                        
                        <a href="about.php" class="btn-link">
                            En savoir plus sur notre mission
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    
                    <div class="about-visual">
                        <div class="about-image-card">
                            <i class="bi bi-heart-pulse-fill"></i>
                            <h4>Notre Vision</h4>
                            <p>Démocratiser l'accès aux soins de santé grâce à la technologie</p>
                        </div>
                        <div class="stats-mini">
                            <div class="stat-mini-item">
                                <h3>98%</h3>
                                <p>Taux de satisfaction</p>
                            </div>
                            <div class="stat-mini-item">
                                <h3>2020</h3>
                                <p>Année de création</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- TESTIMONIALS SECTION -->
        <section class="testimonials">
            <div class="section-container">
                <div class="section-header">
                    <span class="section-label">Témoignages</span>
                    <h2 class="section-title">Ce que disent nos patients</h2>
                </div>
                
                <div class="testimonials-grid">
                    <div class="testimonial-card">
                        <div class="testimonial-rating">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="testimonial-text">
                            "Service exceptionnel ! J'ai pu consulter un médecin rapidement sans me déplacer. 
                            L'interface est intuitive et les médecins sont très professionnels."
                        </p>
                        <div class="testimonial-author">
                            <div class="author-avatar">
                                <i class="bi bi-person"></i>
                            </div>
                            <div>
                                <h4>Marie K.</h4>
                                <p>Patiente depuis 2023</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="testimonial-card">
                        <div class="testimonial-rating">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="testimonial-text">
                            "Parfait pour les urgences non critiques ! J'ai évité des heures d'attente à l'hôpital. 
                            Le médecin a été à l'écoute et a répondu à toutes mes questions."
                        </p>
                        <div class="testimonial-author">
                            <div class="author-avatar">
                                <i class="bi bi-person"></i>
                            </div>
                            <div>
                                <h4>Jean-Paul N.</h4>
                                <p>Patient depuis 2022</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="testimonial-card">
                        <div class="testimonial-rating">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="testimonial-text">
                            "Une révolution dans le domaine de la santé ! La prise de rendez-vous est simple 
                            et les consultations vidéo sont de très bonne qualité."
                        </p>
                        <div class="testimonial-author">
                            <div class="author-avatar">
                                <i class="bi bi-person"></i>
                            </div>
                            <div>
                                <h4>Sophie M.</h4>
                                <p>Patiente depuis 2024</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CONTACT PREVIEW SECTION -->
        <section class="contact-preview" id="contact">
            <div class="section-container">
                <div class="contact-grid">
                    <div class="contact-info">
                        <span class="section-label">Contactez-nous</span>
                        <h2 class="section-title">Besoin d'aide ou d'informations ?</h2>
                        <p class="contact-description">
                            Notre équipe est disponible pour répondre à toutes vos questions. 
                            N'hésitez pas à nous contacter.
                        </p>
                        
                        <div class="contact-methods">
                            <div class="contact-method">
                                <div class="method-icon">
                                    <i class="bi bi-envelope"></i>
                                </div>
                                <div>
                                    <h4>Email</h4>
                                    <p>contact@medi-connect.com</p>
                                </div>
                            </div>
                            <div class="contact-method">
                                <div class="method-icon">
                                    <i class="bi bi-telephone"></i>
                                </div>
                                <div>
                                    <h4>Téléphone</h4>
                                    <p>+237 6XX XXX XXX</p>
                                </div>
                            </div>
                            <div class="contact-method">
                                <div class="method-icon">
                                    <i class="bi bi-clock"></i>
                                </div>
                                <div>
                                    <h4>Horaires</h4>
                                    <p>24/7 - Disponible tous les jours</p>
                                </div>
                            </div>
                        </div>
                        
                        <a href="contact.php" class="btn-link">
                            Accéder au formulaire de contact
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    
                    <div class="contact-quick-form">
                        <h3>Message rapide</h3>
                        <form class="quick-form" id="quick-contact-form">
                            <div class="form-group">
                                <input type="text" placeholder="Votre nom" required>
                            </div>
                            <div class="form-group">
                                <input type="email" placeholder="Votre email" required>
                            </div>
                            <div class="form-group">
                                <textarea placeholder="Votre message" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="btn-submit">
                                Envoyer
                                <i class="bi bi-send"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA SECTION -->
        <section class="cta-section">
            <div class="section-container">
                <div class="cta-content">
                    <h2>Prêt à prendre soin de votre santé ?</h2>
                    <p>Rejoignez des milliers de patients qui font confiance à Medi-Connect</p>
                    <div class="cta-actions">
                        <button class="btn-cta btn-cta-primary">
                            Créer mon compte
                            <i class="bi bi-arrow-right"></i>
                        </button>
                        <button class="btn-cta btn-cta-secondary">
                            Prendre rendez-vous
                            <i class="bi bi-calendar-check"></i>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="main-footer">
            <div class="footer-container">
                <div class="footer-grid">
                    <div class="footer-brand">
                        <div class="footer-logo">
                            <div class="logo-icon">
                                <img src="design/assets/medi_connect_logo.png" alt="Medi-Connect">
                            </div>
                            <h3><span class="logo-main">Medi</span><span class="logo-accent">Connect</span></h3>
                        </div>
                        <p>Votre santé, notre priorité. Accédez à des consultations médicales de qualité, où que vous soyez.</p>
                        <div class="social-links">
                            <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                            <a href="#" aria-label="Twitter"><i class="bi bi-twitter"></i></a>
                            <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                            <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                        </div>
                    </div>
                    
                    <div class="footer-links">
                        <h4>Navigation</h4>
                        <ul>
                            <li><a href="#services">Services</a></li>
                            <li><a href="#apropos">À Propos</a></li>
                            <li><a href="#contact">Contact</a></li>
                            <li><a href="#">FAQ</a></li>
                        </ul>
                    </div>
                    
                    <div class="footer-links">
                        <h4>Services</h4>
                        <ul>
                            <li><a href="services.php">Consultation en ligne</a></li>
                            <li><a href="services.php">Prise de rendez-vous</a></li>
                            <li><a href="services.php">Orientation médicale</a></li>
                            <li><a href="#">Urgences</a></li>
                        </ul>
                    </div>
                    
                    <div class="footer-links">
                        <h4>Légal</h4>
                        <ul>
                            <li><a href="#">Conditions d'utilisation</a></li>
                            <li><a href="#">Politique de confidentialité</a></li>
                            <li><a href="#">Mentions légales</a></li>
                            <li><a href="#">CGU</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="footer-bottom">
                    <p>&copy; 2026 Medi-Connect. Tous droits réservés.</p>
                    <p>Développé par <strong>BEKONO</strong> | Encadré par <strong>BEDING JUNIAS</strong></p>
                </div>
            </div>
        </footer>
    </div>
    
    <script src="JS/index.js"></script>
</body>
</html>