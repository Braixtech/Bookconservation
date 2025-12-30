<?php
// index.php
session_start();
$page_title = "Home";
$page_js = "main.js";
ob_start();
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navigation.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Preserving Northern Nigeria's Historical Heritage</h1>
                <p>Arewa House serves as a centre of excellence in Historical Documentation and Research for the promotion of unity and greatness in Nigeria.</p>
                <div class="hero-buttons">
                    <a href="digital-library.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-book"></i> Explore Digital Library
                    </a>
                    <a href="research-portal.php" class="btn btn-outline btn-lg">
                        <i class="bi bi-search"></i> Start Research
                    </a>
                    <a href="volunteer.php" class="btn btn-secondary btn-lg">
                        <i class="bi bi-people"></i> Become a Volunteer
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Stats -->
    <section class="stats-section">
        <div class="container">
            <h2 class="section-title">Our Impact Across Northern Nigeria</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">12,543</div>
                    <div class="stat-label">Historical Collections</div>
                    <p class="text-muted text-small">Preserved manuscripts, documents, and artifacts</p>
                </div>
                <div class="stat-card">
                    <div class="stat-number">3,241</div>
                    <div class="stat-label">Conservation Projects</div>
                    <p class="text-muted text-small">Completed restoration and preservation works</p>
                </div>
                <div class="stat-card">
                    <div class="stat-number">8,921</div>
                    <div class="stat-label">Digital Assets</div>
                    <p class="text-muted text-small">Digitized manuscripts and documents</p>
                </div>
                <div class="stat-card">
                    <div class="stat-number">19</div>
                    <div class="stat-label">Northern States</div>
                    <p class="text-muted text-small">Covering all states in Northern Nigeria</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2 class="section-title">Our Core Services</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-archive"></i>
                    </div>
                    <h3>Collection Management</h3>
                    <p>Professional preservation and cataloging of historical materials from all 19 Northern states.</p>
                    <a href="collections.php" class="btn btn-outline">Explore Collections</a>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-tools"></i>
                    </div>
                    <h3>Conservation Services</h3>
                    <p>Expert restoration and preservation of manuscripts, documents, and cultural artifacts.</p>
                    <a href="conservation-projects.php" class="btn btn-outline">View Projects</a>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-book"></i>
                    </div>
                    <h3>Digital Library</h3>
                    <p>Access digitized collections including Arabic manuscripts, historical documents, and photographs.</p>
                    <a href="digital-library.php" class="btn btn-outline">Browse Library</a>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-search"></i>
                    </div>
                    <h3>Research Support</h3>
                    <p>Comprehensive research facilities and access to exclusive historical collections.</p>
                    <a href="research-portal.php" class="btn btn-outline">Start Research</a>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <h3>Volunteer Program</h3>
                    <p>Join our community of volunteers dedicated to preserving Northern Nigeria's heritage.</p>
                    <a href="volunteer.php" class="btn btn-outline">Join Us</a>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h3>Security & Preservation</h3>
                    <p>State-of-the-art security and climate-controlled storage for valuable collections.</p>
                    <a href="about.php#security" class="btn btn-outline">Learn More</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Northern States Section -->
    <section class="stats-section bg-light">
        <div class="container">
            <h2 class="section-title">Covering All 19 Northern States</h2>
            <div class="stats-grid">
                <div class="card">
                    <h4><i class="bi bi-geo-alt text-primary"></i> North West Zone</h4>
                    <ul>
                        <li>Jigawa</li>
                        <li>Kaduna</li>
                        <li>Kano</li>
                        <li>Katsina</li>
                        <li>Kebbi</li>
                        <li>Sokoto</li>
                        <li>Zamfara</li>
                    </ul>
                </div>
                
                <div class="card">
                    <h4><i class="bi bi-geo-alt text-primary"></i> North East Zone</h4>
                    <ul>
                        <li>Adamawa</li>
                        <li>Bauchi</li>
                        <li>Borno</li>
                        <li>Gombe</li>
                        <li>Taraba</li>
                        <li>Yobe</li>
                    </ul>
                </div>
                
                <div class="card">
                    <h4><i class="bi bi-geo-alt text-primary"></i> North Central Zone</h4>
                    <ul>
                        <li>Benue</li>
                        <li>Kogi</li>
                        <li>Kwara</li>
                        <li>Nasarawa</li>
                        <li>Niger</li>
                        <li>Plateau</li>
                    </ul>
                </div>
                
                <div class="card">
                    <h4><i class="bi bi-geo-alt text-primary"></i> Arewa House Location</h4>
                    <p><strong>Address:</strong> No. 1 Rabah Road, Ungwan Sarki, Kaduna State, Nigeria</p>
                    <p><strong>Facilities:</strong> Museum, Library, Archive, Conference Halls, Research Accommodation</p>
                    <a href="about.php#location" class="btn btn-sm btn-outline mt-3">Visit Us</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="hero" style="background: linear-gradient(135deg, var(--arewa-green) 0%, var(--primary-color) 100%);">
        <div class="container">
            <div class="hero-content">
                <h2>Contribute to Historical Preservation</h2>
                <p>Join us in preserving Northern Nigeria's rich history for future generations. Whether as a researcher, volunteer, or donor, your contribution matters.</p>
                <div class="hero-buttons">
                    <a href="register.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-person-plus"></i> Create Account
                    </a>
                    <a href="contact.php" class="btn btn-outline btn-lg">
                        <i class="bi bi-envelope"></i> Contact Us
                    </a>
                    <a href="about.php" class="btn btn-secondary btn-lg">
                        <i class="bi bi-info-circle"></i> Learn More
                    </a>
                </div>
            </div>
        </div>
    </section>

<?php
$content = ob_get_clean();
include 'includes/footer.php';
?>