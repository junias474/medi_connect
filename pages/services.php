<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../design/services.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Our Services - Medi-Connect</title>
</head>
<body>
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
                <input type="search" id="search_input" placeholder="Search for a service...">
            </div>
            
            <nav class="main-nav">
                <a href="index.php" class="nav-link">Home</a>
                <a href="services.php" class="nav-link active">Services</a>
                <a href="about.php" class="nav-link">About</a>
                <a href="contact.php" class="nav-link">Contact</a>
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
            <a href="services.php" class="mobile-nav-link active">Services</a>
            <a href="about.php" class="mobile-nav-link">About</a>
            <a href="contact.php" class="mobile-nav-link">Contact</a>
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

    <!-- PAGE HERO -->
    <section class="page-hero">
        <div class="hero-content">
            <nav class="breadcrumb">
                <a href="index.php">Home</a>
                <i class="bi bi-chevron-right"></i>
                <span>Services</span>
            </nav>
            <h1 class="page-title">Our Services</h1>
            <p class="page-description">
                Comprehensive solutions for your health and well-being
            </p>
        </div>
        <div class="hero-decoration">
            <div class="deco-circle circle-1"></div>
            <div class="deco-circle circle-2"></div>
        </div>
    </section>

    <!-- DETAILED SERVICES -->
    <section class="services-detailed">
        <div class="container">
            <!-- Service 1: Online Consultation -->
            <div class="service-detail">
                <div class="service-detail-visual">
                    <div class="visual-card">
                        <i class="bi bi-camera-video-fill"></i>
                    </div>
                    <div class="visual-stats">
                        <div class="stat-badge">
                            <i class="bi bi-clock-history"></i>
                            <span>Available 24/7</span>
                        </div>
                        <div class="stat-badge">
                            <i class="bi bi-shield-check"></i>
                            <span>100% Secure</span>
                        </div>
                    </div>
                </div>
                
                <div class="service-detail-content">
                    <div class="service-tag">Main Service</div>
                    <h2>Online Medical Consultation</h2>
                    <p class="service-intro">
                        Consult a qualified doctor via video conference from the comfort of your home. 
                        Our teleconsultation platform gives you fast access to quality medical care, 
                        with no travel required.
                    </p>
                    
                    <div class="service-features-grid">
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-camera-video"></i></div>
                            <div class="feature-text">
                                <h4>HD Video Conference</h4>
                                <p>Optimal video and audio quality for an effective consultation</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-file-earmark-medical"></i></div>
                            <div class="feature-text">
                                <h4>Digital Prescription</h4>
                                <p>Receive your prescription directly by email after the consultation</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-archive"></i></div>
                            <div class="feature-text">
                                <h4>Medical History</h4>
                                <p>Access your complete and secure medical record at any time</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-chat-dots"></i></div>
                            <div class="feature-text">
                                <h4>Post-Consultation Follow-up</h4>
                                <p>Ask questions by messaging after your appointment</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="service-process">
                        <h3>How does it work?</h3>
                        <div class="process-steps">
                            <div class="process-step">
                                <span class="step-num">1</span>
                                <p>Select an available doctor</p>
                            </div>
                            <div class="process-step">
                                <span class="step-num">2</span>
                                <p>Describe your symptoms</p>
                            </div>
                            <div class="process-step">
                                <span class="step-num">3</span>
                                <p>Join the video consultation</p>
                            </div>
                            <div class="process-step">
                                <span class="step-num">4</span>
                                <p>Receive your prescription</p>
                            </div>
                        </div>
                    </div>
                    
                    <button class="btn-action">
                        Start a consultation <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Service 2: Appointment Booking -->
            <div class="service-detail reverse">
                <div class="service-detail-content">
                    <div class="service-tag popular">Most Popular</div>
                    <h2>Appointment Booking</h2>
                    <p class="service-intro">
                        Schedule your medical consultations in just a few clicks. Our smart system 
                        suggests available time slots based on your preferences and the doctors' availability.
                    </p>
                    
                    <div class="service-features-grid">
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-calendar-check"></i></div>
                            <div class="feature-text">
                                <h4>Real-Time Availability</h4>
                                <p>View available slots instantly</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-bell"></i></div>
                            <div class="feature-text">
                                <h4>Automatic Reminders</h4>
                                <p>SMS and email notifications before your appointment</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-arrow-repeat"></i></div>
                            <div class="feature-text">
                                <h4>Easy Rescheduling</h4>
                                <p>Modify or cancel your appointment in one click</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-person-badge"></i></div>
                            <div class="feature-text">
                                <h4>Doctor Selection</h4>
                                <p>Choose your doctor by desired specialty</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="service-benefits">
                        <h3>Benefits</h3>
                        <ul class="benefits-list">
                            <li><i class="bi bi-check-circle-fill"></i> Significant time savings</li>
                            <li><i class="bi bi-check-circle-fill"></i> Full flexibility in managing your appointments</li>
                            <li><i class="bi bi-check-circle-fill"></i> Access to detailed doctor profiles</li>
                            <li><i class="bi bi-check-circle-fill"></i> Instant email confirmation</li>
                            <li><i class="bi bi-check-circle-fill"></i> History of all your appointments</li>
                        </ul>
                    </div>
                    
                    <button class="btn-action">
                        Book an appointment now <i class="bi bi-calendar-plus"></i>
                    </button>
                </div>
                
                <div class="service-detail-visual">
                    <div class="visual-card">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <div class="visual-info-card">
                        <h4>Quick appointment</h4>
                        <p>Next available slot:</p>
                        <div class="next-slot">
                            <i class="bi bi-clock"></i>
                            <span>Today at 2:30 PM</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Service 3: Medical Orientation -->
            <div class="service-detail">
                <div class="service-detail-visual">
                    <div class="visual-card">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <div class="visual-map-preview">
                        <i class="bi bi-map"></i>
                        <p>Nearby healthcare centers</p>
                    </div>
                </div>
                
                <div class="service-detail-content">
                    <div class="service-tag">Innovative Service</div>
                    <h2>Medical Orientation & Geolocation</h2>
                    <p class="service-intro">
                        Quickly find the nearest healthcare center using our smart 
                        geolocation system. Access detailed information and optimized routes.
                    </p>
                    
                    <div class="service-features-grid">
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-map"></i></div>
                            <div class="feature-text">
                                <h4>Interactive Map</h4>
                                <p>View all healthcare centers near you</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-signpost"></i></div>
                            <div class="feature-text">
                                <h4>Optimized Routes</h4>
                                <p>Get the best directions to the chosen center</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-info-circle"></i></div>
                            <div class="feature-text">
                                <h4>Detailed Information</h4>
                                <p>Hours, available services and contact details</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-star"></i></div>
                            <div class="feature-text">
                                <h4>Reviews & Ratings</h4>
                                <p>Read evaluations from other patients</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="service-info-box">
                        <i class="bi bi-lightbulb"></i>
                        <div>
                            <h4>Did you know?</h4>
                            <p>
                                Our system can also direct you to the nearest emergency rooms, on-call pharmacies 
                                and vaccination centers based on your specific needs.
                            </p>
                        </div>
                    </div>
                    
                    <button class="btn-action">
                        Find a nearby center <i class="bi bi-geo-alt"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- ADDITIONAL SERVICES -->
    <section class="additional-services">
        <div class="container">
            <div class="section-header">
                <h2>Additional Services</h2>
                <p>More features to enhance your healthcare experience</p>
            </div>
            
            <div class="additional-grid">
                <div class="additional-card">
                    <div class="additional-icon"><i class="bi bi-chat-left-text"></i></div>
                    <h3>Secure Messaging</h3>
                    <p>Communicate with your doctor between consultations via our encrypted messaging system.</p>
                </div>
                <div class="additional-card">
                    <div class="additional-icon"><i class="bi bi-clipboard2-pulse"></i></div>
                    <h3>Health Tracking</h3>
                    <p>Monitor your vitals, treatments and appointments in a personalized dashboard.</p>
                </div>
                <div class="additional-card">
                    <div class="additional-icon"><i class="bi bi-capsule"></i></div>
                    <h3>Medication Reminders</h3>
                    <p>Never miss a dose again thanks to our smart notifications.</p>
                </div>
                <div class="additional-card">
                    <div class="additional-icon"><i class="bi bi-people"></i></div>
                    <h3>Family Account</h3>
                    <p>Manage your whole family's health from a single account.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA SECTION -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to benefit from our services?</h2>
                <p>Sign up for free and get started today</p>
                <div class="cta-buttons">
                    <button class="btn-cta primary">
                        Create my account <i class="bi bi-arrow-right"></i>
                    </button>
                    <a href="contact.php" class="btn-cta secondary">
                        Contact us <i class="bi bi-envelope"></i>
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
                        <li><a href="services.php#consultation">Online Consultation</a></li>
                        <li><a href="services.php#rdv">Appointment Booking</a></li>
                        <li><a href="services.php#orientation">Medical Orientation</a></li>
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

    <script src="../JS/services.js"></script>
</body>
</html>