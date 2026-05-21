<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: saas_auth.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $pdo->prepare("
        INSERT INTO packages(name, duration_hours, speed_down, speed_up, price, status)
        VALUES (?, ?, ?, ?, ?, 'active')
    ");

    $stmt->execute([
        $_POST["name"],
        $_POST["duration_hours"],
        $_POST["speed_down"],
        $_POST["speed_up"],
        $_POST["price"]
    ]);

    header("Location: packages.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Package</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="main" style="margin-left:0; max-width:800px; margin:auto;">
    <div class="page-header">
        <div>
            <h1>Add Package</h1>
            <p>Create a new internet package.</p>
        </div>
        <a class="btn btn-secondary" href="packages.php">Back</a>
    </div>

    <div class="card">
        <form method="POST" class="form-grid">
            <div class="form-group">
                <label>Package Name</label>
                <input type="text" name="name" required>
            </div>

            <div class="form-group">
                <label>Duration Hours</label>
                <input type="number" step="0.01" name="duration_hours" required>
            </div>

            <div class="form-group">
                <label>Download Speed</label>
                <input type="text" name="speed_down" placeholder="8M" required>
            </div>

            <div class="form-group">
                <label>Upload Speed</label>
                <input type="text" name="speed_up" placeholder="2M" required>
            </div>

            <div class="form-group">
                <label>Price</label>
                <input type="number" name="price" required>
            </div>

            <div class="form-group full">
                <button class="btn" type="submit">Save Package</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
