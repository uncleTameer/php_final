<?php
session_start();

$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "my_storage";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$errors = [];
$success_message = "";

// Function to check inventory and confirm purchase
function check_inventory_and_confirm_purchase($conn, $user_id) {
    global $errors, $success_message;

    // Fetch cart items
    $sql = "SELECT * FROM cart WHERE user_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_items = [];

    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
    }

    $stmt->close();

    // Check inventory
    foreach ($cart_items as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];

        $sql = "SELECT stock FROM products WHERE product_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->bind_result($stock);
        $stmt->fetch();
        $stmt->close();

        if ($quantity > $stock) {
            $errors[] = "Not enough stock for product ID: $product_id. Available: $stock, In Cart: $quantity.";
            // Reset the cart item quantity to 0
            $sql = "UPDATE cart SET quantity=0 WHERE user_id=? AND product_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    if (empty($errors)) {
        // Proceed with purchase and update stock
        foreach ($cart_items as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];

            // Deduct stock
            $sql = "UPDATE products SET stock=stock-? WHERE product_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $quantity, $product_id);
            $stmt->execute();
            $stmt->close();
        }

        // Clear cart
        $sql = "DELETE FROM cart WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        $success_message = "Purchase confirmed!";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_purchase'])) {
    $user_id = $_SESSION['user_id'];
    check_inventory_and_confirm_purchase($conn, $user_id);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
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
        .error {
            color: red;
            margin-top: 10px;
        }
        .success {
            color: green;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Cart</h1>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?= $error ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <div class="success">
            <p><?= $success_message ?></p>
        </div>
    <?php endif; ?>

    <table>
        <tr>
            <th>Product ID</th>
            <th>Name</th>
            <th>Quantity</th>
        </tr>
        <?php
        $user_id = $_SESSION['user_id'];
        $sql = "SELECT cart.product_id, products.product_name, cart.quantity FROM cart JOIN products ON cart.product_id = products.product_id WHERE cart.user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['product_id']) ?></td>
                <td><?= htmlspecialchars($row['product_name']) ?></td>
                <td><?= htmlspecialchars($row['quantity']) ?></td>
            </tr>
        <?php endwhile; ?>
        <?php $stmt->close(); ?>
    </table>

    <form method="POST" action="">
        <button type="submit" name="confirm_purchase">Confirm Purchase</button>
    </form>
</body>
</html>
<?php
$conn->close();
?>
