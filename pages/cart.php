<?php
session_start();
include '../includes/db.php'; // Include the database connection

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle adding items to the cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];

    // Check if item already exists in cart
    $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_item) {
        // Update quantity if product exists in cart
        $new_quantity = $existing_item['quantity'] + 1;
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$new_quantity, $user_id, $product_id]);
    } else {
        // Insert new product into cart
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$user_id, $product_id]);
    }

    header("Location: cart.php");
    exit();
}

// Handle quantity update
if (isset($_POST['update_quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];

    if ($quantity > 0) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$quantity, $user_id, $product_id]);
    } else {
        // If quantity is zero or less, remove item from cart
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
    }
}

// Handle item removal
if (isset($_POST['remove_from_cart'])) {
    $product_id = $_POST['product_id'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
}

// Fetch cart items
$stmt = $conn->prepare("SELECT cart.product_id, products.name, products.price, cart.quantity
                        FROM cart
                        JOIN products ON cart.product_id = products.id
                        WHERE cart.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_cost = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Shopping Cart</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h2 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .total {
            text-align: right;
            font-size: 1.2em;
            margin-top: 20px;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        .actions form {
            display: inline;
        }
        .actions button {
            padding: 5px 10px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        .actions button.remove {
            background-color: #dc3545;
        }
        .actions button:hover {
            opacity: 0.8;
        }
        .continue-shopping {
            display: block;
            text-align: center;
            margin-top: 20px;
        }
        .continue-shopping a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            font-size: 1.1em;
            border-radius: 5px;
        }
        .continue-shopping a:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <h2>Your Shopping Cart</h2>
    <?php if (empty($cart_items)) : ?>
        <p>Your cart is empty.</p>
    <?php else : ?>
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item) : ?>
                    <?php
                    $item_total = $item['price'] * $item['quantity'];
                    $total_cost += $item_total;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']); ?></td>
                        <td>$<?= number_format($item['price'], 2); ?></td>
                        <td>
                            <form method="POST" action="cart.php">
                                <input type="hidden" name="product_id" value="<?= $item['product_id']; ?>">
                                <input type="number" name="quantity" value="<?= $item['quantity']; ?>" min="1">
                                <button type="submit" name="update_quantity">Update</button>
                            </form>
                        </td>
                        <td>$<?= number_format($item_total, 2); ?></td>
                        <td>
                            <form method="POST" action="cart.php">
                                <input type="hidden" name="product_id" value="<?= $item['product_id']; ?>">
                                <button type="submit" name="remove_from_cart" class="remove">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="total">
            <strong>Total Cost: $<?= number_format($total_cost, 2); ?></strong>
        </div>
    <?php endif; ?>

    <!-- Continue Shopping Button -->
    <div class="continue-shopping">
        <a href="../index.php">Continue Shopping</a>
    </div>
</body>
</html>
