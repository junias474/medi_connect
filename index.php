<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Medi-Connect - Online medical consultation platform. Easily book appointments with qualified doctors.">
    <link rel="stylesheet" href="design/index.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Medi-Connect - Your health, our priority</title>
</head>
<body>
    <!-- Splash screen -->
    <div class="splash-screen" id="splash-screen">
        <div class="splash-content">
            <div class="splash-logo">
                <img src="design/assets/medi_connect_logo.png" alt="Medi-Connect Logo">
            </div>
            <h1 class="splash-title">
                <span class="splash-main">Medi</span><span class="splash-accent">Connect</span>
            </h1>
            <p class="splash-tagline">Your health, our priority</p>
            <div class="splash-loader">
                <div class="loader-bar"></div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="main-content" id="main-content">
        <!-- HEADER -->
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
                    <input type="search" id="search_input" placeholder="Search for a doctor, specialty...">
                </div>
                
                <nav class="main-nav">
                    <a href="#services" class="nav-link">Services</a>
                    <a href="#about" class="nav-link">About</a>
                    <a href="#contact" class="nav-link">Contact</a>
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
                <a href="#services" class="mobile-nav-link">Services</a>
                <a href="#about" class="mobile-nav-link">About</a>
                <a href="#contact" class="mobile-nav-link">Contact</a>
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

        <!-- HERO SECTION -->
        <section class="hero-section">
            <div class="hero-container">
                <div class="hero-content">
                    <div class="hero-badge">
                        <i class="bi bi-shield-check"></i>
                        <span>Secure & Certified Platform</span>
                    </div>
                    
                    <h2 class="hero-title">
                        Consult a doctor 
                        <span class="hero-highlight">wherever you are</span>
                    </h2>
                    
                    <p class="hero-description">
                        Access quality online medical consultations, book appointments easily 
                        and find the nearest healthcare center to you.
                    </p>
                    
                    <div class="hero-actions">
                        <button class="btn-hero btn-hero-primary">
                            <i class="bi bi-calendar-check"></i>
                            Book an appointment
                        </button>
                        <button class="btn-hero btn-hero-secondary">
                            <i class="bi bi-play-circle"></i>
                            Watch the demo
                        </button>
                    </div>
                    
                    <div class="hero-stats">
                        <div class="stat-item">
                            <div class="stat-icon"><i class="bi bi-people"></i></div>
                            <div class="stat-info">
                                <h4>5000+</h4>
                                <p>Satisfied patients</p>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon"><i class="bi bi-person-badge"></i></div>
                            <div class="stat-info">
                                <h4>150+</h4>
                                <p>Qualified doctors</p>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
                            <div class="stat-info">
                                <h4>24/7</h4>
                                <p>Availability</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="hero-visual">
                    <div class="hero-image-wrapper">
                        <div class="floating-card card-1">
                            <i class="bi bi-heart-pulse"></i>
                            <div>
                                <h5>Quick consultation</h5>
                                <p>In less than 15 min</p>
                            </div>
                        </div>
                        <div class="floating-card card-2">
                            <i class="bi bi-shield-check"></i>
                            <div>
                                <h5>100% Secure</h5>
                                <p>Protected data</p>
                            </div>
                        </div>
                        <div class="floating-card card-3">
                            <i class="bi bi-star-fill"></i>
                            <div>
                                <h5>4.9/5</h5>
                                <p>Average rating</p>
                            </div>
                        </div>
                        <div class="hero-main-image">
                            <i class="bi bi-hospital"></i>
                        </div>
                    </div>
                </div>
            </div>
            
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
                    <span class="section-label">Our Services</span>
                    <h2 class="section-title">What we offer</h2>
                    <p class="section-description">
                        Comprehensive solutions for your health and well-being
                    </p>
                </div>
                
                <div class="services-grid">
                    <div class="service-card">
                        <div class="service-icon"><i class="bi bi-camera-video"></i></div>
                        <h3>Online Consultation</h3>
                        <p>Consult a doctor by video from the comfort of your home. Fast, convenient and secure.</p>
                        <ul class="service-features">
                            <li><i class="bi bi-check-circle"></i> HD video conference</li>
                            <li><i class="bi bi-check-circle"></i> Digital prescription</li>
                            <li><i class="bi bi-check-circle"></i> Medical history</li>
                        </ul>
                    </div>
                    
                    <div class="service-card featured">
                        <div class="featured-badge">Popular</div>
                        <div class="service-icon"><i class="bi bi-calendar-check"></i></div>
                        <h3>Appointment Booking</h3>
                        <p>Schedule your consultations based on your availability and the doctors'. Simple and fast.</p>
                        <ul class="service-features">
                            <li><i class="bi bi-check-circle"></i> Real-time availability</li>
                            <li><i class="bi bi-check-circle"></i> Automatic reminders</li>
                            <li><i class="bi bi-check-circle"></i> Easy rescheduling</li>
                        </ul>
                    </div>
                    
                    <div class="service-card">
                        <div class="service-icon"><i class="bi bi-geo-alt"></i></div>
                        <h3>Medical Orientation</h3>
                        <p>Find the nearest healthcare center to you with our geolocation system.</p>
                        <ul class="service-features">
                            <li><i class="bi bi-check-circle"></i> Interactive map</li>
                            <li><i class="bi bi-check-circle"></i> Optimized routes</li>
                            <li><i class="bi bi-check-circle"></i> Detailed information</li>
                        </ul>
                    </div>
                </div>
                
                <div class="section-cta">
                    <a href="pages/services.php" class="btn-link">
                        Discover all our services <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- HOW IT WORKS SECTION -->
        <section class="how-it-works">
            <div class="section-container">
                <div class="section-header">
                    <span class="section-label">How it works</span>
                    <h2 class="section-title">Consult a doctor in 3 steps</h2>
                </div>
                
                <div class="steps-container">
                    <div class="step-item">
                        <div class="step-number">01</div>
                        <div class="step-icon"><i class="bi bi-person-plus"></i></div>
                        <h3>Create your account</h3>
                        <p>Sign up for free in just a few seconds with your basic information.</p>
                    </div>
                    
                    <div class="step-connector"></div>
                    
                    <div class="step-item">
                        <div class="step-number">02</div>
                        <div class="step-icon"><i class="bi bi-clipboard2-pulse"></i></div>
                        <h3>Describe your symptoms</h3>
                        <p>Enter your symptoms and book an appointment with an available doctor.</p>
                    </div>
                    
                    <div class="step-connector"></div>
                    
                    <div class="step-item">
                        <div class="step-number">03</div>
                        <div class="step-icon"><i class="bi bi-camera-video"></i></div>
                        <h3>Consult online</h3>
                        <p>Join your video consultation and receive your prescription.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ABOUT PREVIEW SECTION -->
        <section class="about-preview" id="about">
            <div class="section-container">
                <div class="about-grid">
                    <div class="about-content">
                        <span class="section-label">About Us</span>
                        <h2 class="section-title">A platform built for your health</h2>
                        <p class="about-text">
                            Medi-Connect was born from the desire to make healthcare more accessible to everyone. 
                            We believe that everyone deserves easy and fast access to qualified health professionals, 
                            regardless of where they are.
                        </p>
                        <p class="about-text">
                            Our mission is to transform the medical consultation experience by combining modern 
                            technology with quality medical expertise, while guaranteeing the security and confidentiality 
                            of your data.
                        </p>
                        
                        <div class="about-values">
                            <div class="value-item">
                                <i class="bi bi-shield-check"></i>
                                <div>
                                    <h4>Security</h4>
                                    <p>Your data is protected</p>
                                </div>
                            </div>
                            <div class="value-item">
                                <i class="bi bi-award"></i>
                                <div>
                                    <h4>Quality</h4>
                                    <p>Certified and experienced doctors</p>
                                </div>
                            </div>
                            <div class="value-item">
                                <i class="bi bi-clock"></i>
                                <div>
                                    <h4>Availability</h4>
                                    <p>Service accessible 24/7</p>
                                </div>
                            </div>
                        </div>
                        
                        <a href="about.php" class="btn-link">
                            Learn more about our mission <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    
                    <div class="about-visual">
                        <div class="about-image-card">
                            <i class="bi bi-heart-pulse-fill"></i>
                            <h4>Our Vision</h4>
                            <p>Democratizing access to healthcare through technology</p>
                        </div>
                        <div class="stats-mini">
                            <div class="stat-mini-item">
                                <h3>98%</h3>
                                <p>Satisfaction rate</p>
                            </div>
                            <div class="stat-mini-item">
                                <h3>2020</h3>
                                <p>Year founded</p>
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
                    <span class="section-label">Testimonials</span>
                    <h2 class="section-title">What our patients say</h2>
                </div>
                
                <div class="testimonials-grid">
                    <div class="testimonial-card">
                        <div class="testimonial-rating">
                            <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="testimonial-text">
                            "Exceptional service! I was able to consult a doctor quickly without traveling. 
                            The interface is intuitive and the doctors are very professional."
                        </p>
                        <div class="testimonial-author">
                            <div class="author-avatar"><i class="bi bi-person"></i></div>
                            <div>
                                <h4>Marie K.</h4>
                                <p>Patient since 2023</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="testimonial-card">
                        <div class="testimonial-rating">
                            <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="testimonial-text">
                            "Perfect for non-critical emergencies! I avoided hours of waiting at the hospital. 
                            The doctor listened and answered all my questions."
                        </p>
                        <div class="testimonial-author">
                            <div class="author-avatar"><i class="bi bi-person"></i></div>
                            <div>
                                <h4>Jean-Paul N.</h4>
                                <p>Patient since 2022</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="testimonial-card">
                        <div class="testimonial-rating">
                            <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="testimonial-text">
                            "A revolution in healthcare! Booking an appointment is simple 
                            and video consultations are of very good quality."
                        </p>
                        <div class="testimonial-author">
                            <div class="author-avatar"><i class="bi bi-person"></i></div>
                            <div>
                                <h4>Sophie M.</h4>
                                <p>Patient since 2024</p>
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
                        <span class="section-label">Contact Us</span>
                        <h2 class="section-title">Need help or information?</h2>
                        <p class="contact-description">
                            Our team is available to answer all your questions. 
                            Don't hesitate to reach out.
                        </p>
                        
                        <div class="contact-methods">
                            <div class="contact-method">
                                <div class="method-icon"><i class="bi bi-envelope"></i></div>
                                <div>
                                    <h4>Email</h4>
                                    <p>contact@medi-connect.com</p>
                                </div>
                            </div>
                            <div class="contact-method">
                                <div class="method-icon"><i class="bi bi-telephone"></i></div>
                                <div>
                                    <h4>Phone</h4>
                                    <p>+237 6XX XXX XXX</p>
                                </div>
                            </div>
                            <div class="contact-method">
                                <div class="method-icon"><i class="bi bi-clock"></i></div>
                                <div>
                                    <h4>Hours</h4>
                                    <p>24/7 - Available every day</p>
                                </div>
                            </div>
                        </div>
                        
                        <a href="contact.php" class="btn-link">
                            Go to contact form <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    
                    <div class="contact-quick-form">
                        <h3>Quick message</h3>
                        <form class="quick-form" id="quick-contact-form">
                            <div class="form-group">
                                <input type="text" placeholder="Your name" required>
                            </div>
                            <div class="form-group">
                                <input type="email" placeholder="Your email" required>
                            </div>
                            <div class="form-group">
                                <textarea placeholder="Your message" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="btn-submit">
                                Send <i class="bi bi-send"></i>
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
                    <h2>Ready to take care of your health?</h2>
                    <p>Join thousands of patients who trust Medi-Connect</p>
                    <div class="cta-actions">
                        <button class="btn-cta btn-cta-primary">
                            Create my account <i class="bi bi-arrow-right"></i>
                        </button>
                        <button class="btn-cta btn-cta-secondary">
                            Book an appointment <i class="bi bi-calendar-check"></i>
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
                            <li><a href="#services">Services</a></li>
                            <li><a href="#about">About</a></li>
                            <li><a href="#contact">Contact</a></li>
                            <li><a href="#">FAQ</a></li>
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
    </div>
    
    <script src="JS/index.js"></script>
</body>
</html>