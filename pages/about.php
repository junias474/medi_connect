<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="design/about.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>About Us - Medi-Connect</title>
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
                <input type="search" id="search_input" placeholder="Search...">
            </div>
            
            <nav class="main-nav">
                <a href="index.php" class="nav-link">Home</a>
                <a href="services.php" class="nav-link">Services</a>
                <a href="about.php" class="nav-link active">About</a>
                <a href="contact.php" class="nav-link">Contact</a>
            </nav>
            
            <div class="user-actions">
                <button class="btn btn-secondary" id="login_button">
                    <i class="bi bi-person"></i>
                    Login
                </button>
                <button class="btn btn-primary" id="signup_button">
                    Get Started
                    <i class="bi bi-arrow-right"></i>
                </button>
            </div>
            
            <button class="mobile-menu-toggle" id="mobile-menu-toggle">
                <span></span><span></span><span></span>
            </button>
        </div>
        
        <nav class="mobile-nav" id="mobile-nav">
            <a href="index.php" class="mobile-nav-link">Home</a>
            <a href="services.php" class="mobile-nav-link">Services</a>
            <a href="about.php" class="mobile-nav-link active">About</a>
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
                <span>About</span>
            </nav>
            <h1 class="page-title">Our Mission</h1>
            <p class="page-description">
                Making healthcare accessible to everyone, wherever they are
            </p>
        </div>
        <div class="hero-decoration">
            <div class="deco-circle circle-1"></div>
            <div class="deco-circle circle-2"></div>
        </div>
    </section>

    <!-- OUR STORY -->
    <section class="our-story">
        <div class="container">
            <div class="story-grid">
                <div class="story-content">
                    <span class="section-label">Our Story</span>
                    <h2>How Medi-Connect was born</h2>
                    <p class="story-text">
                        Medi-Connect was born in <strong>2020</strong> from a simple but powerful vision: 
                        to give everyone easy access to quality healthcare, 
                        regardless of their geographic location.
                    </p>
                    <p class="story-text">
                        Faced with the reality that many people struggle to quickly see a doctor — 
                        whether due to distance from healthcare facilities, endless waiting lines, 
                        or limited availability of health professionals — we decided to create an innovative solution.
                    </p>
                    <p class="story-text">
                        Today, Medi-Connect connects <strong>thousands of patients</strong> with 
                        more than <strong>150 qualified doctors</strong>, offering high-quality online medical 
                        consultations, available 24 hours a day, 7 days a week.
                    </p>
                </div>
                
                <div class="story-visual">
                    <div class="story-image-card">
                        <div class="year-badge">2020</div>
                        <i class="bi bi-calendar-event"></i>
                        <h3>Year Founded</h3>
                        <p>The beginning of our journey</p>
                    </div>
                    <div class="story-stats">
                        <div class="story-stat">
                            <h4>5000+</h4>
                            <p>Patients</p>
                        </div>
                        <div class="story-stat">
                            <h4>150+</h4>
                            <p>Doctors</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- OUR MISSION -->
    <section class="our-mission">
        <div class="container">
            <div class="section-header">
                <span class="section-label">Our Mission</span>
                <h2>What drives us every day</h2>
            </div>
            
            <div class="mission-content">
                <div class="mission-main">
                    <i class="bi bi-bullseye"></i>
                    <h3>Democratizing access to healthcare</h3>
                    <p>
                        Our mission is to make medical consultations accessible to all, 
                        by eliminating geographic and time barriers. We firmly believe 
                        that every person, wherever they are, deserves quick and easy access to 
                        qualified health professionals.
                    </p>
                </div>
                
                <div class="mission-pillars">
                    <div class="pillar-card">
                        <div class="pillar-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h4>Accessibility</h4>
                        <p>Healthcare for everyone, without discrimination or geographic barriers</p>
                    </div>
                    
                    <div class="pillar-card">
                        <div class="pillar-icon">
                            <i class="bi bi-award-fill"></i>
                        </div>
                        <h4>Quality</h4>
                        <p>Certified and experienced doctors to guarantee high-level consultations</p>
                    </div>
                    
                    <div class="pillar-card">
                        <div class="pillar-icon">
                            <i class="bi bi-lightning-fill"></i>
                        </div>
                        <h4>Innovation</h4>
                        <p>Using modern technologies to improve the healthcare experience</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- OUR VALUES -->
    <section class="our-values">
        <div class="container">
            <div class="section-header">
                <span class="section-label">Our Values</span>
                <h2>The principles that guide us</h2>
            </div>
            
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-number">01</div>
                    <div class="value-icon"><i class="bi bi-shield-check"></i></div>
                    <h3>Security & Privacy</h3>
                    <p>
                        Protecting your personal and medical data is our absolute priority. 
                        We use the most advanced security protocols to guarantee the 
                        confidentiality of your information.
                    </p>
                </div>
                
                <div class="value-card">
                    <div class="value-number">02</div>
                    <div class="value-icon"><i class="bi bi-heart-pulse"></i></div>
                    <h3>Compassion</h3>
                    <p>
                        We treat every patient with empathy, respect and attention. Your well-being 
                        is at the heart of our concerns, and we are committed to offering you a 
                        human and caring experience.
                    </p>
                </div>
                
                <div class="value-card">
                    <div class="value-number">03</div>
                    <div class="value-icon"><i class="bi bi-graph-up-arrow"></i></div>
                    <h3>Excellence</h3>
                    <p>
                        We continuously improve our services to offer you the best possible experience. 
                        Ongoing doctor training, technology updates and listening to your 
                        feedback are our guiding principles.
                    </p>
                </div>
                
                <div class="value-card">
                    <div class="value-number">04</div>
                    <div class="value-icon"><i class="bi bi-people"></i></div>
                    <h3>Inclusion</h3>
                    <p>
                        We believe in equitable access to care for all, regardless of age, 
                        gender, background or socioeconomic status. Health is a universal right.
                    </p>
                </div>
                
                <div class="value-card">
                    <div class="value-number">05</div>
                    <div class="value-icon"><i class="bi bi-lightbulb"></i></div>
                    <h3>Continuous Innovation</h3>
                    <p>
                        We constantly invest in new technologies to improve 
                        the accessibility, quality and efficiency of our telemedicine services.
                    </p>
                </div>
                
                <div class="value-card">
                    <div class="value-number">06</div>
                    <div class="value-icon"><i class="bi bi-hand-thumbs-up"></i></div>
                    <h3>Transparency</h3>
                    <p>
                        We believe in clear and honest communication with our patients and 
                        partners. No hidden fees, no exaggerated promises — just the truth.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- OUR TEAM -->
    <section class="our-team">
        <div class="container">
            <div class="section-header">
                <span class="section-label">Our Team</span>
                <h2>The people behind Medi-Connect</h2>
                <p>A passionate team dedicated to your health</p>
            </div>
            
            <div class="team-intro">
                <p>
                    Medi-Connect is above all a team of passionate people: developers, doctors, 
                    designers and digital health experts who work together to create the 
                    best telemedicine experience possible.
                </p>
            </div>
            
            <div class="team-stats-grid">
                <div class="team-stat-card">
                    <div class="stat-icon"><i class="bi bi-person-badge"></i></div>
                    <h3>150+</h3>
                    <p>Certified Doctors</p>
                </div>
                <div class="team-stat-card">
                    <div class="stat-icon"><i class="bi bi-laptop"></i></div>
                    <h3>20+</h3>
                    <p>Tech Experts</p>
                </div>
                <div class="team-stat-card">
                    <div class="stat-icon"><i class="bi bi-headset"></i></div>
                    <h3>15+</h3>
                    <p>Customer Support</p>
                </div>
                <div class="team-stat-card">
                    <div class="stat-icon"><i class="bi bi-globe"></i></div>
                    <h3>5</h3>
                    <p>Countries Covered</p>
                </div>
            </div>
        </div>
    </section>

    <!-- OUR IMPACT -->
    <section class="our-impact">
        <div class="container">
            <div class="impact-grid">
                <div class="impact-content">
                    <span class="section-label">Our Impact</span>
                    <h2>The numbers that speak</h2>
                    <p class="impact-intro">
                        Since our founding, we have had a significant impact on access to healthcare 
                        in our region. Here are some figures that reflect our commitment.
                    </p>
                    
                    <div class="impact-highlights">
                        <div class="highlight-item">
                            <i class="bi bi-check-circle-fill"></i>
                            <div>
                                <h4>98% satisfaction rate</h4>
                                <p>Our patients are satisfied with our services</p>
                            </div>
                        </div>
                        <div class="highlight-item">
                            <i class="bi bi-check-circle-fill"></i>
                            <div>
                                <h4>Reduced waiting time</h4>
                                <p>Consultation in less than 15 minutes on average</p>
                            </div>
                        </div>
                        <div class="highlight-item">
                            <i class="bi bi-check-circle-fill"></i>
                            <div>
                                <h4>24/7 Accessibility</h4>
                                <p>Doctors available at any time</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="impact-numbers">
                    <div class="number-card large">
                        <div class="number-value" data-target="10000">0</div>
                        <p>Consultations completed</p>
                    </div>
                    <div class="number-cards-row">
                        <div class="number-card">
                            <div class="number-value" data-target="5000">0</div>
                            <p>Active patients</p>
                        </div>
                        <div class="number-card">
                            <div class="number-value" data-target="150">0</div>
                            <p>Partner doctors</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- OUR COMMITMENT -->
    <section class="our-commitment">
        <div class="container">
            <div class="commitment-card">
                <div class="commitment-icon">
                    <i class="bi bi-bookmark-heart-fill"></i>
                </div>
                <div class="commitment-content">
                    <h2>Our commitment to you</h2>
                    <p>
                        At Medi-Connect, we are committed to providing the highest quality telemedicine services. 
                        Every doctor on our platform is rigorously selected and verified to guarantee 
                        your safety and satisfaction.
                    </p>
                    <p>
                        We continuously invest in improving our technology, 
                        training our teams and listening to your feedback to offer you an 
                        exceptional experience at every consultation.
                    </p>
                    <button class="btn-commitment">
                        Discover our services
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
                <h2>Join thousands of satisfied patients</h2>
                <p>Start your health journey with Medi-Connect today</p>
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

    <script src="JS/about.js"></script>
</body>
</html>