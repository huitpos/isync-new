<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>iSync</title>

    <!--====== Favicon Icon ======-->
    {!! includeFavicon() !!}

    <!-- ===== All CSS files ===== -->
    <link rel="stylesheet" href="assets/play/assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/play/assets/css/animate.css" />
    <link rel="stylesheet" href="assets/play/assets/css/lineicons.css" />
    <link rel="stylesheet" href="assets/play/assets/css/ud-styles.css" />
  </head>
  <body>
    @include('website.header')

    <section class="ud-page-banner">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="ud-banner-content">
              <h1>retail cloud-based pos system</h1>

              <p class="ud-banner-desc">
                Simple, Fast, and Reliable
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="features" class="ud-features text-center">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="ud-section-title">
              <h2>Effortless Retail Management</h2>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-12">
            <div class="text-center">
              <p>
                  Take your retail business to the next level with a feature-rich POS system that’s designed for speed, accuracy, and convenience. Our Retail POS is more than just a cash register—it’s a complete retail management solution that helps you sell smarter, track better, and deliver a seamless customer experience.
                </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="features" class="ud-features text-center">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="ud-section-title">
              <h2>Key Features That Make Retail Easier</h2>
            </div>
          </div>

          <div class="col-lg-12">
            <div class="ud-contact-info-wrapper">
                <div class="ud-single-info">
                    <div class="ud-info-icon">
                        <img src="assets/play/assets/images/logo/search.png"/>
                    </div>
                    <div class="ud-info-meta">
                        <h5>Quick Product Search</h5>
                        <p>Find items instantly with our powerful search functionality—speed up checkout and keep lines moving.</p>
                    </div>
                </div>

                <div class="ud-single-info">
                    <div class="ud-info-icon">
                        <img src="assets/play/assets/images/logo/pause-play.png"/>
                    </div>
                    <div class="ud-info-meta">
                        <h5>Pause & Resume</h5>
                        <p>Need to hold a transaction? Pause it and pick up right where you left off—no interruptions in service.</p>
                    </div>
                </div>
            </div>
          </div>

          <div class="col-lg-12">
            <div class="ud-contact-info-wrapper">
                <div class="ud-single-info">
                    <div class="ud-info-icon">
                        <img src="assets/play/assets/images/logo/receipt.png"/>
                    </div>
                    <div class="ud-info-meta">
                        <h5>Receipt Viewing</h5>
                        <p>Access past transactions and reprint receipts anytime—keeping records organized and accessible.</p>
                    </div>
                </div>

                <div class="ud-single-info">
                    <div class="ud-info-icon">
                        <img src="assets/play/assets/images/logo/advance-order.png"/>
                    </div>
                    <div class="ud-info-meta">
                        <h5>Advance Orders</h5>
                        <p>Take pre-orders and manage them efficiently—perfect for retail stores offering future pickups.</p>
                    </div>
                </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="ud-blog-grids">
      <div class="container">
        <div class="row">
          <div class="col-lg-4 col-md-6">
            <div class="ud-single-blog">
              <div class="ud-blog-image">
                <img src="assets/play/assets/images/logo/discount.png" alt="blog">
              </div>
              <div class="ud-blog-content text-center">
                <h3 class="ud-blog-title">
                  Smart Discounts
                </h3>
                <p class="ud-blog-desc">
                  Apply special discounts like
                    <strong>PWD, Senior Citizen, NAAC,
                    Solo Parent, Zero Rated,</strong> or
                    custom deals with ease—ensure
                    compliance and customer
                    satisfaction.
                </p>
              </div>
            </div>
          </div>

          <div class="col-lg-4 col-md-6">
            <div class="ud-single-blog">
              <div class="ud-blog-image">
                <img src="assets/play/assets/images/logo/xy-reading.png" alt="blog">
              </div>
              <div class="ud-blog-content text-center">
                <h3 class="ud-blog-title">
                  X and Z Reading
                </h3>
                <p class="ud-blog-desc">
                  Generate accurate daily reports
                    in just one click for easy end-of-
                    day reconciliation and financial
                    control.
                </p>
              </div>
            </div>
          </div>

          <div class="col-lg-4 col-md-6">
            <div class="ud-single-blog">
              <div class="ud-blog-image">
                <img src="assets/play/assets/images/logo/barcode.png" alt="blog">
              </div>
              <div class="ud-blog-content text-center">
                <h3 class="ud-blog-title">
                  Barcode Scanning
                </h3>
                <p class="ud-blog-desc">
                  Scan products fast and error-
                    free for a smooth checkout
                    experience every time.
                </p>
              </div>
            </div>
          </div>

          <div class="col-lg-4 col-md-6">
            <div class="ud-single-blog">
              <div class="ud-blog-image">
                <img src="assets/play/assets/images/logo/order-station.png" alt="blog">
              </div>
              <div class="ud-blog-content text-center">
                <h3 class="ud-blog-title">
                  Order Station
                </h3>
                <p class="ud-blog-desc">
                  Manage multiple order stations
                    seamlessly for a faster checkout
                    experience.
                </p>
              </div>
            </div>
          </div>

          <div class="col-lg-4 col-md-6">
            <div class="ud-single-blog">
              <div class="ud-blog-image">
                <img src="assets/play/assets/images/logo/ar.png" alt="blog">
              </div>
              <div class="ud-blog-content text-center">
                <h3 class="ud-blog-title">
                  Accounts Receivable
                </h3>
                <p class="ud-blog-desc">
                  Track credit sales and
                    outstanding balances
                    without the hassle—stay
                    on top of your
                    receivables.
                </p>
              </div>
            </div>
          </div>

          <div class="col-lg-4 col-md-6">
            <div class="ud-single-blog">
              <div class="ud-blog-image">
                <img src="assets/play/assets/images/logo/payout.png" alt="blog">
              </div>
              <div class="ud-blog-content text-center">
                <h3 class="ud-blog-title">
                  Payout and Cash Fund Management
                </h3>
                <p class="ud-blog-desc">
                  Control store cash flow with
                    <strong>Payout, Cash Fund, and
                    Safekeeping</strong> features for
                    secure money handling.
                </p>
              </div>
            </div>
          </div>

          <div class="col-lg-4 col-md-6">
            <div class="ud-single-blog">
              <div class="ud-blog-image">
                <img src="assets/play/assets/images/logo/spot-audit.png" alt="blog">
              </div>
              <div class="ud-blog-content text-center">
                <h3 class="ud-blog-title">
                  Spot Audit and Item Void
                </h3>
                <p class="ud-blog-desc">
                  Perform surprise audits and void
                    items securely—ensuring
                    accurate reporting and fraud
                    prevention.
                </p>
              </div>
            </div>
          </div>

          <div class="col-lg-4 col-md-6">
            <div class="ud-single-blog">
              <div class="ud-blog-image">
                <img src="assets/play/assets/images/logo/auto-backup.png" alt="blog">
              </div>
              <div class="ud-blog-content text-center">
                <h3 class="ud-blog-title">
                  Automatic Backup
                </h3>
                <p class="ud-blog-desc">
                  Keep your data safe with
                    built-in backup—protect
                    your business from data
                    loss.
                </p>
              </div>
            </div>
          </div>

          <div class="col-lg-4 col-md-6">
            <div class="ud-single-blog">
              <div class="ud-blog-image">
                <img src="assets/play/assets/images/logo/printer.png" alt="blog">
              </div>
              <div class="ud-blog-content text-center">
                <h3 class="ud-blog-title">
                  Printer Setup Made Easy
                </h3>
                <p class="ud-blog-desc">
                   Configure your receipt printer
                    in just a few clicks—fast,
                    simple, and ready to go.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="features" class="ud-features">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="ud-section-title">
              <h2>Why Choose Our Retail POS?</h2>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-12">
            <ul>
                <li>
                    <img src="assets/play/assets/images/logo/check.png" style="width:40px" alt="blog"> Fast, intuitive, and easy to use
                </li>
                <li>
                    <img src="assets/play/assets/images/logo/check.png" style="width:40px" alt="blog"> 100% cloud-based—access anytime, anywhere
                </li>
                <li>
                    <img src="assets/play/assets/images/logo/check.png" style="width:40px" alt="blog"> Secure and reliable for any retail business
                </li>
                <li>
                    <img src="assets/play/assets/images/logo/check.png" style="width:40px" alt="blog"> Designed to improve operations and boost sales
                </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <section id="about" class="ud-about">
      <div class="container">
        <div class="ud-about-wrapper wow fadeInUp" data-wow-delay=".2s" style="visibility: visible; animation-delay: 0.2s; animation-name: fadeInUp;">
          <div class="ud-about-content-wrapper">
            <div class="ud-about-content">
              <h2>Upgrade your POS experience now and enjoy seamless operations.</h2>
              <a href="javascript:void(0)" class="ud-main-btn">Learn More</a>
            </div>
          </div>
          <div class="ud-about-image">
            <img src="assets/play/assets/images/homepage/pos-screenshot.png" alt="about-image">
          </div>
        </div>
      </div>
    </section>

    <!-- ====== Footer Start ====== -->
    <footer class="ud-footer wow fadeInUp" data-wow-delay=".15s">
      <div class="shape shape-1">
        <img src="assets/play/assets/images/footer/shape-1.svg" alt="shape" />
      </div>
      <div class="shape shape-2">
        <img src="assets/play/assets/images/footer/shape-2.svg" alt="shape" />
      </div>
      <div class="shape shape-3">
        <img src="assets/play/assets/images/footer/shape-3.svg" alt="shape" />
      </div>
      <div class="ud-footer-widgets">
        <div class="container">
          <div class="row">
            <div class="col-xl-6">
              <div class="ud-widget">
                <a href="index.html" class="ud-footer-logo">
                  <img src="{{ image('logos/logo-dark.png') }}" alt="logo" />
                </a>
                <p class="ud-widget-desc">
                  Call us at:  09171933977<br/>
                  Email: sales@isync.ph</br/>
                  Address: Blk 18 Lot 39 Madrid St. Town and Country West Molino 3 Bacoor Cavite
                </p>
              </div>
            </div>

            <div class="col-xl-5">
              <div class="ud-widget" style="margin-top: 80px">
                <p class="ud-widget-desc">
                  One click away from smarter sales and seamless service, get in touch now.
                </p>
                <ul class="ud-widget-socials">
                  <li>
                    <a href="#">
                      <i class="lni lni-facebook-filled"></i>
                    </a>
                  </li>
                  <li>
                    <a href="#">
                      <i class="lni lni-twitter-filled"></i>
                    </a>
                  </li>
                  <li>
                    <a href="#">
                      <i class="lni lni-instagram-filled"></i>
                    </a>
                  </li>
                  <li>
                    <a href="#">
                      <i class="lni lni-linkedin-original"></i>
                    </a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="ud-footer-bottom">
        <div class="container">
          <div class="row">
            <div class="col-md-8">
              <ul class="ud-footer-bottom-left">
                <li>
                  <a href="javascript:void(0)">Privacy policy</a>
                </li>
                <li>
                  <a href="javascript:void(0)">Support policy</a>
                </li>
                <li>
                  <a href="javascript:void(0)">Terms of service</a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </footer>
    <!-- ====== Footer End ====== -->

    <!-- ====== Back To Top Start ====== -->
    <a href="javascript:void(0)" class="back-to-top">
      <i class="lni lni-chevron-up"> </i>
    </a>
    <!-- ====== Back To Top End ====== -->

    <!-- ====== All Javascript Files ====== -->
    <script src="assets/play/assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/play/assets/js/wow.min.js"></script>
    <script src="assets/play/assets/js/main.js"></script>
    <script>
      // ==== for menu scroll
      const pageLink = document.querySelectorAll(".ud-menu-scroll");

      pageLink.forEach((elem) => {
        elem.addEventListener("click", (e) => {
          e.preventDefault();
          document.querySelector(elem.getAttribute("href")).scrollIntoView({
            behavior: "smooth",
            offsetTop: 1 - 60,
          });
        });
      });

      // section menu active
      function onScroll(event) {
        const sections = document.querySelectorAll(".ud-menu-scroll");
        const scrollPos =
          window.pageYOffset ||
          document.documentElement.scrollTop ||
          document.body.scrollTop;

        for (let i = 0; i < sections.length; i++) {
          const currLink = sections[i];
          const val = currLink.getAttribute("href");
          const refElement = document.querySelector(val);
          const scrollTopMinus = scrollPos + 73;
          if (
            refElement.offsetTop <= scrollTopMinus &&
            refElement.offsetTop + refElement.offsetHeight > scrollTopMinus
          ) {
            document
              .querySelector(".ud-menu-scroll")
              .classList.remove("active");
            currLink.classList.add("active");
          } else {
            currLink.classList.remove("active");
          }
        }
      }

      window.document.addEventListener("scroll", onScroll);
    </script>
  </body>
</html>
