<?php
session_start();
include 'includes/db.php'; // Include the database connection

// Fetch products from the database
$stmt = $conn->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Store</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <div class="header-container">
            <h1>Welcome to Our Store</h1>
            <nav>
                <a href="pages/login.php">Login</a>
                <a href="pages/register.php">Register</a>
                <a href="pages/cart.php" class="cart-link">
                    <img src="images/cart-icon.png" alt="Cart" class="cart-icon">
                    Cart
                </a>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="logout" class="logout-button">Logout</button>
                </form>
            </nav>
        </div>
    </header>
    <div class="main-container">
        <main>
            <h2>Products</h2>
            <div class="product-list">
                <!-- PHP to display products will go here -->
                <?php if (empty($products)) : ?>
    <p>No products available.</p>
<?php else : ?>
    <?php foreach ($products as $product) : ?>
        <div class="product">
            <h3><?= htmlspecialchars($product['name']); ?></h3>
            <p>Price: $<?= number_format($product['price'], 2); ?></p>
            <p><?= htmlspecialchars($product['description']); ?></p>
            <?php if (!empty($product['image'])) : ?>
                <img src="/ecommerce1/images/<?= htmlspecialchars($product['image']); ?>" alt="<?= htmlspecialchars($product['name']); ?>" class="product-image">
                <?php endif; ?>
            <form method="POST" action="pages/cart.php">
                <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                <button type="submit" name="add_to_cart" class="add-to-cart-button">Add to Cart</button>
            </form>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
            </div>
        </main>
    </div>
    <footer>
        <p>&copy; <?= date('Y'); ?> Online Store. All rights reserved.</p>
    </footer>
</body>
</html>