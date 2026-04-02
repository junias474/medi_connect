<!DOCTYPE html>
<html lang="en">
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
                <input type="search" id="search_input" placeholder="Search...">
            </div>
            
            <nav class="main-nav">
                <a href="index.php" class="nav-link">Home</a>
                <a href="services.php" class="nav-link">Services</a>
                <a href="about.php" class="nav-link">About</a>
                <a href="contact.php" class="nav-link active">Contact</a>
            </nav>
            
            <div class="user-actions">
                <button class="btn btn-secondary" id="login_button">
                    <i class="bi bi-person"></i> Login
                </button>
                <button class="btn btn-primary" id="signup_button">
                    Get Started <i class="bi bi-arrow-right"></i>
                </button>
            </div>
            
            <button class="mobile-menu-toggle" id="mobile-menu-toggle">
                <span></span><span></span><span></span>
            </button>
        </div>
        
        <nav class="mobile-nav" id="mobile-nav">
            <a href="index.php" class="mobile-nav-link">Home</a>
            <a href="services.php" class="mobile-nav-link">Services</a>
            <a href="about.php" class="mobile-nav-link">About</a>
            <a href="contact.php" class="mobile-nav-link active">Contact</a>
            <div class="mobile-nav-actions">
                <button class="btn btn-secondary btn-block">
                    <i class="bi bi-person"></i> Login
                </button>
                <button class="btn btn-primary btn-block">
                    Get Started <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </nav>
    </header>

    <section class="page-hero">
        <div class="hero-content">
            <nav class="breadcrumb">
                <a href="index.php">Home</a>
                <i class="bi bi-chevron-right"></i>
                <span>Contact</span>
            </nav>
            <h1 class="page-title">Contact Us</h1>
            <p class="page-description">
                Our team is here to answer all your questions
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
                    <h2>Need help?</h2>
                    <p class="intro-text">
                        Don't hesitate to reach out. Our team is available to answer 
                        all your questions and support you throughout your healthcare journey.
                    </p>
                    
                    <div class="contact-methods">
                        <div class="contact-card">
                            <div class="contact-icon"><i class="bi bi-envelope-fill"></i></div>
                            <div class="contact-details">
                                <h3>Email</h3>
                                <p>contact@medi-connect.com</p>
                                <p>support@medi-connect.com</p>
                                <span class="response-time">Reply within 24h</span>
                            </div>
                        </div>
                        
                        <div class="contact-card">
                            <div class="contact-icon"><i class="bi bi-telephone-fill"></i></div>
                            <div class="contact-details">
                                <h3>Phone</h3>
                                <p>+237 6XX XXX XXX</p>
                                <p>+237 6YY YYY YYY</p>
                                <span class="response-time">Mon–Fri 8am–6pm</span>
                            </div>
                        </div>
                        
                        <div class="contact-card">
                            <div class="contact-icon"><i class="bi bi-geo-alt-fill"></i></div>
                            <div class="contact-details">
                                <h3>Address</h3>
                                <p>123 Health Avenue</p>
                                <p>Yaoundé, Cameroon</p>
                                <span class="response-time">Offices open</span>
                            </div>
                        </div>
                        
                        <div class="contact-card">
                            <div class="contact-icon"><i class="bi bi-clock-fill"></i></div>
                            <div class="contact-details">
                                <h3>Hours</h3>
                                <p>Customer service: 24/7</p>
                                <p>Offices: Mon–Fri 8am–6pm</p>
                                <span class="response-time">Always available</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="social-contact">
                        <h3>Follow us</h3>
                        <div class="social-links">
                            <a href="#" class="social-link" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                            <a href="#" class="social-link" aria-label="Twitter"><i class="bi bi-twitter"></i></a>
                            <a href="#" class="social-link" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                            <a href="#" class="social-link" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                            <a href="#" class="social-link" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form-section">
                    <div class="form-header">
                        <h2>Send us a message</h2>
                        <p>Fill in the form below and we'll get back to you quickly</p>
                    </div>
                    
                    <form class="contact-form" id="contact-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first-name">First Name <span class="required">*</span></label>
                                <input type="text" id="first-name" name="first-name" required>
                            </div>
                            <div class="form-group">
                                <label for="last-name">Last Name <span class="required">*</span></label>
                                <input type="text" id="last-name" name="last-name" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone">
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject <span class="required">*</span></label>
                            <select id="subject" name="subject" required>
                                <option value="">Select a subject</option>
                                <option value="info">Information request</option>
                                <option value="rdv">Appointment booking</option>
                                <option value="technique">Technical issue</option>
                                <option value="feedback">Feedback & suggestions</option>
                                <option value="partenariat">Partnership</option>
                                <option value="autre">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message <span class="required">*</span></label>
                            <textarea id="message" name="message" rows="6" required></textarea>
                        </div>
                        
                        <div class="form-checkbox">
                            <input type="checkbox" id="consent" name="consent" required>
                            <label for="consent">
                                I consent to my data being used to respond to my request
                            </label>
                        </div>
                        
                        <button type="submit" class="btn-submit">
                            <span class="btn-text">Send message</span>
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
                <h2>Frequently Asked Questions</h2>
                <p>Find quick answers to your questions</p>
            </div>
            
            <div class="faq-grid">
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>How do I book an appointment?</h3>
                        <i class="bi bi-plus"></i>
                    </div>
                    <div class="faq-answer">
                        <p>
                            To book an appointment, first create your Medi-Connect account. 
                            Then, go to your dashboard, select the desired medical specialty, 
                            choose an available doctor and reserve your time slot.
                        </p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Are online consultations secure?</h3>
                        <i class="bi bi-plus"></i>
                    </div>
                    <div class="faq-answer">
                        <p>
                            Absolutely. We use end-to-end encryption for all our video consultations 
                            and your medical data is protected according to the strictest standards. 
                            Your privacy is our priority.
                        </p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Can I get a prescription online?</h3>
                        <i class="bi bi-plus"></i>
                    </div>
                    <div class="faq-answer">
                        <p>
                            Yes, if the doctor determines that a prescription is necessary after your consultation, 
                            you will receive a digital prescription by email that you can present at a pharmacy.
                        </p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>What payment methods are accepted?</h3>
                        <i class="bi bi-plus"></i>
                    </div>
                    <div class="faq-answer">
                        <p>
                            We accept bank cards (Visa, Mastercard), Mobile Money (MTN, Orange Money) 
                            and bank transfers. Payment is secure and encrypted.
                        </p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>How do I cancel or reschedule an appointment?</h3>
                        <i class="bi bi-plus"></i>
                    </div>
                    <div class="faq-answer">
                        <p>
                            Log in to your account, go to "My Appointments" in your dashboard. 
                            You can cancel or reschedule an appointment up to 2 hours before the scheduled time.
                        </p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Are the doctors certified?</h3>
                        <i class="bi bi-plus"></i>
                    </div>
                    <div class="faq-answer">
                        <p>
                            All our doctors are certified and registered with the Medical Council. 
                            We rigorously verify their qualifications and experience before integrating them 
                            into our platform.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to get started?</h2>
                <p>Create your account and access quality medical consultations</p>
                <button class="btn-cta">
                    Create my account for free
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
                    <p>Your health, our priority. Access quality medical consultations wherever you are.</p>
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
                        <li><a href="index.php">Home</a></li>
                        <li><a href="services.php">Services</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h4>Services</h4>
                    <ul>
                        <li><a href="services.php">Online Consultation</a></li>
                        <li><a href="services.php">Appointment Booking</a></li>
                        <li><a href="services.php">Medical Orientation</a></li>
                        <li><a href="#">Emergencies</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="#">Terms of Use</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Legal Notice</a></li>
                        <li><a href="#">GTC</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2026 Medi-Connect. All rights reserved.</p>
                <p>Developed by <strong>BEKONO</strong> | Supervised by <strong>BEDING JUNIAS</strong></p>
            </div>
        </div>
    </footer>

    <script src="JS/contact.js"></script>
</body>
</html>