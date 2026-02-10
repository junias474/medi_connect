<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="design/about.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>À Propos - Medi-Connect</title>
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
                <input type="search" id="search_input" placeholder="Rechercher...">
            </div>
            
            <!-- Navigation -->
            <nav class="main-nav">
                <a href="index.php" class="nav-link">Accueil</a>
                <a href="services.php" class="nav-link">Services</a>
                <a href="about.php" class="nav-link active">À Propos</a>
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
            <a href="services.php" class="mobile-nav-link">Services</a>
            <a href="about.php" class="mobile-nav-link active">À Propos</a>
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
                <span>À Propos</span>
            </nav>
            <h1 class="page-title">Notre Mission</h1>
            <p class="page-description">
                Rendre les soins de santé accessibles à tous, où qu'ils soient
            </p>
        </div>
        <div class="hero-decoration">
            <div class="deco-circle circle-1"></div>
            <div class="deco-circle circle-2"></div>
        </div>
    </section>

    <!-- NOTRE HISTOIRE -->
    <section class="our-story">
        <div class="container">
            <div class="story-grid">
                <div class="story-content">
                    <span class="section-label">Notre Histoire</span>
                    <h2>Comment Medi-Connect est né</h2>
                    <p class="story-text">
                        Medi-Connect est né en <strong>2020</strong> d'une vision simple mais puissante : 
                        permettre à chacun d'accéder facilement à des soins de santé de qualité, 
                        indépendamment de sa localisation géographique.
                    </p>
                    <p class="story-text">
                        Face au constat que de nombreuses personnes rencontrent des difficultés pour 
                        consulter rapidement un médecin, que ce soit en raison de l'éloignement des centres 
                        de santé, des files d'attente interminables ou du manque de disponibilité des 
                        professionnels de santé, nous avons décidé de créer une solution innovante.
                    </p>
                    <p class="story-text">
                        Aujourd'hui, Medi-Connect connecte des <strong>milliers de patients</strong> avec 
                        plus de <strong>150 médecins qualifiés</strong>, offrant des consultations médicales 
                        en ligne de qualité, accessibles 24 heures sur 24, 7 jours sur 7.
                    </p>
                </div>
                
                <div class="story-visual">
                    <div class="story-image-card">
                        <div class="year-badge">2020</div>
                        <i class="bi bi-calendar-event"></i>
                        <h3>Année de création</h3>
                        <p>Le début de notre aventure</p>
                    </div>
                    <div class="story-stats">
                        <div class="story-stat">
                            <h4>5000+</h4>
                            <p>Patients</p>
                        </div>
                        <div class="story-stat">
                            <h4>150+</h4>
                            <p>Médecins</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- NOTRE MISSION -->
    <section class="our-mission">
        <div class="container">
            <div class="section-header">
                <span class="section-label">Notre Mission</span>
                <h2>Ce qui nous anime au quotidien</h2>
            </div>
            
            <div class="mission-content">
                <div class="mission-main">
                    <i class="bi bi-bullseye"></i>
                    <h3>Démocratiser l'accès aux soins de santé</h3>
                    <p>
                        Notre mission est de rendre les consultations médicales accessibles à tous, 
                        en éliminant les barrières géographiques et temporelles. Nous croyons fermement 
                        que chaque personne, où qu'elle se trouve, mérite un accès rapide et facile à 
                        des professionnels de santé qualifiés.
                    </p>
                </div>
                
                <div class="mission-pillars">
                    <div class="pillar-card">
                        <div class="pillar-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h4>Accessibilité</h4>
                        <p>Des soins de santé pour tous, sans discrimination ni obstacle géographique</p>
                    </div>
                    
                    <div class="pillar-card">
                        <div class="pillar-icon">
                            <i class="bi bi-award-fill"></i>
                        </div>
                        <h4>Qualité</h4>
                        <p>Des médecins certifiés et expérimentés pour garantir des consultations de haut niveau</p>
                    </div>
                    
                    <div class="pillar-card">
                        <div class="pillar-icon">
                            <i class="bi bi-lightning-fill"></i>
                        </div>
                        <h4>Innovation</h4>
                        <p>Utilisation des technologies modernes pour améliorer l'expérience de santé</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- NOS VALEURS -->
    <section class="our-values">
        <div class="container">
            <div class="section-header">
                <span class="section-label">Nos Valeurs</span>
                <h2>Les principes qui nous guident</h2>
            </div>
            
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-number">01</div>
                    <div class="value-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h3>Sécurité & Confidentialité</h3>
                    <p>
                        La protection de vos données personnelles et médicales est notre priorité absolue. 
                        Nous utilisons les protocoles de sécurité les plus avancés pour garantir la 
                        confidentialité de vos informations.
                    </p>
                </div>
                
                <div class="value-card">
                    <div class="value-number">02</div>
                    <div class="value-icon">
                        <i class="bi bi-heart-pulse"></i>
                    </div>
                    <h3>Bienveillance</h3>
                    <p>
                        Nous traitons chaque patient avec empathie, respect et attention. Votre bien-être 
                        est au cœur de nos préoccupations, et nous nous engageons à vous offrir une 
                        expérience humaine et chaleureuse.
                    </p>
                </div>
                
                <div class="value-card">
                    <div class="value-number">03</div>
                    <div class="value-icon">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <h3>Excellence</h3>
                    <p>
                        Nous ne cessons d'améliorer nos services pour vous offrir la meilleure expérience 
                        possible. Formation continue de nos médecins, mise à jour technologique et écoute 
                        de vos retours sont nos maîtres-mots.
                    </p>
                </div>
                
                <div class="value-card">
                    <div class="value-number">04</div>
                    <div class="value-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <h3>Inclusion</h3>
                    <p>
                        Nous croyons en un accès équitable aux soins pour tous, sans distinction d'âge, 
                        de genre, d'origine ou de statut socio-économique. La santé est un droit universel.
                    </p>
                </div>
                
                <div class="value-card">
                    <div class="value-number">05</div>
                    <div class="value-icon">
                        <i class="bi bi-lightbulb"></i>
                    </div>
                    <h3>Innovation Continue</h3>
                    <p>
                        Nous investissons constamment dans les nouvelles technologies pour améliorer 
                        l'accessibilité, la qualité et l'efficacité de nos services de télémédecine.
                    </p>
                </div>
                
                <div class="value-card">
                    <div class="value-number">06</div>
                    <div class="value-icon">
                        <i class="bi bi-hand-thumbs-up"></i>
                    </div>
                    <h3>Transparence</h3>
                    <p>
                        Nous croyons en une communication claire et honnête avec nos patients et nos 
                        partenaires. Pas de frais cachés, pas de promesses exagérées, juste la vérité.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- NOTRE ÉQUIPE -->
    <section class="our-team">
        <div class="container">
            <div class="section-header">
                <span class="section-label">Notre Équipe</span>
                <h2>Les personnes derrière Medi-Connect</h2>
                <p>Une équipe passionnée et dévouée à votre santé</p>
            </div>
            
            <div class="team-intro">
                <p>
                    Medi-Connect c'est avant tout une équipe de passionnés : développeurs, médecins, 
                    designers et experts en santé numérique qui travaillent ensemble pour créer la 
                    meilleure expérience de télémédecine possible.
                </p>
            </div>
            
            <div class="team-stats-grid">
                <div class="team-stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <h3>150+</h3>
                    <p>Médecins Certifiés</p>
                </div>
                
                <div class="team-stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-laptop"></i>
                    </div>
                    <h3>20+</h3>
                    <p>Experts Tech</p>
                </div>
                
                <div class="team-stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-headset"></i>
                    </div>
                    <h3>15+</h3>
                    <p>Support Client</p>
                </div>
                
                <div class="team-stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-globe"></i>
                    </div>
                    <h3>5</h3>
                    <p>Pays Couverts</p>
                </div>
            </div>
        </div>
    </section>

    <!-- NOTRE IMPACT -->
    <section class="our-impact">
        <div class="container">
            <div class="impact-grid">
                <div class="impact-content">
                    <span class="section-label">Notre Impact</span>
                    <h2>Les chiffres qui parlent</h2>
                    <p class="impact-intro">
                        Depuis notre création, nous avons eu un impact significatif sur l'accès aux soins 
                        de santé dans notre région. Voici quelques chiffres qui témoignent de notre engagement.
                    </p>
                    
                    <div class="impact-highlights">
                        <div class="highlight-item">
                            <i class="bi bi-check-circle-fill"></i>
                            <div>
                                <h4>98% de satisfaction</h4>
                                <p>Nos patients sont satisfaits de nos services</p>
                            </div>
                        </div>
                        
                        <div class="highlight-item">
                            <i class="bi bi-check-circle-fill"></i>
                            <div>
                                <h4>Temps d'attente réduit</h4>
                                <p>Consultation en moins de 15 minutes en moyenne</p>
                            </div>
                        </div>
                        
                        <div class="highlight-item">
                            <i class="bi bi-check-circle-fill"></i>
                            <div>
                                <h4>Accessibilité 24/7</h4>
                                <p>Des médecins disponibles à toute heure</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="impact-numbers">
                    <div class="number-card large">
                        <div class="number-value" data-target="10000">0</div>
                        <p>Consultations réalisées</p>
                    </div>
                    
                    <div class="number-cards-row">
                        <div class="number-card">
                            <div class="number-value" data-target="5000">0</div>
                            <p>Patients actifs</p>
                        </div>
                        
                        <div class="number-card">
                            <div class="number-value" data-target="150">0</div>
                            <p>Médecins partenaires</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- NOTRE ENGAGEMENT -->
    <section class="our-commitment">
        <div class="container">
            <div class="commitment-card">
                <div class="commitment-icon">
                    <i class="bi bi-bookmark-heart-fill"></i>
                </div>
                <div class="commitment-content">
                    <h2>Notre engagement envers vous</h2>
                    <p>
                        Chez Medi-Connect, nous nous engageons à fournir des services de télémédecine 
                        de la plus haute qualité. Chaque médecin de notre plateforme est rigoureusement 
                        sélectionné et vérifié pour garantir votre sécurité et votre satisfaction.
                    </p>
                    <p>
                        Nous investissons continuellement dans l'amélioration de notre technologie, 
                        la formation de nos équipes et l'écoute de vos retours pour vous offrir une 
                        expérience exceptionnelle à chaque consultation.
                    </p>
                    <button class="btn-commitment">
                        Découvrir nos services
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA SECTION -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Rejoignez des milliers de patients satisfaits</h2>
                <p>Commencez votre parcours de santé avec Medi-Connect dès aujourd'hui</p>
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

    <script src="JS/about.js"></script>
</body>
</html>