<?php
require_once "includes/db.php";
require_once "includes/send_mail.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if (empty($name) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()) {
            $error = "Email is already registered.";
        } else {
            // Register user
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, phone, address, password_hash, role) VALUES (?, ?, ?, ?, ?, 'customer')",
            );
            $stmt->bind_param("sssss", $name, $email, $phone, $address, $hash);
            if ($stmt->execute()) {
                $success = "Registration successful! You can now login.";

                // Send welcome email using PHPMailer
                $subject = "Welcome to DecorSlot!";
                $body = "
        <html>
        <head>
            <style>
                body { 

                font-family: Arial, sans-serif; 
                line-height: 1.6; 
                color: #333; 
                background: #f4f4f4; 
                margin: 0; 
                padding: 0; }

                .email-container { 
                
                max-width: 600px; 
                margin: 20px auto; 
                background: white; 
                border-radius: 10px; 
                overflow: hidden; 
                box-shadow: 0 0 20px rgba(0,0,0,0.1); }

                .email-header { 
                background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); 
                color: white; 
                padding: 40px 20px; 
                text-align: center; 
                }
                .email-header h1 { 
                margin: 0; 
                font-size: 28px; 
                }

                .email-body { 
                padding: 40px 30px; 
                }

                .greeting { 
                font-size: 18px; 
                color: #333; 
                margin-bottom: 20px; 
                
                }

                .details-container { 
                
                background: #f8f9fa; 
                border-left: 4px solid #0d6efd; 
                padding: 20px; margin: 20px 0; 
                border-radius: 4px; 
                }

                .footer { 
                background: #f8f9fa; 
                padding: 20px; 
                text-align: center; 
                font-size: 12px; 
                color: #666; 
                border-top: 1px solid #dee2e6; 
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <h1>🎉 Welcome to DecorSlot!</h1>
                </div>
                <div class='email-body'>
                    <p class='greeting'>Hello <strong>" . htmlspecialchars($name) . "</strong>,</p>
                    
                    <p>Thank you for registering at DecorSlot. We are excited to help you transform your space with premium ceiling and wallpaper decorations.</p>
                    
                    <div class='details-container'>
                        <h3 style='margin-top: 0; color: #0d6efd;'>Getting Started</h3>
                        <p style='margin-bottom: 0;'>You can now log in to your account to browse our exclusive catalog, book installations, and track your orders seamlessly.</p>
                    </div>
                    
                    <p style='margin-top: 20px;'>Best regards,<br><strong>DecorSlot Team</strong></p>
                </div>
                <div class='footer'>
                    <p style='margin: 5px 0;'><strong>DecorSlot Service</strong></p>
                    <p style='margin: 5px 0;'>© " . date('Y') . " All Rights Reserved</p>
                    <p style='margin: 5px 0;'>This is an automated email. Please do not reply to this message.</p>
                </div>
            </div>
        </body>
        </html>
        ";
                sendEmail($email, $subject, $body); 
            } else {
                $error =
                    "An error occurred during registration. Please try again.";
            }
        }
    }
}

include "includes/header.php";
?>

<style>
  /* Go to Login Button */
.auth-goto-login-btn {
    display: block;
    text-align: center;
    margin-top: 1rem;
}

/* Submit Button */
.auth-submit-btn {
    width: 100%;
}

/* Login Text */
.auth-login-text {
    text-align: center;
    margin-top: 1.5rem;
}

/* Login Link */
.auth-login-link {
    color: var(--primary-color);
}
</style>

<div class="auth-container">
    <div class="auth-form">
        <h2>Create an Account</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars(
                $success,
            ) ?></div>
            <a href="login.php" class="btn-primary auth-goto-login-btn">Go to Login</a>
        <?php else: ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars(
                        $_POST["name"] ?? "",
                    ) ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars(
                        $_POST["email"] ?? "",
                    ) ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars(
                        $_POST["phone"] ?? "",
                    ) ?>">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" class="form-control" rows="2"><?= htmlspecialchars(
                        $_POST["address"] ?? "",
                    ) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn-primary auth-submit-btn">Register</button>
            </form>
            <p class="auth-login-text">Already have an account? <a href="login.php" class="auth-login-link">Login here</a></p>
        <?php endif; ?>
    </div>
</div>

<?php include "includes/footer.php"; ?>
