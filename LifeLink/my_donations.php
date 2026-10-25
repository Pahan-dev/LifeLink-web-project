
<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php?redirect=my_donations.php");
    exit;
}

$user_id = (int) $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT * FROM donations WHERE user_id = ? ORDER BY created_at DESC"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Donations - LifeLink</title>

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

        .container {
            width: min(1000px, 94%);
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            color: white;
            font-size: 36px;
            margin: 10px 0 25px;
            text-shadow: 1px 2px 5px rgba(0, 0, 0, 0.5);
        }

        .top-actions {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .home-link {
            display: inline-block;
            padding: 11px 22px;
            background: #ffbfa1;
            color: #333;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: background 0.2s;
        }

        .home-link:hover {
            background: #ffa780;
        }

        .donation-card {
            background: rgba(255, 255, 255, 0.53);
            padding: 24px;
            margin: 20px 0;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            overflow-wrap: anywhere;
        }

        .donation-card h2 {
            color: #8b442b;
            margin-top: 0;
            margin-bottom: 12px;
        }

        .category {
            display: inline-block;
            color: #66351f;
            background: #ffe0d0;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }

        .description {
            line-height: 1.6;
        }

        .donation-card p {
            line-height: 1.5;
        }

        .posted-date {
            color: #444;
            font-size: 13px;
        }

        /* Edit and Delete buttons */
        .card-actions {
            display: flex;
            gap: 12px;
            margin-top: 20px;
            flex-wrap: wrap;
            align-items: center;
        }

        .edit-btn,
        .delete-btn {
            display: inline-block;
            padding: 10px 22px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
            font-family: Arial, sans-serif;
        }

        .edit-btn {
            background: #0c581a;
            color: white;
        }

        .edit-btn:hover {
            background: #0b3717;
        }

        .delete-btn {
            background: #b42318;
            color: white;
        }

        .delete-btn:hover {
            background: #8f1b13;
        }

        .card-actions form {
            margin: 0;
        }

        .empty {
            text-align: center;
            background: rgba(255, 255, 255, 0.92);
            padding: 30px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .empty h2 {
            color: #8b442b;
        }

        @media (max-width: 600px) {
            body {
                padding: 20px 10px;
            }

            h1 {
                font-size: 28px;
            }

            .donation-card {
                padding: 18px;
            }
        }
    </style>
</head>

<body>
    <div class="container">

        <h1>My Donations</h1>

        <div class="top-actions">
            <a class="home-link" href="index.html">Back to Home</a>
            <a class="home-link" href="donate.php">Add a Donation</a>
        </div>

        <?php if ($result->num_rows > 0): ?>

            <?php while ($row = $result->fetch_assoc()): ?>

                <div class="donation-card">

                    <h2>
                        <?php echo htmlspecialchars($row["item_name"]); ?>
                    </h2>

                    <span class="category">
                        <?php echo htmlspecialchars($row["category"]); ?>
                    </span>

                    <?php if (!empty($row["description"])): ?>
                        <p class="description">
                            <?php
                            echo nl2br(
                                htmlspecialchars($row["description"])
                            );
                            ?>
                        </p>
                    <?php endif; ?>

                    <p>
                        <strong>Location:</strong>
                        <?php echo htmlspecialchars($row["location"]); ?>
                    </p>

                    <p class="posted-date">
                        <strong>Posted:</strong>
                        <?php echo htmlspecialchars($row["created_at"]); ?>
                    </p>

                    <div class="card-actions">

                        <a class="edit-btn"
                           href="edit_donation.php?id=<?php echo (int) $row["id"]; ?>">
                            Edit
                        </a>

                        <form method="POST"
                              action="delete_donation.php"
                              onsubmit="return confirm('Are you sure you want to permanently delete this donation?');">

                            <input type="hidden"
                                   name="donation_id"
                                   value="<?php echo (int) $row["id"]; ?>">

                            <button type="submit" class="delete-btn">
                                Delete
                            </button>
                        </form>

                    </div>
                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="empty">
                <h2>You haven't posted any donations yet.</h2>
                <p>Your donations will appear here after you submit them.</p>

                <a class="home-link" href="donate.php">
                    Share Your First Donation
                </a>
            </div>

        <?php endif; ?>

    </div>
</body>
</html>
