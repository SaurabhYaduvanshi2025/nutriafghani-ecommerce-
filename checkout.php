<?php
require_once('includes/customer-auth.php');

// Require customer login
require_customer_login('checkout.php');
?>
<!doctype html>

<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Checkout - Nutri Afghan</title>

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
            <div class="container-full">
                <div class="row">
                    <div class="col-12">
                        <h3 class="heading text-center">Checkout</h3>
                        <ul class="breadcrumbs d-flex align-items-center justify-content-center">
                            <li>
                                <a class="link" href="./">Home</a>
                            </li>
                            <li>
                                <i class="icon-arrRight"></i>
                            </li>
                            <li>
                                Checkout
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- /page-title -->

        <!-- Section checkout -->
        <section>
            <div class="container">
                <div class="row">
                    <div class="col-xl-6">
                        <div class="flat-spacing tf-page-checkout">
                            <div class="wrap">
                                <div class="title-login">
                                    <p>Already have an account?</p>
                                    <a href="#" class="text-button">Login here</a>
                                </div>
                                <form class="login-box">
                                    <div class="grid-2">
                                        <input type="text" placeholder="Your name/Email">
                                        <input type="password" placeholder="Password">
                                    </div>
                                    <button class="tf-btn" type="submit"><span class="text">Login</span></button>
                                </form>
                            </div>
                            <div class="wrap">
                                <h5 class="title">Information</h5>
                                <form class="info-box" id="checkout-form">
                                    <div class="grid-2">
                                        <input type="text" id="first-name" name="first_name" placeholder="First Name*" required>
                                        <input type="text" id="last-name" name="last_name" placeholder="Last Name*" required>
                                    </div>
                                    <div class="grid-2">
                                        <input type="email" id="email" name="email" placeholder="Email Address*" required>
                                        <input type="tel" id="phone" name="phone" placeholder="Phone Number*" required>
                                    </div>
                                    <input type='hidden' name='country' value=''>
                                    <div class="grid-2">
                                        <input type="text" name="city" placeholder="Town/City*" required>
                                        <input type="text" name="address" placeholder="Street,..." required>
                                    </div>
                                    <div class="grid-2">
                                        <div class="tf-select">
                                            <select class="text-title" name="state" data-default="">
                                                <option selected value="Choose State">Choose State</option>
                                                <option value="Andhra Pradesh">Andhra Pradesh</option>
                                                <option value="Arunachal Pradesh">Arunachal Pradesh</option>
                                                <option value="Assam">Assam</option>
                                                <option value="Bihar">Bihar</option>
                                                <option value="Chhattisgarh">Chhattisgarh</option>
                                                <option value="Goa">Goa</option>
                                                <option value="Gujarat">Gujarat</option>
                                                <option value="Haryana">Haryana</option>
                                                <option value="Himachal Pradesh">Himachal Pradesh</option>
                                                <option value="Jharkhand">Jharkhand</option>
                                                <option value="Karnataka">Karnataka</option>
                                                <option value="Kerala">Kerala</option>
                                                <option value="Madhya Pradesh">Madhya Pradesh</option>
                                                <option value="Maharashtra">Maharashtra</option>
                                                <option value="Manipur">Manipur</option>
                                                <option value="Meghalaya">Meghalaya</option>
                                                <option value="Mizoram">Mizoram</option>
                                                <option value="Nagaland">Nagaland</option>
                                                <option value="Odisha">Odisha</option>
                                                <option value="Punjab">Punjab</option>
                                                <option value="Rajasthan">Rajasthan</option>
                                                <option value="Sikkim">Sikkim</option>
                                                <option value="Tamil Nadu">Tamil Nadu</option>
                                                <option value="Telangana">Telangana</option>
                                                <option value="Tripura">Tripura</option>
                                                <option value="Uttar Pradesh">Uttar Pradesh</option>
                                                <option value="Uttarakhand">Uttarakhand</option>
                                                <option value="West Bengal">West Bengal</option>
                                                <option value="Andaman and Nicobar Islands">Andaman and Nicobar Islands</option>
                                                <option value="Chandigarh">Chandigarh</option>
                                                <option value="Dadra and Nagar Haveli and Daman and Diu">Dadra and Nagar Haveli and Daman and Diu</option>
                                                <option value="Delhi">Delhi</option>
                                                <option value="Jammu and Kashmir">Jammu and Kashmir</option>
                                                <option value="Ladakh">Ladakh</option>
                                                <option value="Lakshadweep">Lakshadweep</option>
                                                <option value="Puducherry">Puducherry</option>
                                            </select>
                                        </div>
                                        <input type="text" name="postal_code" placeholder="Postal Code*">
                                    </div>
                                    <textarea placeholder="Write note..."></textarea>
                                </form>
                            </div>
                            <div class="wrap">
                                <h5 class="title">Choose payment Option:</h5>
                                <form class="form-payment" id="payment-form">
                                    <div class="payment-box" id="payment-box">
                                        <!-- UPI / Card Payment via Razorpay -->
                                        <div class="payment-item payment-choose-card active">
                                            <label for="upi-method" class="payment-header" data-bs-toggle="collapse" data-bs-target="#upi-payment" aria-controls="upi-payment">
                                                <input type="radio" name="payment-method" class="tf-check-rounded" id="upi-method" value="upi" checked>
                                                <span class="text-title">UPI / Card Payment (Razorpay)</span>
                                            </label>
                                            <div id="upi-payment" class="collapse show" data-bs-parent="#payment-box">
                                                <div class="payment-body">
                                                    <p class="text-secondary">Pay securely using UPI, Debit/Credit Card, or Net Banking via Razorpay</p>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Cash on Delivery -->
                                        <div class="payment-item">
                                            <label for="cod-method" class="payment-header collapsed" data-bs-toggle="collapse" data-bs-target="#cod-payment" aria-controls="cod-payment">
                                                <input type="radio" name="payment-method" class="tf-check-rounded" id="cod-method" value="cod">
                                                <span class="text-title">Cash on Delivery</span>
                                            </label>
                                            <div id="cod-payment" class="collapse" data-bs-parent="#payment-box">
                                                <div class="payment-body">
                                                    <p class="text-secondary">Pay when you receive your order at your doorstep.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="tf-btn btn-reset" id="btn-payment" onclick="processPayment()">Proceed to Payment</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-1">
                        <div class="line-separation"></div>
                    </div>
                    <div class="col-xl-5">
                        <div class="flat-spacing flat-sidebar-checkout">
                            <div class="sidebar-checkout-content">
                                <h5 class="title">Shopping Cart</h5>
                                <div class="list-product" id="checkout-cart-items">
                                    <!-- Cart items will load here -->
                                </div>
                                <div class="sec-total-price">
                                    <div class="top">
                                        <div class="item d-flex align-items-center justify-content-between text-button">
                                            <span>Shipping</span>
                                            <span>Free</span>
                                        </div>
                                        <div class="item d-flex align-items-center justify-content-between text-button">
                                            <span>Discounts</span>
                                            <span>-₹200.00</span>
                                        </div>
                                    </div>
                                    <div class="bottom">
                                        <h5 class="d-flex justify-content-between">
                                            <span>Estimated total</span>
                                            <span class="total-price-checkout" id="checkout-total-price">Rs. 0.00</span>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Section checkout -->

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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadCheckoutCart();
        });

        function loadCheckoutCart() {
            console.log('Starting loadCheckoutCart...');
            fetch('cart-process.php', {
                method: 'GET',
                credentials: 'same-origin'
            })
                .then(response => {
                    console.log('Response received, status:', response.status);
                    return response.text();
                })
                .then(text => {
                    console.log('Response text:', text);
                    try {
                        const data = JSON.parse(text);
                        console.log('Parsed data:', data);
                        if (data.success) {
                            console.log('Success! Cart items count:', data.cart_items.length);
                            displayCheckoutCartItems(data.cart_items);
                            updateCheckoutTotal(data.total_amount);
                        } else {
                            console.error('Failed to load cart:', data.message);
                            alert('Error: ' + data.message);
                            displayCheckoutCartItems([]);
                        }
                    } catch (e) {
                        console.error('Failed to parse JSON:', e);
                        console.error('Response was:', text);
                        alert('Error parsing response: ' + e.message);
                    }
                })
                .catch(error => {
                    console.error('Network error loading cart:', error);
                    alert('Network error: ' + error.message);
                    displayCheckoutCartItems([]);
                });
        }

        function displayCheckoutCartItems(items) {
            console.log('displayCheckoutCartItems called with:', items);
            const cartContainer = document.getElementById('checkout-cart-items');
            console.log('Cart container found:', !!cartContainer);
            if (!cartContainer) {
                console.error('Cart container not found!');
                return;
            }

            if (items.length === 0) {
                console.log('No items to display');
                cartContainer.innerHTML = '<p class="text-center text-muted py-4">Your cart is empty</p>';
                return;
            }
            console.log('Displaying', items.length, 'items');

            const itemsHtml = items.map(item => {
                const price = parseFloat(item.price);
                const quantity = parseInt(item.quantity);
                const itemTotal = parseFloat(item.total);
                return `
                <div class="item-product">
                    <a href="product-detail.php?slug=${encodeURIComponent(item.product_slug)}" class="img-product">
                        <img src="${item.main_image ? 'uploads/' + item.main_image : 'images/pista_demp.jpg'}" alt="${item.product_name}">
                    </a>
                    <div class="content-box">
                        <div class="info">
                            <a href="product-detail.php?slug=${encodeURIComponent(item.product_slug)}" class="name-product link text-title">${item.product_name}</a>
                            ${item.weight_label ? `<div class="variant text-caption-1 text-secondary"><span class="size">${item.weight_label}</span></div>` : ''}
                        </div>
                        <div class="total-price text-button">
                            <span class="count">${quantity}</span>X<span class="price">Rs. ${price.toFixed(2)}</span>
                        </div>
                    </div>
                </div>
            `;
            }).join('');

            cartContainer.innerHTML = itemsHtml;
            console.log('Items rendered successfully');
        }

        function updateCheckoutTotal(totalAmount) {
            console.log('Updating total to:', totalAmount);
            const total = parseFloat(totalAmount);
            const totalElement = document.getElementById('checkout-total-price');
            if (totalElement) {
                totalElement.textContent = 'Rs. ' + total.toFixed(2);
                console.log('Total element updated to:', total.toFixed(2));
            } else {
                console.error('Total element not found!');
            }
        }

        function processPayment() {
            const form = document.getElementById('checkout-form');
            if (!form) {
                alert('Form not found');
                return;
            }

            // Validate form
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Get form data
            const formData = new FormData(form);
            const paymentMethod = document.querySelector('input[name="payment-method"]:checked')?.value;

            if (!paymentMethod) {
                alert('Please select a payment method');
                return;
            }

            // Get cart total
            const totalElement = document.getElementById('checkout-total-price');
            const totalText = totalElement?.textContent || 'Rs. 0.00';
            const totalAmount = parseFloat(totalText.replace('Rs. ', '')) * 100; // Convert to paise for Razorpay

            const paymentData = new FormData();
            paymentData.append('action', 'create_order');
            paymentData.append('payment_method', paymentMethod);
            paymentData.append('first_name', formData.get('first_name'));
            paymentData.append('last_name', formData.get('last_name'));
            paymentData.append('email', formData.get('email'));
            paymentData.append('phone', formData.get('phone'));
            paymentData.append('address', formData.get('address'));
            paymentData.append('city', formData.get('city'));
            paymentData.append('state', formData.get('state'));
            paymentData.append('postal_code', formData.get('postal_code'));
            paymentData.append('country', formData.get('country'));

            // Show loading
            const btn = document.getElementById('btn-payment');
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Processing...';

            // Create order first
            fetch('payment-process.php', {
                method: 'POST',
                body: paymentData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to create order');
                }

                const orderId = data.order_id;
                if (paymentMethod === 'cod') {
                    // Handle Cash on Delivery
                    handleCODPayment(orderId, data);
                } else if (paymentMethod === 'upi') {
                    // Handle UPI via Razorpay
                    handleRazorpayPayment(orderId, data.razorpay_order_id || '', data.customer_email, data.razorpay_amount);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error: ' + error.message);
                btn.disabled = false;
                btn.textContent = originalText;
            });
        }

        function handleCODPayment(orderId, orderData) {
            const paymentData = new FormData();
            paymentData.append('action', 'cod_payment');
            paymentData.append('order_id', orderId);

            fetch('payment-process.php', {
                method: 'POST',
                body: paymentData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Order confirmed! Your order will be delivered COD.');
                    // Refresh cart count
                    if (window.refreshCartCount) {
                        window.refreshCartCount();
                    }
                    window.location.href = 'order-success.php?order_id=' + orderId;
                } else {
                    throw new Error(data.message || 'Failed to confirm order');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error: ' + error.message);
            });
        }

        function handleRazorpayPayment(orderId, razorpayOrderId, email, amount) {
            // Load Razorpay script
            const script = document.createElement('script');
            script.src = 'https://checkout.razorpay.com/v1/checkout.js';
            script.async = true;
            script.onload = function() {
                initiateRazorpayCheckout(orderId, razorpayOrderId, email, amount);
            };
            script.onerror = function() {
                alert('Unable to load Razorpay checkout. Please check your internet connection and try again.');
                document.getElementById('btn-payment').disabled = false;
                document.getElementById('btn-payment').textContent = 'Proceed to Payment';
            };
            document.body.appendChild(script);
        }

        function initiateRazorpayCheckout(orderId, razorpayOrderId, email, amountInPaise) {
            // Get customer info
            const firstName = document.getElementById('first-name').value;
            const phone = document.getElementById('phone').value;
            const amount = parseInt(amountInPaise, 10);

            if (!amount || amount <= 0) {
                alert('Invalid payment amount');
                document.getElementById('btn-payment').disabled = false;
                document.getElementById('btn-payment').textContent = 'Proceed to Payment';
                return;
            }

            const options = {
                key: 'rzp_test_1DP5MMOk9ganti', // Replace with your Razorpay key
                amount: amount,
                currency: 'INR',
                name: 'Nutri Afghan',
                description: 'Order Payment',
                prefill: {
                    name: firstName,
                    email: email,
                    contact: phone
                },
                handler: function(response) {
                    verifyRazorpayPayment(orderId, response);
                },
                modal: {
                    ondismiss: function() {
                        alert('Payment cancelled');
                        document.getElementById('btn-payment').disabled = false;
                        document.getElementById('btn-payment').textContent = 'Proceed to Payment';
                    }
                }
            };

            if (razorpayOrderId) {
                options.order_id = razorpayOrderId;
            }

            const rzp = new Razorpay(options);
            rzp.on('payment.failed', function(response) {
                const message = response?.error?.description || response?.error?.reason || 'Payment failed';
                alert('Payment failed: ' + message);
                document.getElementById('btn-payment').disabled = false;
                document.getElementById('btn-payment').textContent = 'Proceed to Payment';
            });
            rzp.open();
        }

        function verifyRazorpayPayment(orderId, response) {
            const verifyData = new FormData();
            verifyData.append('action', 'verify_payment');
            verifyData.append('order_id', orderId);
            verifyData.append('razorpay_payment_id', response.razorpay_payment_id);
            verifyData.append('razorpay_order_id', response.razorpay_order_id || '');
            verifyData.append('razorpay_signature', response.razorpay_signature || '');

            fetch('payment-process.php', {
                method: 'POST',
                body: verifyData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Payment successful! Your order is being processed.');
                    // Refresh cart count
                    if (window.refreshCartCount) {
                        window.refreshCartCount();
                    }
                    window.location.href = 'order-success.php?order_id=' + orderId;
                } else {
                    throw new Error(data.message || 'Payment verification failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error: ' + error.message);
                document.getElementById('btn-payment').disabled = false;
                document.getElementById('btn-payment').textContent = 'Proceed to Payment';
            });
        }
    </script>

    <?php
    include_once('includes/footer-link.php');
    ?>
</body>
</html>
