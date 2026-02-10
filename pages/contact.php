<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../design/contact.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Contact - Medi-Connect</title>
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <div class="logo-section">
                <div class="logo-icon">
                    <img src="design/assets/medi_connect_logo.png" alt="Medi-Connect Logo">
                </div>
                <h1 class="logo-name">
                    <span class="logo-main">Medi</span><span class="logo-accent">Connect</span>
                </h1>
            </div>
            
            <div class="search-area">
                <i class="bi bi-search search-icon"></i>
                <input type="search" id="search_input" placeholder="Rechercher...">
            </div>
            
            <nav class="main-nav">
                <a href="index.php" class="nav-link">Accueil</a>
                <a href="services.php" class="nav-link">Services</a>
                <a href="about.php" class="nav-link">À Propos</a>
                <a href="contact.php" class="nav-link active">Contact</a>
            </nav>
            
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
            
            <button class="mobile-menu-toggle" id="mobile-menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
        
        <nav class="mobile-nav" id="mobile-nav">
            <a href="index.php" class="mobile-nav-link">Accueil</a>
            <a href="services.php" class="mobile-nav-link">Services</a>
            <a href="about.php" class="mobile-nav-link">À Propos</a>
            <a href="contact.php" class="mobile-nav-link active">Contact</a>
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

    <section class="page-hero">
        <div class="hero-content">
            <nav class="breadcrumb">
                <a href="index.php">Accueil</a>
                <i class="bi bi-chevron-right"></i>
                <span>Contact</span>
            </nav>
            <h1 class="page-title">Contactez-nous</h1>
            <p class="page-description">
                Notre équipe est à votre écoute pour répondre à toutes vos questions
            </p>
        </div>
        <div class="hero-decoration">
            <div class="deco-circle circle-1"></div>
            <div class="deco-circle circle-2"></div>
        </div>
    </section>

    <section class="contact-main">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-info-section">
                    <h2>Besoin d'aide ?</h2>
                    <p class="intro-text">
                        N'hésitez pas à nous contacter. Notre équipe est disponible pour répondre 
                        à toutes vos questions et vous accompagner dans votre parcours de santé.
                    </p>
                    
                    <div class="contact-methods">
                        <div class="contact-card">
                            <div class="contact-icon">
                                <i class="bi bi-envelope-fill"></i>
                            </div>
                            <div class="contact-details">
                                <h3>Email</h3>
                                <p>contact@medi-connect.com</p>
                                <p>support@medi-connect.com</p>
                                <span class="response-time">Réponse sous 24h</span>
                            </div>
                        </div>
                        
                        <div class="contact-card">
                            <div class="contact-icon">
                                <i class="bi bi-telephone-fill"></i>
                            </div>
                            <div class="contact-details">
                                <h3>Téléphone</h3>
                                <p>+237 6XX XXX XXX</p>
                                <p>+237 6YY YYY YYY</p>
                                <span class="response-time">Lun-Ven 8h-18h</span>
                            </div>
                        </div>
                        
                        <div class="contact-card">
                            <div class="contact-icon">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <div class="contact-details">
                                <h3>Adresse</h3>
                                <p>123 Avenue de la Santé</p>
                                <p>Yaoundé, Cameroun</p>
                                <span class="response-time">Bureaux ouverts</span>
                            </div>
                        </div>
                        
                        <div class="contact-card">
                            <div class="contact-icon">
                                <i class="bi bi-clock-fill"></i>
                            </div>
                            <div class="contact-details">
                                <h3>Horaires</h3>
                                <p>Service client : 24/7</p>
                                <p>Bureaux : Lun-Ven 8h-18h</p>
                                <span class="response-time">Toujours disponible</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="social-contact">
                        <h3>Suivez-nous</h3>
                        <div class="social-links">
                            <a href="#" class="social-link" aria-label="Facebook">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="#" class="social-link" aria-label="Twitter">
                                <i class="bi bi-twitter"></i>
                            </a>
                            <a href="#" class="social-link" aria-label="LinkedIn">
                                <i class="bi bi-linkedin"></i>
                            </a>
                            <a href="#" class="social-link" aria-label="Instagram">
                                <i class="bi bi-instagram"></i>
                            </a>
                            <a href="#" class="social-link" aria-label="WhatsApp">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form-section">
                    <div class="form-header">
                        <h2>Envoyez-nous un message</h2>
                        <p>Remplissez le formulaire ci-dessous et nous vous répondrons rapidement</p>
                    </div>
                    
                    <form class="contact-form" id="contact-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first-name">Prénom <span class="required">*</span></label>
                                <input type="text" id="first-name" name="first-name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="last-name">Nom <span class="required">*</span></label>
                                <input type="text" id="last-name" name="last-name" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Téléphone</label>
                            <input type="tel" id="phone" name="phone">
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Sujet <span class="required">*</span></label>
                            <select id="subject" name="subject" required>
                                <option value="">Sélectionnez un sujet</option>
                                <option value="info">Demande d'information</option>
                                <option value="rdv">Prise de rendez-vous</option>
                                <option value="technique">Problème technique</option>
                                <option value="feedback">Avis et suggestions</option>
                                <option value="partenariat">Partenariat</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message <span class="required">*</span></label>
                            <textarea id="message" name="message" rows="6" required></textarea>
                        </div>
                        
                        <div class="form-checkbox">
                            <input type="checkbox" id="consent" name="consent" required>
                            <label for="consent">
                                J'accepte que mes données soient utilisées pour répondre à ma demande
                            </label>
                        </div>
                        
                        <button type="submit" class="btn-submit">
                            <span class="btn-text">Envoyer le message</span>
                            <i class="bi bi-send"></i>
                        </button>
                        
                        <div id="form-message" class="form-message"></div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="faq-section">
        <div class="container">
            <div class="section-header">
                <span class="section-label">FAQ</span>
                <h2>Questions Fréquentes</h2>
                <p>Trouvez rapidement des réponses à vos questions</p>
            </div>
            
            <div class="faq-grid">
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Comment prendre un rendez-vous ?</h3>
                        <i class="bi bi-plus"></i>
                    </div>
                    <div class="faq-answer">
                        <p>
                            Pour prendre rendez-vous, créez d'abord votre compte sur Medi-Connect. 
                            Ensuite, accédez à votre tableau de bord, sélectionnez la spécialité médicale souhaitée, 
                            choisissez un médecin disponible et réservez votre créneau horaire.
                        </p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Les consultations en ligne sont-elles sécurisées ?</h3>
                        <i class="bi bi-plus"></i>
                    </div>
                    <div class="faq-answer">
                        <p>
                            Absolument. Nous utilisons un cryptage de bout en bout pour toutes nos consultations vidéo 
                            et vos données médicales sont protégées selon les normes les plus strictes. 
                            Votre confidentialité est notre priorité.
                        </p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Puis-je obtenir une ordonnance en ligne ?</h3>
                        <i class="bi bi-plus"></i>
                    </div>
                    <div class="faq-answer">
                        <p>
                            Oui, si le médecin juge qu'une prescription est nécessaire suite à votre consultation, 
                            vous recevrez une ordonnance numérique par email que vous pourrez présenter en pharmacie.
                        </p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Quels sont les moyens de paiement acceptés ?</h3>
                        <i class="bi bi-plus"></i>
                    </div>
                    <div class="faq-answer">
                        <p>
                            Nous acceptons les cartes bancaires (Visa, Mastercard), Mobile Money (MTN, Orange Money) 
                            et les virements bancaires. Le paiement est sécurisé et crypté.
                        </p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Comment annuler ou modifier un rendez-vous ?</h3>
                        <i class="bi bi-plus"></i>
                    </div>
                    <div class="faq-answer">
                        <p>
                            Connectez-vous à votre compte, accédez à "Mes rendez-vous" dans votre tableau de bord. 
                            Vous pouvez annuler ou modifier un rendez-vous jusqu'à 2 heures avant l'heure prévue.
                        </p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Les médecins sont-ils certifiés ?</h3>
                        <i class="bi bi-plus"></i>
                    </div>
                    <div class="faq-answer">
                        <p>
                            Tous nos médecins sont certifiés et enregistrés auprès de l'Ordre des Médecins. 
                            Nous vérifions rigoureusement leurs qualifications et leur expérience avant de les intégrer 
                            à notre plateforme.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Prêt à commencer ?</h2>
                <p>Créez votre compte et accédez à des consultations médicales de qualité</p>
                <button class="btn-cta">
                    Créer mon compte gratuitement
                    <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>
    </section>

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

    <script src="JS/contact.js"></script>
</body>
</html>