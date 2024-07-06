<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_product'])) {
        $product_code = $_POST['product_code'];
        $product_name = $_POST['product_name'];
        $price = $_POST['price'];
        $color = $_POST['color'];
        $weight = $_POST['weight'];
        $image = $_POST['image'];
        $stock = $_POST['stock'];

        $sql = "INSERT INTO products (product_code, product_name, price, color, weight, image, stock) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdsssi", $product_code, $product_name, $price, $color, $weight, $image, $stock);

        if ($stmt->execute()) {
            $success_message = "Product added successfully!";
        } else {
            $error_message = "Error adding product: " . $stmt->error;
        }

        $stmt->close();
    } elseif (isset($_POST['delete_product'])) {
        $product_id = $_POST['product_id'];

        $sql = "DELETE FROM products WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);

        if ($stmt->execute()) {
            $success_message = "Product deleted successfully!";
        } else {
            $error_message = "Error deleting product: " . $stmt->error;
        }

        $stmt->close();
    }
}

$sql = "SELECT * FROM products";
$result = $conn->query($sql);

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
        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        input {
            display: block;
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <h1>Admin Page</h1>

    <?php if (isset($success_message)): ?>
        <p style="color: green;"><?= $success_message ?></p>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        <p style="color: red;"><?= $error_message ?></p>
    <?php endif; ?>

    <div class="form-container">
        <h2>Add New Product</h2>
        <form method="POST" action="">
            <input type="text" name="product_code" placeholder="Product Code" required>
            <input type="text" name="product_name" placeholder="Product Name" required>
            <input type="number" step="0.01" name="price" placeholder="Price" required>
            <input type="text" name="color" placeholder="Color" required>
            <input type="number" step="0.01" name="weight" placeholder="Weight" required>
            <input type="text" name="image" placeholder="Image URL" required>
            <input type="number" name="stock" placeholder="Stock" required>
            <button type="submit" name="add_product">Add Product</button>
        </form>
    </div>

    <table>
        <tr>
            <th>Product ID</th>
            <th>Product Code</th>
            <th>Name</th>
            <th>Price</th>
            <th>Color</th>
            <th>Weight</th>
            <th>Image</th>
            <th>Stock</th>
            <th>Action</th>
        </tr>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['product_id']) ?></td>
                    <td><?= htmlspecialchars($row['product_code']) ?></td>
                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                    <td><?= htmlspecialchars($row['price']) ?></td>
                    <td><?= htmlspecialchars($row['color']) ?></td>
                    <td><?= htmlspecialchars($row['weight']) ?></td>
                    <td><img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['product_name']) ?>" width="50"></td>
                    <td><?= htmlspecialchars($row['stock']) ?></td>
                    <td>
                        <form method="POST" action="" style="display:inline;">
                            <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
                            <button type="submit" name="delete_product">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="9">No products found</td>
            </tr>
        <?php endif; ?>
    </table>

</body>
</html>
<?php
$conn->close();
?>
