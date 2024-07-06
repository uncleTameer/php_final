<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "my_website";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM products ORDER BY price DESC";
$result = $conn->query($sql);

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
            margin-top: 20px;
        }
        button:hover {
            background: #555;
        }
    </style>
</head>
<body>
    <h1>Products</h1>
    <table>
        <tr>
            <th>Product Code</th>
            <th>Name</th>
            <th>Price</th>
            <th>Color</th>
            <th>Weight</th>
            <th>Image</th>
            <th>Action</th>
        </tr>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['product_code']) ?></td>
                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                    <td><?= htmlspecialchars($row['price']) ?></td>
                    <td><?= htmlspecialchars($row['color']) ?></td>
                    <td><?= htmlspecialchars($row['weight']) ?></td>
                    <td><img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['product_name']) ?>" width="50"></td>
                    <td><button onclick="addToCart(<?= $row['product_id'] ?>)">Add to Cart</button></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7">No products found</td>
            </tr>
        <?php endif; ?>
    </table>
    <button onclick="window.location.href='cart.php'">View Cart</button>

    <script>
        function addToCart(productId) {
            // Handle the add to cart action (you will implement this later)
            alert('Product ' + productId + ' added to cart');
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>
