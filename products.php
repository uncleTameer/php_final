<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "my_storage";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $user_id = $_SESSION['user_id'];

    // Check the stock of the product
    $sql_stock = "SELECT stock FROM products WHERE product_id=?";
    $stmt_stock = $conn->prepare($sql_stock);
    $stmt_stock->bind_param("i", $product_id);
    $stmt_stock->execute();
    $stmt_stock->bind_result($stock);
    $stmt_stock->fetch();
    $stmt_stock->close();

    if ($quantity > $stock) {
        $error_message = 'Not enough stock available.';
    } else {
        // Check if the item is already in the cart
        $sql_check = "SELECT * FROM cart WHERE user_id=? AND product_id=?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("ii", $user_id, $product_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            // Update quantity if the item is already in the cart
            $sql_update = "UPDATE cart SET quantity=quantity+? WHERE user_id=? AND product_id=?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("iii", $quantity, $user_id, $product_id);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            // Insert new item into the cart
            $sql_insert = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("iii", $user_id, $product_id, $quantity);
            $stmt_insert->execute();
            $stmt_insert->close();
        }

        $stmt_check->close();

        // Decrease stock immediately to reserve the product
        $sql_update_stock = "UPDATE products SET stock=stock-? WHERE product_id=?";
        $stmt_update_stock = $conn->prepare($sql_update_stock);
        $stmt_update_stock->bind_param("ii", $quantity, $product_id);
        $stmt_update_stock->execute();
        $stmt_update_stock->close();
    }

    if (isset($error_message)) {
        echo $error_message;
    } else {
        header('Location: products.php');
        exit();
    }
}

$sql = "SELECT * FROM products ORDER BY price DESC";
$result = $conn->query($sql);

if ($result === false) {
    die("Error: " . $conn->error);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
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
        table {
            border-collapse: collapse;
            width: 80%;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #333;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        button {
            padding: 10px 20px;
            background: #333;
            color: #fff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        button:hover {
            background: #555;
        }
    </style>
</head>
<body>
    <nav>
        <a href="homePage.php">Home</a>
        <a href="products.php">Products</a>
        <a href="cart.php">Cart</a>
    </nav>
    <h1>Products</h1>
    <table>
        <tr>
            <th>Product Code</th>
            <th>Name</th>
            <th>Price</th>
            <th>Color</th>
            <th>Weight</th>
            <th>Stock</th>
            <th>Action</th>
        </tr>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['product_code']) ?></td>
                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                    <td><?= htmlspecialchars($row['price']) ?></td>
                    <td><?= htmlspecialchars($row['color']) ?></td>
                    <td><?= htmlspecialchars($row['weight']) ?></td>
                    <td><?= htmlspecialchars($row['stock']) ?></td>
                    <td>
                        <form method="POST" action="">
                            <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
                            <input type="number" name="quantity" value="1" min="1" max="<?= $row['stock'] ?>">
                            <button type="submit">Add to Cart</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7">No products available.</td>
            </tr>
        <?php endif; ?>
    </table>
    <form action="cart.php" method="GET">
        <button type="submit">Go to Cart</button>
    </form>
</body>
</html>
