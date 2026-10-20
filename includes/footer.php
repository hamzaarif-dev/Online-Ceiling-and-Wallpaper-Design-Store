<?php
$base_path =
    strpos($_SERVER["SCRIPT_NAME"], "/admin/") !== false ? "../" : ""; ?>
    </main>
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>DecorSlot</h3>
                <p>Your one-stop destination for premium ceiling and wallpaper decoration services.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="<?= $base_path ?>index.php">Home</a></li>
                    <li><a href="<?= $base_path ?>catalog.php">Catalog</a></li>
                    <li><a href="<?= $base_path ?>login.php">Login</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p>Email: booking@decorslot.com</p>
                <p>Phone: +92 324 7947566</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date(
                "Y",
            ); ?> DecorSlot. All Rights Reserved.</p>
        </div>
    </footer>
    <script src="<?= $base_path ?>assets/js/scripts.js"></script>
</body>
</html>
