<?php
require_once "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($name === "" || $email === "" || $phone === "" || $password === "") {
        $message = "Please fill in every field.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $message = "Your password must contain at least 8 characters.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "This email is already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, phone, password)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("ssss", $name, $email, $phone, $hashedPassword);

            if ($stmt->execute()) {
                header("Location: login.php?registered=1");
                exit;
            } else {
                $message = "Registration failed. Please try again.";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - LifeLink</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .register-box {
            width: min(400px, 90%);
            margin: 60px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            color: #333;
        }

        .register-box h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .register-box label {
            display: block;
            margin-top: 12px;
        }

        .register-box input {
            width: 100%;
            padding: 11px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .register-box button {
            width: 100%;
            margin-top: 20px;
            padding: 12px;
            background: #ffbfa1;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .register-box p {
            text-align: center;
        }

        .message {
            color: #b00020;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="register-box">
        <h1>Create Account</h1>

        <?php if ($message !== ""): ?>
            <p class="message">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name"
                   maxlength="100" required>

            <label for="email">Email Address</label>
            <input type="email" id="email" name="email"
                   maxlength="150" required>

            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone"
                   maxlength="20" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   minlength="8" required>

            <button type="submit">Register</button>
        </form>

        <p>Already have an account?
            <a href="login.php">Log in</a>
        </p>

        <p><a href="index.html">Back to Home</a></p>
    </div>
</body>
</html>
