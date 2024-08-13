<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #f0f0f0;
        }
        header {
            background-color: #333;
            color: #fff;
            padding: 1em;
            width: 100%;
            text-align: center;
        }
        main {
            padding: 2em;
            text-align: center;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        button {
            padding: 10px 20px;
            background: #333;
            color: #fff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin: 10px;
        }
        button:hover {
            background: #555;
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome, <?= htmlspecialchars($username) ?></h1>
    </header>
    <main>
        <p>This is the home page for the project.</p>
        <p>User Role: <?= htmlspecialchars($role) ?></p>
        <button onclick="window.location.href='products.php'">View Products</button>
        <?php if ($role == 'admin'): ?>
            <button onclick="window.location.href='admin.php'">Admin Page</button>
            <button onclick="window.location.href='admin.php'">Add Product</button>
        <?php endif; ?>
        <button onclick="window.location.href='index.php?logout=true'">Logout</button>
    </main>
    <?php
    if (isset($_GET['logout'])) {
        session_destroy();
        header('Location: index.php');
        exit();
    }
    ?>
</body>
</html>
