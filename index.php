<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="design/index.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Medi-Connect</title>
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
    </div>
    
<script src="JS/index.js"></script>
</body>
</html>