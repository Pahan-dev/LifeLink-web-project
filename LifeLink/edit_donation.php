
<?php
session_start();
require_once "db.php";

// Require login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php?redirect=my_donations.php");
    exit;
}

$user_id = (int) $_SESSION["user_id"];
$donation_id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$donation_id || $donation_id < 1) {
    header("Location: my_donations.php");
    exit;
}

$message = "";
$item_name = "";
$category = "";
$description = "";
$location = "";

// Retrieve only a donation owned by the logged-in user
$stmt = $conn->prepare(
    "SELECT item_name, category, description, location
     FROM donations
     WHERE id = ? AND user_id = ?"
);
$stmt->bind_param("ii", $donation_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$donation = $result->fetch_assoc();
$stmt->close();

if (!$donation) {
    http_response_code(404);
    exit("Donation not found or you are not allowed to edit it.");
}

$item_name = $donation["item_name"];
$category = $donation["category"];
$description = $donation["description"] ?? "";
$location = $donation["location"];

$allowed_categories = [
    "food",
    "medical supplies",
    "clothing",
    "education supplies",
    "other"
];

// Process form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $item_name = trim($_POST["item_name"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $location = trim($_POST["location"] ?? "");

    if (
        $item_name === "" ||
        strlen($item_name) > 150 ||
        $location === "" ||
        strlen($location) > 150 ||
        !in_array($category, $allowed_categories, true)
    ) {
        $message = "Please enter valid item details, category and location.";
    } elseif (strlen($description) > 10000) {
        $message = "The description is too long.";
    } else {
        // Update only if the donation belongs to this user
        $stmt = $conn->prepare(
            "UPDATE donations
             SET item_name = ?, category = ?, description = ?, location = ?
             WHERE id = ? AND user_id = ?"
        );

        $stmt->bind_param(
            "ssssii",
            $item_name,
            $category,
            $description,
            $location,
            $donation_id,
            $user_id
        );

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: my_donations.php?updated=1");
            exit;
        } else {
            $message = "Unable to update the donation. Please try again.";
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Donation - LifeLink</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 30px 15px;
            min-height: 100vh;
            color: #333;
            background:
                linear-gradient(
                    rgba(0, 0, 0, 0.55),
                    rgba(0, 0, 0, 0.55)
                ),
                url("2.png");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
        }

        .edit-box {
            width: min(600px, 95%);
            margin: 20px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.94);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        h1 {
            text-align: center;
            color: #8b442b;
            margin-top: 0;
        }

        label {
            display: block;
            margin-top: 16px;
            font-weight: bold;
        }

        input, select, textarea {
            width: 100%;
            padding: 11px;
            margin-top: 7px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font: inherit;
        }

        textarea {
            min-height: 110px;
            resize: vertical;
        }

        button, .back-link {
            display: inline-block;
            padding: 12px 20px;
            margin-top: 20px;
            border: none;
            border-radius: 6px;
            background: #ffbfa1;
            color: #333;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
        }

        button:hover, .back-link:hover {
            background: #ffa780;
        }

        .message {
            color: #b00020;
            text-align: center;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
    </style>
</head>

<body>
    <div class="edit-box">
        <h1>Edit Donation</h1>

        <?php if ($message !== ""): ?>
            <p class="message">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <form method="POST"
              action="edit_donation.php?id=<?php echo $donation_id; ?>">

            <label for="item_name">Item Name</label>
            <input
                type="text"
                id="item_name"
                name="item_name"
                maxlength="150"
                value="<?php echo htmlspecialchars($item_name); ?>"
                required
            >

            <label for="category">Category</label>
            <select id="category" name="category" required>
                <option value="">Select a category</option>

                <?php foreach ($allowed_categories as $option): ?>
                    <option
                        value="<?php echo htmlspecialchars($option); ?>"
                        <?php echo $category === $option ? "selected" : ""; ?>
                    >
                        <?php echo htmlspecialchars(ucwords($option)); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="description">Description</label>
            <textarea
                id="description"
                name="description"
                maxlength="10000"
            ><?php echo htmlspecialchars($description); ?></textarea>

            <label for="location">Location</label>
            <input
                type="text"
                id="location"
                name="location"
                maxlength="150"
                value="<?php echo htmlspecialchars($location); ?>"
                required
            >

            <div class="actions">
                <button type="submit">Save Changes</button>
                <a class="back-link" href="my_donations.php">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
