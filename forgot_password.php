<?php

date_default_timezone_set("Asia/Karachi");

require_once "includes/db.php";
require_once "includes/send_mail.php";
session_start();

$error = "";
$success = "";
$info = "";
$step = 1; // Step 1: Enter email, Step 2: Enter code and new password

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Step 1: Send verification code to email
    if (isset($_POST["send_code"])) {
        $email = trim($_POST["email"]);

        if (empty($email)) {
            $error = "Please enter your email address!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address!";
        } else {
            // Check if email exists in database
            $query = "SELECT id, name FROM users WHERE email = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user) {
                $user_name = $user["name"];

                // Generate 6-digit verification code
                $reset_code = strval(rand(100000, 999999));
                $expires_at = date("Y-m-d H:i:s", strtotime("+30 minutes"));

                // Delete old reset codes for this email
                $delete_query = "DELETE FROM password_resets WHERE email = ?";
                $stmt = $conn->prepare($delete_query);
                $stmt->bind_param("s", $email);
                $stmt->execute();

                // Insert new reset code into database
                $insert_query =
                    "INSERT INTO password_resets (email, reset_code, expires_at) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($insert_query);

                $stmt->bind_param("sss", $email, $reset_code, $expires_at);
                if ($stmt->execute()) {
                    // Prepare email content
                    $subject =
                        "Password Reset Code - DecorSlot";

                    // HTML Email Template
                    $message =
                        "
                    <html>
                    <head>
                        <style>
                            body { 
                            font-family: Arial, 
                            sans-serif; 
                            line-height: 1.6; 
                            color: #333; 
                            background: #f4f4f4; 
                            margin: 0; 
                            padding: 0; 
                            }

                            .email-container { 
                            
                            max-width: 
                            600px; 
                            margin: 20px auto; 
                            background: white; 
                            border-radius: 10px; 
                            overflow: hidden; 
                            box-shadow: 0 0 20px rgba(0,0,0,0.1); 
                            }

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

                            .code-container { 
                            background: #f8f9fa; 
                            border: 3px dashed #0d6efd; 
                            border-radius: 10px; 
                            padding: 30px; 
                            text-align: center; 
                            margin: 30px 0; 
                            }


                            .code-label { 
                            font-size: 14px; 
                            color: #666; 
                            margin-bottom: 10px; 
                            }

                            .verification-code { 
                            font-size: 42px; 
                            font-weight: bold; 
                            color: #0d6efd; 
                            letter-spacing: 8px; 
                            font-family: 'Courier New', monospace; 
                            margin: 10px 0; 
                            }

                            .instructions { 
                            background: #e7f3ff; 
                            border-left: 4px solid #2196F3; 
                            padding: 15px; 
                            margin: 20px 0; 
                            border-radius: 5px; 
                            }

                            .warning { background: #fff3cd; 
                            border-left: 4px solid #ffc107; 
                            padding: 15px; 
                            margin: 20px 0; 
                            border-radius: 5px; 
                            }

                            .footer { background: #f8f9fa; 
                            padding: 20px; 
                            text-align: center; 
                            font-size: 12px; 
                            color: #666; 
                            border-top: 1px solid #dee2e6; 
                            }

                            .btn { 
                            display: inline-block; 
                            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); 
                            color: white; 
                            padding: 12px 30px; 
                            text-decoration: none; 
                            border-radius: 25px; 
                            margin: 20px 0; 
                            }

                            ul { padding-left: 20px; }
                            li { margin: 8px 0; }
                        </style>
                    </head>
                    <body>
                        <div class='email-container'>
                            <div class='email-header'>
                                <h1>🔐 Password Reset Request</h1>
                            </div>
                            <div class='email-body'>
                                <p class='greeting'>Hello <strong>" .
                        htmlspecialchars($user_name) .
                        "</strong>,</p>
                                
                                <p>We received a request to reset the password for your account associated with <strong>" .
                        htmlspecialchars($email) .
                        "</strong>.</p>
                                
                                <div class='instructions'>
                                    <strong>📧 Your Verification Code:</strong>
                                    <p style='margin: 5px 0 0 0;'>Please use the code below to reset your password.</p>
                                </div>
                                
                                <div class='code-container'>
                                    <div class='code-label'>VERIFICATION CODE</div>
                                    <div class='verification-code'>" .
                        $reset_code .
                        "</div>
                                    <div class='code-label' style='margin-top: 10px;'>Enter this code on the password reset page</div>
                                </div>
                                
                                <div class='warning'>
                                    <strong>⚠️ Important Security Information:</strong>
                                    <ul style='margin: 10px 0 0 0;'>
                                        <li>This code will <strong>expire in 30 minutes</strong></li>
                                        <li>This code can only be used <strong>once</strong></li>
                                        <li><strong>Never share</strong> this code with anyone</li>
                                        <li>Our team will <strong>never ask</strong> for this code</li>
                                    </ul>
                                </div>
                                
                                <p style='margin-top: 30px;'>If you did not request a password reset, please ignore this email or contact our support team immediately if you have concerns about your account security.</p>
                                
                                <p style='margin-top: 20px;'>Best regards,<br><strong>DecorSlot Service Team</strong></p>
                            </div>
                            <div class='footer'>
                                <p style='margin: 5px 0;'><strong>DecorSlot</strong></p>
                                <p style='margin: 5px 0;'>© 2026 All Rights Reserved</p>
                                <p style='margin: 5px 0;'>This is an automated email. Please do not reply to this message.</p>
                            </div>
                        </div>
                    </body>
                    </html>
                    ";

                    // Send email using existing utility
                    if (sendEmail($email, $subject, $message)) {
                        // Move to step 2
                        $_SESSION["reset_email"] = $email;
                        $step = 2;

                        // Always show the same message for security (don't reveal if email was sent or not)
                        $success =
                            "If an account exists with " .
                            htmlspecialchars($email) .
                            ", a verification code has been sent to that email address.";
                        $info =
                            "Please check your inbox and spam/junk folder for the verification code. The code will expire in 30 minutes.";
                    } else {
                        $error =
                            "Email could not be sent. Please check the mailer logs.";
                    }
                } else {
                    $error =
                        "Failed to generate verification code. Please try again!";
                }
            } else {
                $error = "No account found with this email address!";
            }
        }
    }

    // Step 2: Verify code and reset password
    if (isset($_POST["reset_password"])) {
        if (!isset($_SESSION["reset_email"])) {
            $error =
                "Session expired. Please start the password reset process again.";
            $step = 1;
        } else {
            $email = $_SESSION["reset_email"];
            $reset_code = preg_replace("/\D/", "", $_POST["reset_code"]);

            $new_password = $_POST["new_password"];
            $confirm_password = $_POST["confirm_password"];

            if (
                empty($reset_code) ||
                empty($new_password) ||
                empty($confirm_password)
            ) {
                $error = "All fields are required!";
                $step = 2;
            } elseif (strlen($new_password) < 6) {
                $error = "Password must be at least 6 characters long!";
                $step = 2;
            } elseif ($new_password !== $confirm_password) {
                $error = "Passwords do not match!";
                $step = 2;
            } else {
                // Verify reset code from database
                $query =
                    "SELECT id FROM password_resets WHERE email = ? AND reset_code = ? AND expires_at > NOW()";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ss", $email, $reset_code);
                $stmt->execute();
                $stmt_result = $stmt->get_result();
                $result = $stmt_result->fetch_assoc();

                if ($result) {
                    // Update password in database
                    $hashed_password = password_hash(
                        $new_password,
                        PASSWORD_DEFAULT,
                    );
                    $update_query =
                        "UPDATE users SET password_hash = ? WHERE email = ?";
                    $stmt = $conn->prepare($update_query);

                    $stmt->bind_param("ss", $hashed_password, $email);
                    if ($stmt->execute()) {
                        // Delete used reset code
                        $delete_query =
                            "DELETE FROM password_resets WHERE email = ?";
                        $stmt = $conn->prepare($delete_query);
                        $stmt->bind_param("s", $email);
                        $stmt->execute();

                        // Clear session data
                        unset($_SESSION["reset_email"]);
                        unset($_SESSION["demo_code"]);

                        $success =
                            "Password reset successful! You can now login with your new password. Redirecting to login page...";
                        header("refresh:3;url=login.php");
                        $step = 3; // Success step
                    } else {
                        $error = "Failed to update password. Please try again!";
                        $step = 2;
                    }
                } else {
                    $error =
                        "Invalid or expired verification code! Please request a new code.";
                    $step = 2;
                }
            }
        }
    }
}

