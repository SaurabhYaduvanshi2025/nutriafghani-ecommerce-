<?php
$contactStatus = $_GET['status'] ?? '';
$contactMessages = [
    'sent' => ['type' => 'success', 'text' => 'Thank you. Your message has been sent successfully.'],
    'missing' => ['type' => 'danger', 'text' => 'Please fill all required fields.'],
    'invalid_name' => ['type' => 'danger', 'text' => 'Please enter a valid name.'],
    'invalid_email' => ['type' => 'danger', 'text' => 'Please enter a valid email address.'],
    'invalid_mobile' => ['type' => 'danger', 'text' => 'Please enter a valid mobile number.'],
    'invalid_message' => ['type' => 'danger', 'text' => 'Please enter a message between 10 and 1000 characters.'],
    'too_long' => ['type' => 'danger', 'text' => 'One or more fields are too long. Please review your details.'],
    'error' => ['type' => 'danger', 'text' => 'Could not send your message right now. Please try again.'],
    'invalid' => ['type' => 'danger', 'text' => 'Invalid request. Please try again.'],
];
$contactAlert = $contactMessages[$contactStatus] ?? null;
?>
<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Nutri Afghan</title>

  <meta name="author" content=""/>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
  <meta name="description" content=""/>

  <?php
  include_once('includes/header-link.php');
  ?>
  <style>
    .contact-field .contact-error {
      display: none;
      margin-top: 6px;
      color: #dc3545;
      font-size: 13px;
      line-height: 1.4;
    }

    .contact-field.is-invalid input,
    .contact-field.is-invalid textarea {
      border-color: #dc3545;
    }

    .contact-field.is-invalid .contact-error {
      display: block;
    }
  </style>
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
                        <h3 class="heading text-center">Contact Us</h3>
                        <ul class="breadcrumbs d-flex align-items-center justify-content-center">
                            <li>
                                <a class="link" href="./">Home</a>
                            </li>
                            <li>
                                <i class="icon-arrRight"></i>
                            </li>
                            <li>
                                Contact Us
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- /page-title -->

        <!-- Store locations -->
        <section class="flat-spacing">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="contact-us-map">
                            <div class="wrap-map">
                               <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4348.761043360073!2d77.05655487616225!3d28.552853887693217!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390d1b484253cef7%3A0xe64b9713e5420705!2sNutri%20Afghan!5e1!3m2!1sen!2sin!4v1765434797364!5m2!1sen!2sin" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                            </div>
                            <div class="right">
                                <h4>Information</h4>
                                <div class="mb_20">
                                    <div class="text-title mb_8">Phone:</div>
                                    <p class="text-secondary"><a href="tal:+918744982660">+918744982660</a></p>
                                </div>
                                <div class="mb_20">
                                    <div class="text-title mb_8">Email:</div>
                                    <p class="text-secondary"><a href="mailto:md@nutriafghan.com">md@nutriafghan.com</a></p>
                                </div>
                                <div class="mb_20">
                                    <div class="text-title mb_8">Address:</div>
                                    <p class="text-secondary">1st Floor, IK1F09 Pacific Mall Dwarka Sector 21 Metro Station, Delhi-110077</p>
                                </div>
                                <div>
                                    <div class="text-title mb_8">Open Time:</div>
                                    <p class="mb_4 open-time">
                                        <span class="text-secondary">Mon - Sat:</span> 7:30am - 8:00pm PST
                                    </p>
                                    <p class="open-time">
                                        <span class="text-secondary">Sunday:</span> 9:00am - 5:00pm PST
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Store locations -->

        <!-- Get In Touch -->
        <section class="flat-spacing pt-0">
            <div class="container">
                <div class="heading-section text-center">
                    <h3 class="heading">Get In Touch</h3>
                    <p class="subheading">Use the form below to get in touch with the sales team</p>
                </div>
                <?php if ($contactAlert): ?>
                    <div class="alert alert-<?php echo htmlspecialchars($contactAlert['type']); ?>" role="alert">
                        <?php echo htmlspecialchars($contactAlert['text']); ?>
                    </div>
                <?php endif; ?>
                <form id="contactform" action="contact-process.php" method="post" class="form-leave-comment">
                    <div class="wrap">
                        <div class="cols">
                            <fieldset class="contact-field">
                                <input class="" type="text" placeholder="Your Name*" name="name" id="name" tabindex="2" value="" aria-required="true" required minlength="2" maxlength="80" autocomplete="name">
                                <div class="contact-error" data-error-for="name">Please enter your name, at least 2 characters.</div>
                            </fieldset>
                            <fieldset class="contact-field">
                                <input class="" type="email" placeholder="Your Email*" name="email" id="email" tabindex="2" value="" aria-required="true" required maxlength="150" autocomplete="email">
                                <div class="contact-error" data-error-for="email">Please enter a valid email address.</div>
                            </fieldset>
                            <fieldset class="contact-field">
                                <input class="" type="tel" placeholder="Your Mobile*" name="mobile" id="mobile" tabindex="2" value="" aria-required="true" required minlength="7" maxlength="20" pattern="[0-9+\-\s()]{7,20}" autocomplete="tel">
                                <div class="contact-error" data-error-for="mobile">Please enter a valid mobile number.</div>
                            </fieldset>
                        </div>
                        <fieldset class="contact-field">
                            <textarea name="message" id="message" rows="4" placeholder="Your Message*" tabindex="2" aria-required="true" required minlength="10" maxlength="1000"></textarea>
                            <div class="contact-error" data-error-for="message">Please enter a message between 10 and 1000 characters.</div>
                        </fieldset>
                    </div>
                    <div class="button-submit send-wrap">
                        <button class="tf-btn btn-fill" type="submit">
                            <span class="text text-button">Send message</span>
                        </button>
                    </div>
                </form>
            </div>
        </section>
        <!-- /Get In Touch -->

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
    document.addEventListener('DOMContentLoaded', function () {
      var form = document.getElementById('contactform');
      if (!form) {
        return;
      }

      var fields = {
        name: {
          element: document.getElementById('name'),
          validate: function (value) {
            return value.length >= 2 && value.length <= 80;
          }
        },
        email: {
          element: document.getElementById('email'),
          validate: function (value) {
            return value.length <= 150 && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
          }
        },
        mobile: {
          element: document.getElementById('mobile'),
          validate: function (value) {
            var digits = value.replace(/\D/g, '');
            return /^[0-9+\-\s()]{7,20}$/.test(value) && digits.length >= 7 && digits.length <= 15;
          }
        },
        message: {
          element: document.getElementById('message'),
          validate: function (value) {
            return value.length >= 10 && value.length <= 1000;
          }
        }
      };

      function setFieldState(field, isValid) {
        var wrapper = field.element.closest('.contact-field');
        if (!wrapper) {
          return;
        }

        wrapper.classList.toggle('is-invalid', !isValid);
        field.element.setAttribute('aria-invalid', isValid ? 'false' : 'true');
      }

      function validateField(field) {
        var value = field.element.value.trim();
        var isValid = field.validate(value);
        setFieldState(field, isValid);
        return isValid;
      }

      Object.keys(fields).forEach(function (key) {
        fields[key].element.addEventListener('input', function () {
          validateField(fields[key]);
        });
      });

      form.addEventListener('submit', function (event) {
        var firstInvalid = null;
        var isFormValid = Object.keys(fields).reduce(function (formValid, key) {
          var isValid = validateField(fields[key]);
          if (!isValid && !firstInvalid) {
            firstInvalid = fields[key].element;
          }
          return formValid && isValid;
        }, true);

        if (!isFormValid) {
          event.preventDefault();
          firstInvalid.focus();
        }
      });
    });
  </script>
</body>
</html>
