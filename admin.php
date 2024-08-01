<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit();
}

$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "my_storage";

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $product_code = $_POST['product_code'];
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $color = $_POST['color'];
    $weight = $_POST['weight'];
    $stock = $_POST['stock'];

    $sql = "INSERT INTO products (product_code, product_name, price, color, weight, stock) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("ssdssi", $product_code, $product_name, $price, $color, $weight, $stock);

    if ($stmt->execute()) {
        $success_message = "Product added successfully!";
    } else {
        $error_message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #f0f0f0;
            padding: 20px;
        }
        nav {
            width: 100%;
            background-color: #333;
            overflow: hidden;
        }
        nav a {
            float: left;
            display: block;
            color: #f2f2f2;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        }
        nav a:hover {
            background-color: #ddd;
            color: black;
        }
        .container {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            margin-bottom: 20px;
        }
        input, button {
            display: block;
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        button {
            background: #333;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: #555;
        }
        .message {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <nav>
        <a href="homePage.php">Home</a>
        <a href="products.php">Products</a>
        <a href="cart.php">Cart</a>
        <a href="admin.php">Add Products</a>
        <form method="POST" action="logout.php" style="float: right;">
            <button type="submit" style="background: #333; color: #fff; border: none; cursor: pointer; padding: 14px 16px;">Logout</button>
        </form>
    </nav>

    <h1>Admin Page</h1>

    <?php if (isset($success_message)): ?>
        <div class="message" style="color: green;">
            <?= $success_message ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="message" style="color: red;">
            <?= $error_message ?>
        </div>
    <?php endif; ?>

    <div class="container">
        <form method="POST" action="">
            <input type="text" name="product_code" placeholder="Product Code" required>
            <input type="text" name="product_name" placeholder="Product Name" required>
            <input type="text" name="price" placeholder="Price" required>
            <input type="text" name="color" placeholder="Color" required>
            <input type="text" name="weight" placeholder="Weight" required>
            <input type="number" name="stock" placeholder="Stock" required>
            <button type="submit" name="add_product">Add Product</button>
        </form>
    </div>
</body>
</html>