// Check if already on step 2
if (isset($_SESSION["reset_email"]) && $step == 1) {
    $step = 2;
}

include "includes/header.php";
?>

<style>
    .auth-step-text { text-align: center; color: var(--text-light); margin-bottom: 2rem; }
    .auth-submit-btn { width: 100%; }
    .auth-code-input { letter-spacing: 5px; font-weight: bold; font-size: 20px; text-align: center; }
    .auth-code-helper { color: var(--text-light); display: block; margin-top: 5px; }
    .auth-startover-container { text-align: center; margin-top: 1.5rem; }
    .auth-startover-link { color: var(--text-light); font-size: 0.9rem; }
    .auth-login-text { text-align: center; margin-top: 1.5rem; }
    .auth-login-link { color: var(--primary-color); }
</style>

<div class="auth-container">
    <div class="auth-form">
        <h2>Forgot Password</h2>
        
        <?php if ($step == 1): ?>
        <p class="auth-step-text">Enter your email to receive a verification code.</p>
        <?php elseif ($step == 2): ?>
        <p class="auth-step-text">Verification code sent to <strong><?= htmlspecialchars(
            $_SESSION["reset_email"],
        ) ?></strong></p>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars(
                $error,
            ); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars(
                $success,
            ); ?></div>
        <?php endif; ?>

        <?php if ($info): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars(
                $info,
            ); ?></div>
        <?php endif; ?>

        <?php if ($step == 1): ?>
        <!-- STEP 1: Enter Email Address -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" 
                       placeholder="Enter your registered email" 
                       value="<?php echo isset($_POST["email"])
                           ? htmlspecialchars($_POST["email"])
                           : ""; ?>"
                       required autofocus>
            </div>
            <button type="submit" name="send_code" class="btn-primary auth-submit-btn">
                Send Verification Code
            </button>
        </form>
        
        <?php elseif ($step == 2): ?>
        <!-- STEP 2: Enter Verification Code & New Password -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="reset_code">Verification Code</label>
                <input type="text" id="reset_code" name="reset_code" class="form-control auth-code-input" 
                       placeholder="Enter 6-digit code" 
                       maxlength="6" 
                       required autofocus>
                <small class="auth-code-helper">Code expires in 30 minutes</small>
            </div>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" class="form-control" 
                       placeholder="Enter new password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                       placeholder="Confirm your new password" required>
            </div>

            <button type="submit" name="reset_password" class="btn-primary auth-submit-btn">
                Reset Password
            </button>
            <div class="auth-startover-container">
                <a href="forgot_password.php" class="auth-startover-link" onclick="return confirm('Are you sure? This will cancel the current reset process.');">Start Over</a>
            </div>
        </form>
        <?php endif; ?>

        <p class="auth-login-text">Remember your password? <a href="login.php" class="auth-login-link">Login here</a></p>
    </div>
</div>

<?php include "includes/footer.php"; ?>
