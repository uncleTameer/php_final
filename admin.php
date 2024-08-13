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

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to generate a unique product ID
function generate_unique_product_id($conn) {
    $unique = false;
    $product_id = null;

    while (!$unique) {
        $product_id = rand(1, 1000000); // Generate a random number between 1 and 1,000,000
        $sql_check = "SELECT * FROM products WHERE product_id=?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("i", $product_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows == 0) {
            $unique = true;
        }

        $stmt_check->close();
    }

    return $product_id;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_product'])) {
        $product_id = generate_unique_product_id($conn);
        $product_code = $_POST['product_code'];
        $product_name = $_POST['product_name'];
        $price = $_POST['price'];
        $color = $_POST['color'];
        $weight = $_POST['weight'];
        $stock = $_POST['stock'];

        $sql_add = "INSERT INTO products (product_id, product_code, product_name, price, color, weight, stock) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_add = $conn->prepare($sql_add);
        $stmt_add->bind_param("issdsdi", $product_id, $product_code, $product_name, $price, $color, $weight, $stock);
        $stmt_add->execute();
        $stmt_add->close();
    } elseif (isset($_POST['update_product'])) {
        $product_id = $_POST['product_id'];
        $product_code = $_POST['product_code'];
        $product_name = $_POST['product_name'];
        $price = $_POST['price'];
        $color = $_POST['color'];
        $weight = $_POST['weight'];
        $stock = $_POST['stock'];

        $sql_update = "UPDATE products SET product_code=?, product_name=?, price=?, color=?, weight=?, stock=? WHERE product_id=?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ssdsdii", $product_code, $product_name, $price, $color, $weight, $stock, $product_id);
        $stmt_update->execute();
        $stmt_update->close();
    } elseif (isset($_POST['delete_product'])) {
        $product_id = $_POST['product_id'];

        $sql_delete = "DELETE FROM products WHERE product_id=?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $product_id);
        $stmt_delete->execute();
        $stmt_delete->close();
    }
}

$sql = "SELECT * FROM products";
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
    <nav>
        <a href="homePage.php">Home</a>
        <a href="admin.php">Admin</a>
        <a href="products.php">Products</a>
        <?php if ($_SESSION['role'] == 'admin'): ?>
            <a href="admin.php">Add Product</a>
        <?php endif; ?>
    </nav>
    <h1>Admin Page</h1>

    <h2>Add Product</h2>
    <form method="POST" action="">
        <label for="product_code">Product Code:</label>
        <input type="text" id="product_code" name="product_code" required>
        <label for="product_name">Product Name:</label>
        <input type="text" id="product_name" name="product_name" required>
        <label for="price">Price:</label>
        <input type="number" id="price" name="price" step="0.01" required>
        <label for="color">Color:</label>
        <input type="text" id="color" name="color">
        <label for="weight">Weight:</label>
        <input type="number" id="weight" name="weight" step="0.01">
        <label for="stock">Stock:</label>
        <input type="number" id="stock" name="stock" required>
        <button type="submit" name="add_product">Add Product</button>
    </form>

    <h2>Update Product</h2>
    <form method="POST" action="">
        <label for="product_id">Product ID:</label>
        <input type="number" id="product_id" name="product_id" required>
        <label for="product_code">Product Code:</label>
        <input type="text" id="product_code" name="product_code" required>
        <label for="product_name">Product Name:</label>
        <input type="text" id="product_name" name="product_name" required>
        <label for="price">Price:</label>
        <input type="number" id="price" name="price" step="0.01" required>
        <label for="color">Color:</label>
        <input type="text" id="color" name="color">
        <label for="weight">Weight:</label>
        <input type="number" id="weight" name="weight" step="0.01">
        <label for="stock">Stock:</label>
        <input type="number" id="stock" name="stock" required>
        <button type="submit" name="update_product">Update Product</button>
    </form>

    <h2>Delete Product</h2>
    <form method="POST" action="">
        <label for="product_id">Product ID:</label>
        <input type="number" id="product_id" name="product_id" required>
        <button type="submit" name="delete_product">Delete Product</button>
    </form>

    <h2>Product List</h2>
    <table>
        <tr>
            <th>Product ID</th>
            <th>Product Code</th>
            <th>Product Name</th>
            <th>Price</th>
            <th>Color</th>
            <th>Weight</th>
            <th>Stock</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['product_id']) ?></td>
                <td><?= htmlspecialchars($row['product_code']) ?></td>
                <td><?= htmlspecialchars($row['product_name']) ?></td>
                <td><?= htmlspecialchars($row['price']) ?></td>
                <td><?= htmlspecialchars($row['color']) ?></td>
                <td><?= htmlspecialchars($row['weight']) ?></td>
                <td><?= htmlspecialchars($row['stock']) ?></td>
                <td>
                    <form method="POST" action="">
                        <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
                        <button type="submit" name="delete_product">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
