<?php
require_once "includes/db.php";
session_start();

if (isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (empty($email) || empty($password)) {
        $error = "Both email and password are required.";
    } else {
        $stmt = $conn->prepare(
            "SELECT id, name, password_hash, role FROM users WHERE email = ?",
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user["password_hash"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["role"] = $user["role"];

            if ($user["role"] === "admin") {
                header("Location: admin/index.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}

include "includes/header.php";
?>

<style>
    /* Forgot Password Container */
.auth-forgot-container {
    text-align: right;
    margin-bottom: 1.5rem;
}

/* Forgot Password Link */
.auth-forgot-link {
    color: var(--text-light);
    font-size: 0.9rem;
}

/* Submit Button */
.auth-submit-btn {
    width: 100%;
}

/* Register Text */
.auth-register-text {
    text-align: center;
    margin-top: 1.5rem;
}

/* Register Link */
.auth-register-link {
    color: var(--primary-color);
}
</style>

<div class="auth-container">
    <div class="auth-form">
        <h2>Welcome Back</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars(
                    $_POST["email"] ?? "",
                ) ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="auth-forgot-container">
                <a href="forgot_password.php" class="auth-forgot-link">Forgot password?</a>
            </div>
            <button type="submit" class="btn-primary auth-submit-btn">Login</button>
        </form>
        <p class="auth-register-text">Don't have an account? <a href="register.php" class="auth-register-link">Register here</a></p>
    </div>
</div>

<?php include "includes/footer.php"; ?>
