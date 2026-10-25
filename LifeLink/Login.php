
<?php
session_start();

$redirect = $_GET["redirect"] ?? $_POST["redirect"] ?? "donate.php";

$allowed_redirects = ["donate.php", "my_donations.php"];

if (!in_array($redirect, $allowed_redirects, true)) {
    $redirect = "donate.php";
}

require_once "db.php";

$message = "";

if (isset($_GET["registered"])) {
    $message = "Registration successful! You can now log in.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $message = "Please enter your email and password.";
    } else {
        $stmt = $conn->prepare(
            "SELECT id, name, email, password FROM users WHERE email = ?"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user["password"])) {
                session_regenerate_id(true);

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_name"] = $user["name"];

                header("Location: " . $redirect);
                exit;
            }
        }

        $message = "Incorrect email or password.";
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LifeLink</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .login-box {
            width: min(400px, 90%);
            margin: 70px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            color: #333;
            box-sizing: border-box;
        }

        .login-box h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .login-box label {
            display: block;
            margin-top: 14px;
        }

        .login-box input {
            width: 100%;
            padding: 11px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .login-box button {
            width: 100%;
            margin-top: 20px;
            padding: 12px;
            background: #ffbfa1;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .login-box p {
            text-align: center;
        }

        .message {
            text-align: center;
            color: #b00020;
        }

        .success {
            color: #18743a;
        }
    </style>
</head>

<body>
    <div class="login-box">
        <h1>Welcome Back</h1>

        <?php if ($message !== ""): ?>
            <p class="message <?= isset($_GET["registered"]) && $_SERVER["REQUEST_METHOD"] !== "POST" ? "success" : "" ?>">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input
                type="hidden"
                name="redirect"
                value="<?= htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8') ?>"
            >

            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Log In</button>
        </form>

        <p>
            Don't have an account?
            <a href="register.php">Register here</a>
        </p>

        <p><a href="index.html">Back to Home</a></p>
    </div>
</body>
</html>
