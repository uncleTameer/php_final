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

$errors = [];
$success_message = "";
$total_price = 0;

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
        // Proceed with purchase and clear cart
        $sql_clear_cart = "DELETE FROM cart WHERE user_id=?";
        $stmt_clear_cart = $conn->prepare($sql_clear_cart);
        $stmt_clear_cart->bind_param("i", $user_id);
        $stmt_clear_cart->execute();
        $stmt_clear_cart->close();

        $success_message = "Purchase confirmed!";
    }
}

function remove_item_from_cart($conn, $user_id, $product_id, $quantity_to_remove) {
    // Fetch current quantity from cart
    $sql = "SELECT quantity FROM cart WHERE user_id=? AND product_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $stmt->bind_result($current_quantity);
    $stmt->fetch();
    $stmt->close();

    if ($quantity_to_remove >= $current_quantity) {
        // If removing equal or more than current quantity, delete the item
        $sql_delete = "DELETE FROM cart WHERE user_id=? AND product_id=?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("ii", $user_id, $product_id);
        $stmt_delete->execute();
        $stmt_delete->close();

        // Increase stock by the quantity removed
        $sql_update_stock = "UPDATE products SET stock=stock+? WHERE product_id=?";
        $stmt_update_stock = $conn->prepare($sql_update_stock);
        $stmt_update_stock->bind_param("ii", $current_quantity, $product_id);
        $stmt_update_stock->execute();
        $stmt_update_stock->close();
    } else {
        // Otherwise, decrease the quantity in the cart
        $new_quantity = $current_quantity - $quantity_to_remove;
        $sql_update_cart = "UPDATE cart SET quantity=? WHERE user_id=? AND product_id=?";
        $stmt_update_cart = $conn->prepare($sql_update_cart);
        $stmt_update_cart->bind_param("iii", $new_quantity, $user_id, $product_id);
        $stmt_update_cart->execute();
        $stmt_update_cart->close();

        // Increase stock by the quantity removed
        $sql_update_stock = "UPDATE products SET stock=stock+? WHERE product_id=?";
        $stmt_update_stock = $conn->prepare($sql_update_stock);
        $stmt_update_stock->bind_param("ii", $quantity_to_remove, $product_id);
        $stmt_update_stock->execute();
        $stmt_update_stock->close();
    }
}

$user_id = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['confirm_purchase'])) {
        check_inventory_and_confirm_purchase($conn, $user_id);
    } elseif (isset($_POST['remove_item'])) {
        $product_id = $_POST['product_id'];
        $quantity_to_remove = $_POST['quantity_to_remove'];
        remove_item_from_cart($conn, $user_id, $product_id, $quantity_to_remove);
    }
}

// Fetch cart items to display
$sql = "SELECT cart.product_id, products.product_name, cart.quantity, products.price FROM cart JOIN products ON cart.product_id = products.product_id WHERE cart.user_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
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
    <nav>
        <a href="homePage.php">Home</a>
        <a href="products.php">Products</a>
        <a href="cart.php">Cart</a>
    </nav>
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
            <th>Price</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php $total_price += $row['quantity'] * $row['price']; ?>
            <tr>
                <td><?= htmlspecialchars($row['product_id']) ?></td>
                <td><?= htmlspecialchars($row['product_name']) ?></td>
                <td><?= htmlspecialchars($row['quantity']) ?></td>
                <td><?= htmlspecialchars($row['price']) ?></td>
                <td>
                    <form method="POST" action="">
                        <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
                        <input type="number" name="quantity_to_remove" value="1" min="1" max="<?= $row['quantity'] ?>">
                        <button type="submit" name="remove_item">Remove</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        <?php $stmt->close(); ?>
    </table>
    <h2>Total Price: <?= $total_price ?></h2>

    <form method="POST" action="">
        <button type="submit" name="confirm_purchase">Confirm Purchase</button>
    </form>
</body>
</html>
<?php
$conn->close();
?>
