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