<?php
require_once('includes/customer-auth.php');

// Require customer login
require_customer_login('cart.php');
?>
<!doctype html>

<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Shopping Cart - Nutri Afghan</title>

    <meta name="author" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />

    <?php
    include_once('includes/header-link.php');
    ?>
</head>

<body class="preload-wrapper popup-loader">
    <!-- Scroll Top -->
    <?php
    include_once('includes/scroll-top.php');
    ?>
    <!-- /Scroll Top -->

    <!-- preload -->
    <?php
    include_once('includes/preloader.php');
    ?>
    <!-- /preload -->

    <div id="wrapper">
        <!-- Header -->
        <?php
        include_once('includes/header.php');
        ?>
        <!-- /Header -->

        <!-- page-title -->
        <div class="page-title" style="background-image: url(images/breadcrumb_banner.jpg);">
            <div class="container">
                <h3 class="heading text-center">Shopping Cart</h3>
                <ul class="breadcrumbs d-flex align-items-center justify-content-center">
                    <li><a class="link" href="./">Home</a></li>
                    <li><i class="icon-arrRight"></i></li>
                    <li>Shopping Cart</li>
                </ul>
            </div>
        </div>
        <!-- /page-title -->

        <!-- Section cart -->
        <section class="flat-spacing">
            <div class="container">
                <div class="row">
                    <div class="col-xl-8">
                        <form id="cart-form">
                            <table class="tf-table-page-cart">
                                <thead>
                                    <tr>
                                        <th>Products</th>
                                        <th>Quantity</th>
                                        <th>Total Price</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="cart-items">
                                    <!-- Cart items will be loaded here -->
                                </tbody>
                            </table>
                            <div id="empty-cart" style="display: none; text-align: center; padding: 50px;">
                                <h3>Your cart is empty</h3>
                                <p>Add some products to your cart to continue shopping.</p>
                                <a href="collection.php" class="tf-button style-1">Continue Shopping</a>
                            </div>
                        </form>
                            <div class="ip-discount-code">
                                <input type="text" placeholder="Add voucher discount">
                                <button class="tf-btn"><span class="text">Apply Code</span></button>
                            </div>
                            <div class="group-discount">
                                <div class="box-discount">
                                    <div class="discount-top">
                                        <div class="discount-off">
                                            <div class="text-caption-1">Discount</div>
                                            <span class="sale-off text-btn-uppercase">10% OFF</span>
                                        </div>
                                        <div class="discount-from">
                                            <p class="text-caption-1">For all orders <br> from ₹1500.00</p>
                                        </div>
                                    </div>
                                    <div class="discount-bot">
                                        <span class="text-btn-uppercase">Mo234231</span>
                                        <button class="tf-btn"><span class="text">Apply Code</span></button>
                                    </div>
                                </div>
                                <div class="box-discount active">
                                    <div class="discount-top">
                                        <div class="discount-off">
                                            <div class="text-caption-1">Discount</div>
                                            <span class="sale-off text-btn-uppercase">15% OFF</span>
                                        </div>
                                        <div class="discount-from">
                                            <p class="text-caption-1">For all orders <br> from ₹3000.00</p>
                                        </div>
                                    </div>
                                    <div class="discount-bot">
                                        <span class="text-btn-uppercase">Mo234231</span>
                                        <button class="tf-btn"><span class="text">Apply Code</span></button>
                                    </div>
                                </div>
                                <div class="box-discount">
                                    <div class="discount-top">
                                        <div class="discount-off">
                                            <div class="text-caption-1">Discount</div>
                                            <span class="sale-off text-btn-uppercase">20% OFF</span>
                                        </div>
                                        <div class="discount-from">
                                            <p class="text-caption-1">For all orders <br> from ₹5000.00</p>
                                        </div>
                                    </div>
                                    <div class="discount-bot">
                                        <span class="text-btn-uppercase">Mo234231</span>
                                        <button class="tf-btn"><span class="text">Apply Code</span></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-xl-4">
                        <div class="fl-sidebar-cart">
                            <div class="box-order bg-surface">
                                <h5 class="title">Order Summary</h5>
                                <div class="order-summary-items" id="order-summary-items">
                                    <!-- Order summary items will be populated here -->
                                </div>
                                <div class="line-bt"></div>
                                <h5 class="total-order d-flex justify-content-between align-items-center">
                                    <span>Total</span>
                                    <span class="total" id="cart-total">Rs. 0.00</span>
                                </h5>
                                <div class="box-progress-checkout">
                                    <fieldset class="check-agree">
                                        <label for="check-agree">
                                            Taxes included. Discounts and <a href="#">shipping</a> calculated at checkout.
                                        </label>
                                    </fieldset>
                                    <a href="checkout.php" class="tf-btn btn-reset" id="checkout-btn">Process To Checkout</a>
                                    <p class="text-button text-center">Or continue shopping</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Section cart -->

        <!-- Footer -->
        <?php
        include_once('includes/footer.php');
        ?>
        <!-- /Footer -->

        <!-- toolbar-bottom -->
        <?php
        include_once('includes/bottom-toolbar.php');
        ?>
        <!-- /toolbar-bottom -->
    </div>

    <!-- auto popup  -->
    <?php
    include_once('includes/auto-popup.php');
    ?>
    <!-- /auto popup  -->

    <!-- search -->
    <?php
    include_once('includes/search-bar.php');
    ?>
    <!-- /search -->

    <!-- mobile menu -->
    <?php
    include_once('includes/mobile-menu.php');
    ?>
    <!-- /mobile menu -->

    <!-- quickView -->
    <?php
    include_once('includes/quick-view.php');
    ?>
    <!-- quickView -->

    <!-- Shopping Cart -->
    <?php
    include_once('includes/shopping-cart.php')
    ?>
    <!-- /Shopping Cart -->

    <!-- wishlist -->
    <?php
    include_once('includes/wishlist-bar.php');
    ?>
    <!-- /wishlist -->

    <?php
    include_once('includes/footer-link.php');
    ?>

    <script>
        // Cart management functions
        function loadCart() {
            fetch('cart-process.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayCartItems(data.cart_items);
                        updateCartSummary(data.total_amount);
                    } else {
                        console.error('Failed to load cart:', data.message);
                        // Show empty cart state
                        displayCartItems([]);
                        updateCartSummary(0);
                    }
                })
                .catch(error => {
                    console.error('Error loading cart:', error);
                    // Show empty cart state on error
                    displayCartItems([]);
                    updateCartSummary(0);
                });
        }

        function displayCartItems(items) {
            const cartItemsContainer = document.getElementById('cart-items');
            const emptyCartDiv = document.getElementById('empty-cart');

            if (items.length === 0) {
                cartItemsContainer.innerHTML = '';
                emptyCartDiv.style.display = 'block';
                updateOrderSummary([]);
                return;
            }

            emptyCartDiv.style.display = 'none';

            const itemsHtml = items.map(item => `
                <tr class="tf-cart-item file-delete" data-cart-item-id="${item.id}">
                    <td class="tf-cart-item_product">
                        <a href="product-detail.php?slug=${encodeURIComponent(item.product_slug)}" class="img-box">
                            <img src="${item.main_image ? 'uploads/' + item.main_image : 'images/pista_demp.jpg'}" alt="${item.product_name}">
                        </a>
                        <div class="cart-info">
                            <a href="product-detail.php?slug=${encodeURIComponent(item.product_slug)}" class="cart-title link">${item.product_name}</a>
                            ${item.weight_label ? `<div class="variant-box">${item.weight_label}</div>` : ''}
                        </div>
                    </td>
                    <td data-cart-title="Quantity" class="tf-cart-item_quantity">
                        <div class="wg-quantity mx-md-auto">
                            <span class="btn-quantity btn-decrease" onclick="updateQuantity(${item.id}, ${item.quantity - 1})">-</span>
                            <input type="text" class="quantity-product" name="number" value="${item.quantity}" onchange="updateQuantity(${item.id}, this.value)">
                            <span class="btn-quantity btn-increase" onclick="updateQuantity(${item.id}, ${item.quantity + 1})">+</span>
                        </div>
                    </td>
                    <td data-cart-title="Total" class="tf-cart-item_total text-center">
                        <div class="cart-total text-button total-price">Rs. ${item.total.toFixed(2)}</div>
                    </td>
                    <td data-cart-title="Remove" class="remove-cart">
                        <span class="remove icon icon-close" onclick="removeItem(${item.id})"></span>
                    </td>
                </tr>
            `).join('');

            cartItemsContainer.innerHTML = itemsHtml;
            updateOrderSummary(items);
        }

        function updateOrderSummary(items) {
            const orderSummaryContainer = document.getElementById('order-summary-items');
            if (!orderSummaryContainer) return;

            if (items.length === 0) {
                orderSummaryContainer.innerHTML = '<p class="text-center text-caption-1">Your cart is empty</p>';
                return;
            }

            const summaryHtml = items.map(item => `
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <div class="flex-grow-1">
                        <div class="text-caption-1 fw-6">${item.product_name}</div>
                        ${item.weight_label ? `<div class="text-caption-2">${item.weight_label}</div>` : ''}
                        <div class="text-caption-2">Qty: ${item.quantity}</div>
                    </div>
                    <span class="text-caption-1 fw-6">Rs. ${item.total.toFixed(2)}</span>
                </div>
            `).join('');

            orderSummaryContainer.innerHTML = summaryHtml;
        }

        function updateCartSummary(totalAmount) {
            // Update total in the sidebar
            const totalElement = document.getElementById('cart-total');
            if (totalElement) {
                totalElement.textContent = 'Rs. ' + totalAmount.toFixed(2);
            }

            // Update checkout button
            const checkoutBtn = document.getElementById('checkout-btn');
            if (checkoutBtn) {
                if (totalAmount > 0) {
                    checkoutBtn.style.display = 'block';
                } else {
                    checkoutBtn.style.display = 'none';
                }
            }
        }

        function updateQuantity(cartItemId, newQuantity) {
            newQuantity = parseInt(newQuantity);
            if (newQuantity < 0) return;

            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('cart_item_id', cartItemId);
            formData.append('quantity', newQuantity);

            fetch('cart-process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCart(); // Reload cart to show updated data
                } else {
                    alert('Error: ' + (data.message || 'Failed to update cart'));
                    loadCart(); // Reload to revert changes
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the cart');
                loadCart();
            });
        }

        function removeItem(cartItemId) {
            if (!confirm('Are you sure you want to remove this item from your cart?')) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'remove');
            formData.append('cart_item_id', cartItemId);

            fetch('cart-process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCart(); // Reload cart
                } else {
                    alert('Error: ' + (data.message || 'Failed to remove item'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while removing the item');
            });
        }

        // Load cart when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadCart();
        });
    </script>
</body>

</html>