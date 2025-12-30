<?php
// includes/footer.php
?>
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <div class="footer-logo">
                        <i class="bi bi-house-door"></i> Arewa Conservation
                    </div>
                    <p>Centre of Excellence in Historical Documentation and Research for Northern Nigeria.</p>
                    <div class="d-flex gap-3 mt-4">
                        <a href="#" class="text-white"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-instagram"></i></a>
                    </div>
                </div>
                
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About Arewa House</a></li>
                        <li><a href="collections.php">Collections</a></li>
                        <li><a href="digital-library.php">Digital Library</a></li>
                        <li><a href="research-portal.php">Research Portal</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h4>Services</h4>
                    <ul>
                        <li><a href="conservation-projects.php">Conservation</a></li>
                        <li><a href="digital-library.php">Digitization</a></li>
                        <li><a href="research-portal.php">Research Support</a></li>
                        <li><a href="volunteer.php">Volunteer Program</a></li>
                        <li><a href="about.php#education">Educational Programs</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h4>Contact Info</h4>
                    <ul>
                        <li><i class="bi bi-geo-alt"></i> No. 1 Rabah Road, Ungwan Sarki, Kaduna</li>
                        <li><i class="bi bi-telephone"></i> +234 803 123 4567</li>
                        <li><i class="bi bi-envelope"></i> info@arewahouse.org</li>
                        <li><i class="bi bi-clock"></i> Mon-Fri: 8AM-6PM</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Arewa Conservation Platform. All rights reserved. | 
                    <a href="privacy.php" class="text-muted">Privacy Policy</a> | 
                    <a href="terms.php" class="text-muted">Terms of Service</a>
                </p>
                <p class="text-small text-muted">Ahmadu Bello University, Zaria | Centre for Historical Documentation and Research</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="js/main.js"></script>
    <?php if(isset($page_js)): ?>
        <script src="js/<?php echo $page_js; ?>"></script>
    <?php endif; ?>
    
    <?php if(isset($custom_js)): ?>
        <script>
            <?php echo $custom_js; ?>
        </script>
    <?php endif; ?>
</body>
</html>