<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../design/services.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Nos Services - Medi-Connect</title>
</head>
<body>
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
                <input type="search" id="search_input" placeholder="Rechercher un service...">
            </div>
            
            <!-- Navigation -->
            <nav class="main-nav">
                <a href="index.php" class="nav-link">Accueil</a>
                <a href="services.php" class="nav-link active">Services</a>
                <a href="about.php" class="nav-link">À Propos</a>
                <a href="contact.php" class="nav-link">Contact</a>
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
            <a href="index.php" class="mobile-nav-link">Accueil</a>
            <a href="services.php" class="mobile-nav-link active">Services</a>
            <a href="about.php" class="mobile-nav-link">À Propos</a>
            <a href="contact.php" class="mobile-nav-link">Contact</a>
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

    <!-- PAGE HERO -->
    <section class="page-hero">
        <div class="hero-content">
            <nav class="breadcrumb">
                <a href="index.php">Accueil</a>
                <i class="bi bi-chevron-right"></i>
                <span>Services</span>
            </nav>
            <h1 class="page-title">Nos Services</h1>
            <p class="page-description">
                Des solutions complètes pour votre santé et votre bien-être
            </p>
        </div>
        <div class="hero-decoration">
            <div class="deco-circle circle-1"></div>
            <div class="deco-circle circle-2"></div>
        </div>
    </section>

    <!-- SERVICES DÉTAILLÉS -->
    <section class="services-detailed">
        <div class="container">
            <!-- Service 1: Consultation en ligne -->
            <div class="service-detail">
                <div class="service-detail-visual">
                    <div class="visual-card">
                        <i class="bi bi-camera-video-fill"></i>
                    </div>
                    <div class="visual-stats">
                        <div class="stat-badge">
                            <i class="bi bi-clock-history"></i>
                            <span>Disponible 24/7</span>
                        </div>
                        <div class="stat-badge">
                            <i class="bi bi-shield-check"></i>
                            <span>100% Sécurisé</span>
                        </div>
                    </div>
                </div>
                
                <div class="service-detail-content">
                    <div class="service-tag">Service Principal</div>
                    <h2>Consultation Médicale en Ligne</h2>
                    <p class="service-intro">
                        Consultez un médecin qualifié par vidéoconférence depuis le confort de votre domicile. 
                        Notre plateforme de téléconsultation vous permet d'accéder rapidement à des soins médicaux 
                        de qualité, sans déplacement.
                    </p>
                    
                    <div class="service-features-grid">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bi bi-camera-video"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Vidéoconférence HD</h4>
                                <p>Qualité vidéo et audio optimale pour une consultation efficace</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bi bi-file-earmark-medical"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Ordonnance Numérique</h4>
                                <p>Recevez votre ordonnance directement par email après la consultation</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bi bi-archive"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Historique Médical</h4>
                                <p>Accédez à tout moment à votre dossier médical complet et sécurisé</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bi bi-chat-dots"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Suivi Post-Consultation</h4>
                                <p>Posez vos questions par messagerie après votre rendez-vous</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="service-process">
                        <h3>Comment ça fonctionne ?</h3>
                        <div class="process-steps">
                            <div class="process-step">
                                <span class="step-num">1</span>
                                <p>Sélectionnez un médecin disponible</p>
                            </div>
                            <div class="process-step">
                                <span class="step-num">2</span>
                                <p>Décrivez vos symptômes</p>
                            </div>
                            <div class="process-step">
                                <span class="step-num">3</span>
                                <p>Rejoignez la consultation vidéo</p>
                            </div>
                            <div class="process-step">
                                <span class="step-num">4</span>
                                <p>Recevez votre ordonnance</p>
                            </div>
                        </div>
                    </div>
                    
                    <button class="btn-action">
                        Démarrer une consultation
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Service 2: Prise de rendez-vous -->
            <div class="service-detail reverse">
                <div class="service-detail-content">
                    <div class="service-tag popular">Le Plus Populaire</div>
                    <h2>Prise de Rendez-vous</h2>
                    <p class="service-intro">
                        Planifiez vos consultations médicales en quelques clics. Notre système intelligent 
                        vous propose les créneaux disponibles selon vos préférences et celles des médecins.
                    </p>
                    
                    <div class="service-features-grid">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Disponibilités en Temps Réel</h4>
                                <p>Visualisez instantanément les créneaux disponibles</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bi bi-bell"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Rappels Automatiques</h4>
                                <p>Notifications par SMS et email avant votre rendez-vous</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bi bi-arrow-repeat"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Reprogrammation Facile</h4>
                                <p>Modifiez ou annulez votre rendez-vous en un clic</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bi bi-person-badge"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Choix du Médecin</h4>
                                <p>Sélectionnez votre médecin selon la spécialité souhaitée</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="service-benefits">
                        <h3>Avantages</h3>
                        <ul class="benefits-list">
                            <li><i class="bi bi-check-circle-fill"></i> Gain de temps considérable</li>
                            <li><i class="bi bi-check-circle-fill"></i> Flexibilité totale dans la gestion de vos rendez-vous</li>
                            <li><i class="bi bi-check-circle-fill"></i> Accès aux profils détaillés des médecins</li>
                            <li><i class="bi bi-check-circle-fill"></i> Confirmation instantanée par email</li>
                            <li><i class="bi bi-check-circle-fill"></i> Historique de tous vos rendez-vous</li>
                        </ul>
                    </div>
                    
                    <button class="btn-action">
                        Prendre rendez-vous maintenant
                        <i class="bi bi-calendar-plus"></i>
                    </button>
                </div>
                
                <div class="service-detail-visual">
                    <div class="visual-card">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <div class="visual-info-card">
                        <h4>Rendez-vous rapide</h4>
                        <p>Prochain créneau disponible :</p>
                        <div class="next-slot">
                            <i class="bi bi-clock"></i>
                            <span>Aujourd'hui 14:30</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Service 3: Orientation médicale -->
            <div class="service-detail">
                <div class="service-detail-visual">
                    <div class="visual-card">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <div class="visual-map-preview">
                        <i class="bi bi-map"></i>
                        <p>Centres de santé à proximité</p>
                    </div>
                </div>
                
                <div class="service-detail-content">
                    <div class="service-tag">Service Innovant</div>
                    <h2>Orientation Médicale & Géolocalisation</h2>
                    <p class="service-intro">
                        Trouvez rapidement le centre de santé le plus proche de vous grâce à notre système 
                        de géolocalisation intelligent. Accédez aux informations détaillées et aux itinéraires optimisés.
                    </p>
                    
                    <div class="service-features-grid">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bi bi-map"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Carte Interactive</h4>
                                <p>Visualisez tous les centres de santé autour de vous</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bi bi-signpost"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Itinéraires Optimisés</h4>
                                <p>Obtenez le meilleur trajet vers le centre choisi</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bi bi-info-circle"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Informations Détaillées</h4>
                                <p>Horaires, services disponibles et coordonnées</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bi bi-star"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Avis et Notes</h4>
                                <p>Consultez les évaluations des autres patients</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="service-info-box">
                        <i class="bi bi-lightbulb"></i>
                        <div>
                            <h4>Le saviez-vous ?</h4>
                            <p>
                                Notre système peut également vous orienter vers les urgences, pharmacies de garde 
                                et centres de vaccination les plus proches en fonction de vos besoins spécifiques.
                            </p>
                        </div>
                    </div>
                    
                    <button class="btn-action">
                        Trouver un centre proche
                        <i class="bi bi-geo-alt"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- SERVICES SUPPLÉMENTAIRES -->
    <section class="additional-services">
        <div class="container">
            <div class="section-header">
                <h2>Services Complémentaires</h2>
                <p>D'autres fonctionnalités pour améliorer votre expérience santé</p>
            </div>
            
            <div class="additional-grid">
                <div class="additional-card">
                    <div class="additional-icon">
                        <i class="bi bi-chat-left-text"></i>
                    </div>
                    <h3>Messagerie Sécurisée</h3>
                    <p>Communiquez avec votre médecin entre les consultations via notre messagerie cryptée.</p>
                </div>
                
                <div class="additional-card">
                    <div class="additional-icon">
                        <i class="bi bi-clipboard2-pulse"></i>
                    </div>
                    <h3>Suivi de Santé</h3>
                    <p>Suivez vos constantes, traitements et rendez-vous dans un tableau de bord personnalisé.</p>
                </div>
                
                <div class="additional-card">
                    <div class="additional-icon">
                        <i class="bi bi-capsule"></i>
                    </div>
                    <h3>Rappel de Médicaments</h3>
                    <p>Ne manquez plus jamais une prise grâce à nos notifications intelligentes.</p>
                </div>
                
                <div class="additional-card">
                    <div class="additional-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <h3>Compte Famille</h3>
                    <p>Gérez la santé de toute votre famille depuis un seul compte.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA SECTION -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Prêt à bénéficier de nos services ?</h2>
                <p>Inscrivez-vous gratuitement et commencez dès aujourd'hui</p>
                <div class="cta-buttons">
                    <button class="btn-cta primary">
                        Créer mon compte
                        <i class="bi bi-arrow-right"></i>
                    </button>
                    <a href="contact.php" class="btn-cta secondary">
                        Nous contacter
                        <i class="bi bi-envelope"></i>
                    </a>
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
                        <li><a href="index.php">Accueil</a></li>
                        <li><a href="services.php">Services</a></li>
                        <li><a href="about.php">À Propos</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h4>Services</h4>
                    <ul>
                        <li><a href="services.php#consultation">Consultation en ligne</a></li>
                        <li><a href="services.php#rdv">Prise de rendez-vous</a></li>
                        <li><a href="services.php#orientation">Orientation médicale</a></li>
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

    <script src="../JS/services.js"></script>
</body>
</html>