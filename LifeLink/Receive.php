
<?php
require_once "db.php";

$search = trim($_GET["search"] ?? "");

$sql = "SELECT donations.*, users.name AS donor_name,
               users.phone AS donor_phone
        FROM donations
        JOIN users ON donations.user_id = users.id";

if ($search !== "") {
    $sql .= " WHERE donations.item_name LIKE ?
              OR donations.category LIKE ?
              OR donations.location LIKE ?
              OR donations.description LIKE ?
              OR users.name LIKE ?";
}

$sql .= " ORDER BY donations.created_at DESC";

$stmt = $conn->prepare($sql);

if ($search !== "") {
    $term = "%" . $search . "%";
    $stmt->bind_param(
        "sssss",
        $term,
        $term,
        $term,
        $term,
        $term
    );
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Donations - LifeLink</title>
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            background-color: #333;
            background-image:
                linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)),
                url("2.png");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
        }

        .receive-container {
            width: min(1100px, 94%);
            margin: 35px auto;
            color: #333;
        }

        .receive-heading {
            text-align: center;
            color: white;
            margin-bottom: 25px;
            text-shadow: 1px 1px 4px #222;
        }

        .search-form {
            display: flex;
            gap: 10px;
            margin: 20px auto;
            max-width: 650px;
        }

        .search-form input {
            flex: 1;
            min-width: 0;
            padding: 12px;
            border: none;
            border-radius: 6px;
        }

        .search-form button {
            padding: 12px 20px;
            background: #ffbfa1;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .donation-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }

        .donation-card {
            background: rgba(241, 255, 242, 0.69);
            padding: 22px;
            border-radius: 12px;
            overflow-wrap: anywhere;
        }

        .donation-card h2 {
            margin-top: 0;
            color: #8b442b;
        }

        .donation-card p {
            line-height: 1.5;
        }

        .donation-card .category {
            display: inline-block;
            padding: 5px 10px;
            background: #ffbfa1;
            border-radius: 20px;
            font-size: 13px;
        }

        .contact-button {
            display: inline-block;
            padding: 10px 14px;
            background: #ffbfa1;
            color: #222;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }

        .no-results {
            text-align: center;
            background: rgba(59, 93, 66, 0.7);
            padding: 25px;
            border-radius: 10px;
             color: white;
        }

        .page-links {
            text-align: center;
            margin: 25px;
        }

        .page-links a {
            color: white;
            margin: 0 10px;
        }

        @media (max-width: 480px) {
            .search-form {
                flex-direction: column;
            }
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
    background-color: #502410;
}
    </style>
</head>

<body>
    <main class="receive-container">
        <div class="receive-heading">
            <h1>Available Donations</h1>
            <p>Find resources shared by your community.</p>
        </div>

        <form class="search-form" method="GET" action="receive.php">
            <input
                type="search"
                name="search"
                placeholder="Search items, categories or locations..."
                value="<?= htmlspecialchars($search) ?>"
            >
            <button type="submit">Search</button>
        </form>

        <?php if ($result->num_rows > 0): ?>
            <div class="donation-grid">
                <?php while ($donation = $result->fetch_assoc()): ?>
                    <article class="donation-card">
                        <h2><?= htmlspecialchars($donation["item_name"]) ?></h2>

                        <span class="category">
                            <?= htmlspecialchars($donation["category"]) ?>
                        </span>

                        <p>
                            <?= nl2br(htmlspecialchars(
                                $donation["description"] ?: "No description provided."
                            )) ?>
                        </p>

                        <hr>

                        <p>
                            <strong>Donor:</strong>
                            <?= htmlspecialchars($donation["donor_name"]) ?>
                        </p>

                        <p>
                            <strong>Location:</strong>
                            <?= htmlspecialchars($donation["location"]) ?>
                        </p>

                        <p>
                            <strong>Phone:</strong>
                            <?= htmlspecialchars($donation["donor_phone"]) ?>
                        </p>

                        <p>
                            <small>Posted:
                                <?= htmlspecialchars($donation["created_at"]) ?>
                            </small>
                        </p>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <h2>No donations found</h2>
                <p>
                    <?= $search !== ""
                        ? "Try a different search term."
                        : "There are no donations available yet. Check back later!" ?>
                </p>
            </div>
        <?php endif; ?>

        <div class="page-links">
            <p><a href="index.html" class="home-btn">Home</a></p>
        </div>
    </main>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
