<?php
session_start();
require_once "db.php";

// Only logged users can put donaton
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $item_name = trim($_POST["item_name"] ?? "");
    $category = $_POST["category"] ?? "";
    $description = trim($_POST["description"] ?? "");
    $location = trim($_POST["location"] ?? "");

    $allowed_categories = [
        "Food",
        "Medicine & Medical Supplies",
        "Clothing",
        "Education Supplies",
        "Other"
    ];

    if ($item_name === "" || $location === "") {
        $message = "Please enter the item name and location.";
    } elseif (!in_array($category, $allowed_categories, true)) {
        $message = "Please select a valid category.";
    } elseif (strlen($item_name) > 150 || strlen($location) > 150) {
        $message = "The item name or location is too long.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO donations
            (user_id, item_name, category, description, location)
            VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "issss",
            $_SESSION["user_id"],
            $item_name,
            $category,
            $description,
            $location
        );

        if ($stmt->execute()) {
            $message = "Your donation has been published successfully!";
        } else {
            $message = "Could not publish your donation. Please try again.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate - LifeLink</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .donate-box {
            width: min(520px, 92%);
            margin: 40px auto;
            padding: 30px;
            background: rgba(75, 96, 74, 0.68);
            color: #efefef;
            border-radius: 12px;
            box-sizing: border-box;
        }

        .donate-box h1 {
            text-align: center;
        }

        .donate-box label {
            display: block;
            margin-top: 14px;
        }

        .donate-box input,
        .donate-box select,
        .donate-box textarea {
            width: 100%;
            padding: 11px;
            margin-top: 6px;
            border: 1px solid #696969;
            border-radius: 6px;
            box-sizing: border-box;
            font: inherit;
        }

        .donate-box button {
            width: 100%;
            margin-top: 20px;
            padding: 12px;
            background: #ffbfa1;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        .donate-box .message {
            text-align: center;
            color: #18743a;
        }

        .donate-box .error {
            color: #b00020;
        }

        .donate-box p {
            text-align: center;
        }
        
body {
    background-color: #333;
    background-image:
        linear-gradient(rgba(0, 0, 0, 0.25), rgba(0, 0, 0, 0.25)),
        url("2.png");
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
    min-height: 100vh;
}
.home-btn {
    display: inline-block;
    background-color: #1e3a5f00;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 6px;
    margin-top: 20px;
    position: relative;
}

.home-btn:hover {
    background-color: #24431f;
}

    </style>
</head>

<body>
    <div class="donate-box">
        <h1>Share a Donation</h1>
        <p>Welcome, <?= htmlspecialchars($_SESSION["user_name"]) ?>!</p>
        <p>Share a resource with someone who needs it.</p>

        <?php if ($message !== ""): ?>
            <p class="<?= strpos($message, 'successfully') !== false ? 'message' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="donate.php">
            <label for="item_name">What are you donating?</label>
            <input type="text" id="item_name" name="item_name"
                   maxlength="150" required>

            <label for="category">Category</label>
            <select id="category" name="category" required>
                <option value="">Choose a category</option>
                <option value="Food">Food</option>
                <option value="Medicine & Medical Supplies">Medicine & Medical Supplies</option>
                <option value="Clothing">Clothing</option>
                <option value="Education Supplies">Education Supplies</option>
                <option value="Other">Other</option>
            </select>

            <label for="description">Description</label>
            <textarea id="description" name="description"
                      rows="4" maxlength="2000"
                      placeholder="Describe the item and its condition"></textarea>

            <label for="location">Location</label>
            <input type="text" id="location" name="location"
                   maxlength="150" required
                   placeholder="e.g. Galle, Sri Lanka">

            <button type="submit">Publish Donation</button>
        </form>

        <p><a href="index.html" class="home-btn">Home</a></p>
    </div>
</body>
</html>
