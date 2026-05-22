<!-- Javascript -->
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/swiper-bundle.min.js"></script>
<script type="text/javascript" src="js/carousel.js"></script>
<script type="text/javascript" src="js/bootstrap-select.min.js"></script>
<script type="text/javascript" src="js/lazysize.min.js"></script>
<script type="text/javascript" src="js/count-down.js"></script>
<script type="text/javascript" src="js/wow.min.js"></script>
<script type="text/javascript" src="js/multiple-modal.js"></script>
<script type="text/javascript" src="js/nouislider.min.js"></script>
<script type="text/javascript" src="js/main.js"></script>

<!-- Global Login Check Functions -->
<script>
    // Global function to check login and add to cart
    function checkLoginAndAddToCart(event) {
        event.preventDefault();
        const isLoggedIn = <?php 
            require_once(__DIR__ . '/../includes/customer-auth.php');
            echo json_encode(is_customer_logged_in()); 
        ?>;
        
        if (!isLoggedIn) {
            if (confirm('You need to be logged in to add items to your cart. Would you like to login now?')) {
                window.location.href = 'customer-login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
            }
        } else {
            // Redirect to cart page
            window.location.href = 'cart.php';
        }
    }

    // Global function to check login and buy now
    function checkLoginAndBuyNow(event) {
        event.preventDefault();
        const isLoggedIn = <?php 
            require_once(__DIR__ . '/../includes/customer-auth.php');
            echo json_encode(is_customer_logged_in()); 
        ?>;
        
        if (!isLoggedIn) {
            if (confirm('You need to be logged in to make a purchase. Would you like to login now?')) {
                window.location.href = 'customer-login.php?redirect=checkout.php';
            }
        } else {
            // Redirect to checkout page
            window.location.href = 'checkout.php';
        }
    }

    const wishlistLoginRequired = <?php
        require_once(__DIR__ . '/../includes/customer-auth.php');
        echo json_encode(!is_customer_logged_in());
    ?>;
    const wishlistStorageKey = 'nutriafghan_wishlist_items';

    function escapeWishlistHtml(value) {
        const span = document.createElement('span');
        span.textContent = value == null ? '' : String(value);
        return span.innerHTML;
    }

    function renderWishlistItems(items) {
        const list = document.getElementById('wishlist-items');
        if (!list) {
            return;
        }

        if (!Array.isArray(items) || items.length === 0) {
            list.innerHTML = '<div class="text-center wishlist-empty" style="padding: 40px 20px; color: #999;"><p>Your wishlist is empty</p></div>';
            return;
        }

        list.innerHTML = items.map(function (item) {
            const itemId = escapeWishlistHtml(item.id);
            const name = escapeWishlistHtml(item.name);
            const image = escapeWishlistHtml(item.image);
            const slug = encodeURIComponent(item.slug || '');
            const price = Number(item.price || 0).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            return `
                <div class="tf-mini-cart-item file-delete" data-wishlist-item-id="${item.id}" data-product-id="${item.product_id}">
                    <div class="tf-mini-cart-image">
                        <img class="lazyload" data-src="${image}" src="${image}" alt="${name}" style="width: 100%; height: 100%; object-fit: cover;" />
                    </div>
                    <div class="tf-mini-cart-info flex-grow-1">
                        <div class="mb_12 d-flex align-items-center justify-content-between flex-wrap gap-12">
                            <div class="text-title">
                                <a href="product-detail.php?slug=${slug}" class="link text-line-clamp-1">${name}</a>
                            </div>
                            <div class="text-button tf-btn-remove remove" onclick="removeFromWishlist('${itemId}')">Remove</div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-12">
                            <div class="text-secondary-2">Saved item</div>
                            <div class="text-button">₹${price}</div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function getLocalWishlistItems() {
        try {
            return JSON.parse(localStorage.getItem(wishlistStorageKey) || '[]');
        } catch (error) {
            return [];
        }
    }

    function saveLocalWishlistItems(items) {
        localStorage.setItem(wishlistStorageKey, JSON.stringify(items));
    }

    function updateWishlistCount(count) {
        const countBox = document.getElementById('wishlist-count-box');
        if (countBox) {
            countBox.textContent = Number(count || 0);
        }
    }

    function getProductDataFromButton(button) {
        const card = button.closest('.card-product');
        const image = card ? card.querySelector('.img-product, .product-img img') : document.querySelector('.tf-product-media-main img, .product-img img');
        const title = card ? card.querySelector('.card-product-info .title') : document.querySelector('.tf-product-info-title h5, .tf-product-info-title, h3');
        const price = card ? card.querySelector('.price, .sale-price, .product-price-line') : document.querySelector('.price-on-sale, .total-price, .tf-product-info-price');
        const link = card ? card.querySelector('.card-product-info .title, .product-img') : document.querySelector('a[href*="product-detail.php"]');
        const productId = button.getAttribute('data-product-id');
        const rawPrice = price ? price.textContent.replace(/[^\d.]/g, '') : '0';

        return {
            id: 'local-' + productId,
            product_id: Number(productId),
            name: button.getAttribute('data-product-name') || (title ? title.textContent.trim() : 'Product'),
            slug: button.getAttribute('data-product-slug') || (link && link.href.indexOf('slug=') !== -1 ? new URL(link.href).searchParams.get('slug') : ''),
            image: button.getAttribute('data-product-image') || (image ? (image.getAttribute('data-src') || image.getAttribute('src') || 'images/pista_demp.jpg') : 'images/pista_demp.jpg'),
            price: Number(button.getAttribute('data-product-price') || rawPrice || 0)
        };
    }

    function addLocalWishlistItem(item) {
        const items = getLocalWishlistItems();
        const exists = items.some(function (wishlistItem) {
            return Number(wishlistItem.product_id) === Number(item.product_id);
        });

        if (!exists) {
            items.unshift(item);
            saveLocalWishlistItems(items);
        }

        updateWishlistCount(items.length);
        return getLocalWishlistItems();
    }

    function showWishlistMessage(message) {
        let toast = document.getElementById('wishlist-toast-message');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'wishlist-toast-message';
            toast.style.position = 'fixed';
            toast.style.top = '90px';
            toast.style.right = '20px';
            toast.style.zIndex = '99999';
            toast.style.maxWidth = '320px';
            toast.style.padding = '14px 18px';
            toast.style.borderRadius = '6px';
            toast.style.background = '#181818';
            toast.style.color = '#ffffff';
            toast.style.boxShadow = '0 8px 24px rgba(0, 0, 0, 0.18)';
            toast.style.fontSize = '14px';
            toast.style.lineHeight = '1.4';
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-8px)';
            toast.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
            document.body.appendChild(toast);
        }

        toast.textContent = message;
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';

        clearTimeout(window.wishlistToastTimer);
        window.wishlistToastTimer = setTimeout(function () {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-8px)';
        }, 2200);
    }

    function openWishlistModal() {
        const wishlistModal = document.getElementById('wishlist');
        if (!wishlistModal) {
            return;
        }

        if (window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(wishlistModal).show();
            return;
        }

        if (window.jQuery && typeof jQuery.fn.modal === 'function') {
            jQuery('#wishlist').modal('show');
            return;
        }

        wishlistModal.style.display = 'block';
        wishlistModal.classList.add('show');
        wishlistModal.removeAttribute('aria-hidden');
        wishlistModal.setAttribute('aria-modal', 'true');
        document.body.classList.add('modal-open');

        if (!document.querySelector('.modal-backdrop.wishlist-backdrop')) {
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show wishlist-backdrop';
            backdrop.addEventListener('click', closeWishlistModal);
            document.body.appendChild(backdrop);
        }
    }

    function closeWishlistModal() {
        const wishlistModal = document.getElementById('wishlist');
        if (!wishlistModal) {
            return;
        }

        if (window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(wishlistModal).hide();
        } else if (window.jQuery && typeof jQuery.fn.modal === 'function') {
            jQuery('#wishlist').modal('hide');
        } else {
            wishlistModal.classList.remove('show');
            wishlistModal.style.display = 'none';
            wishlistModal.setAttribute('aria-hidden', 'true');
            wishlistModal.removeAttribute('aria-modal');
            document.body.classList.remove('modal-open');
        }

        const backdrop = document.querySelector('.modal-backdrop.wishlist-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
    }

    function loadWishlistItems() {
        if (wishlistLoginRequired) {
            renderWishlistItems(getLocalWishlistItems());
            return Promise.resolve(getLocalWishlistItems());
        }

        return fetch('wishlist-process.php?action=list', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            if (data.success) {
                renderWishlistItems(data.wishlist_items);
                return data.wishlist_items;
            }
            renderWishlistItems(getLocalWishlistItems());
            return getLocalWishlistItems();
        })
        .catch(function () {
            renderWishlistItems(getLocalWishlistItems());
            return getLocalWishlistItems();
        });
    }

    function addToWishlist(productId) {
        const params = new URLSearchParams();
        params.set('action', 'add');
        params.set('product_id', productId);

        return fetch('wishlist-process.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: params.toString()
        }).then(function (response) {
            return response.json().then(function (data) {
                if (!response.ok) {
                    throw data;
                }
                return data;
            });
        });
    }

    function removeFromWishlist(wishlistItemId) {
        if (String(wishlistItemId).indexOf('local-') === 0 || wishlistLoginRequired) {
            const items = getLocalWishlistItems().filter(function (item) {
                return String(item.id) !== String(wishlistItemId);
            });
            saveLocalWishlistItems(items);
            renderWishlistItems(items);
            updateWishlistCount(items.length);
            return;
        }

        const params = new URLSearchParams();
        params.set('action', 'remove');
        params.set('wishlist_item_id', wishlistItemId);

        fetch('wishlist-process.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: params.toString()
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            if (data.success) {
                renderWishlistItems(data.wishlist_items);
                updateWishlistCount(data.wishlist_count || (data.wishlist_items ? data.wishlist_items.length : 0));
            } else {
                alert(data.message || 'Could not remove wishlist item.');
            }
        })
        .catch(function () {
            alert('Could not remove wishlist item.');
        });
    }
    window.removeFromWishlist = removeFromWishlist;

    document.addEventListener('click', function (event) {
        const wishlistOpenButton = event.target.closest('a[href="#wishlist"]:not(.btn-icon-action)');
        if (wishlistOpenButton) {
            event.preventDefault();
            loadWishlistItems().then(openWishlistModal);
            return;
        }

        const wishlistCloseButton = event.target.closest('#wishlist .icon-close-popup, #wishlist [data-bs-dismiss="modal"]');
        if (wishlistCloseButton) {
            event.preventDefault();
            closeWishlistModal();
            return;
        }

        const wishlistButton = event.target.closest('.btn-icon-action.wishlist[data-product-id]');
        if (!wishlistButton) {
            return;
        }

        event.preventDefault();
        const productId = wishlistButton.getAttribute('data-product-id');
        if (!productId) {
            return;
        }

        const localProduct = getProductDataFromButton(wishlistButton);

        if (wishlistLoginRequired) {
            renderWishlistItems(addLocalWishlistItem(localProduct));
            wishlistButton.classList.add('active');
            showWishlistMessage('Product added to your wishlist.');
            return;
        }

        addToWishlist(productId)
            .then(function (data) {
                renderWishlistItems(data.wishlist_items);
                updateWishlistCount(data.wishlist_count || (data.wishlist_items ? data.wishlist_items.length : 0));
                wishlistButton.classList.add('active');
                showWishlistMessage(data.message || 'Product added to your wishlist.');
            })
            .catch(function (data) {
                renderWishlistItems(addLocalWishlistItem(localProduct));
                wishlistButton.classList.add('active');
                showWishlistMessage('Product added to your wishlist.');
            });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const wishlistModal = document.getElementById('wishlist');
        if (wishlistLoginRequired) {
            updateWishlistCount(getLocalWishlistItems().length);
        }
        if (wishlistModal && wishlistLoginRequired) {
            wishlistModal.addEventListener('show.bs.modal', function () {
                renderWishlistItems(getLocalWishlistItems());
            });
        }
    });
</script>

<script src="js/sibforms.js" defer></script>

<script>
  window.REQUIRED_CODE_ERROR_MESSAGE = "Please choose a country code";
  window.LOCALE = "en";
  window.EMAIL_INVALID_MESSAGE = window.SMS_INVALID_MESSAGE =
    "The information provided is invalid. Please review the field format and try again.";

  window.REQUIRED_ERROR_MESSAGE = "This field cannot be left blank. ";

  window.GENERIC_INVALID_MESSAGE =
    "The information provided is invalid. Please review the field format and try again.";

  window.translation = {
    common: {
      selectedList: "{quantity} list selected",
      selectedLists: "{quantity} lists selected",
    },
  };

  var AUTOHIDE = Boolean(0);
</script>
