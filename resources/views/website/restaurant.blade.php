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
              <h1>restaurant cloud-based pos system</h1>

              <p class="ud-banner-desc">
                Speed. Accuracy. Control.
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
              <h2>The Smart Way to Manage Your Kitchen Flow</h2>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-12">
            <div class="text-center">
              <p>
                  Streamline your kitchen operations with precision and speed. Our Kitchen Cloud-based POS System ensures accurate order routing, real-time updates, and seamless communication between front-of-house and kitchen staff. Reduce errors, serve faster, and deliver a flawless dining experience every time.
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
              <h2>Smart Features for Fast-Paced Restaurants</h2>
            </div>
          </div>

          <div class="col-lg-12">
            <div class="ud-contact-info-wrapper">
                <div class="ud-single-info">
                    <div class="ud-info-icon">
                        <img src="assets/play/assets/images/logo/search.png"/>
                    </div>
                    <div class="ud-info-meta">
                        <h5>Table Management</h5>
                        <p>Easily manage tables, merge them for big groups, or reserve in advance—keep your dining flow organized and hassle-free.</p>
                    </div>
                </div>

                <div class="ud-single-info">
                    <div class="ud-info-icon">
                        <img src="assets/play/assets/images/logo/pause-play.png"/>
                    </div>
                    <div class="ud-info-meta">
                        <h5>Multiple Order Stations</h5>
                        <p>Manage multiple stations for a faster, more efficient kitchen and front-of-house workflow.</p>
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
                        <h5>Receipts & SOA</h5>
                        <p>View and print receipts anytime, plus generate Statements of Account (SOA) for smooth billing.</p>
                    </div>
                </div>

                <div class="ud-single-info">
                    <div class="ud-info-icon">
                        <img src="assets/play/assets/images/logo/advance-order.png"/>
                    </div>
                    <div class="ud-info-meta">
                        <h5>Take-Out Orders</h5>
                        <p>Easily process dine-in, take-out, and even multi-station orders without confusion.</p>
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
                  Quick Product Search
                </h3>
                <p class="ud-blog-desc">
                  Find items instantly with our
                  smart search—perfect for fast-
                  paced service.
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
                  Guest Count Tracking
                </h3>
                <p class="ud-blog-desc">
                  Monitor guest numbers per table
                  —essential for accurate
                  reporting and service planning.
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
                  Merge & Split Bills
                </h3>
                <p class="ud-blog-desc">
                  Combine tables or split bills for
                  group dining—flexibility at its
                  finest.
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
                  Item Status Tracking
                </h3>
                <p class="ud-blog-desc">
                  Mark dishes as prepared, in-
                  progress, or completed for
                  smooth workflow.
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
                  Multiple Printer Setup
                </h3>
                <p class="ud-blog-desc">
                  Send orders directly to kitchen
                  or bar printers—streamline
                  preparation like a pro.
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
                  Discount Management
                </h3>
                <p class="ud-blog-desc">
                  Apply discounts for PWD,
                  Senior, Solo Parent, NAAC,
                  Zero Rated, or custom promos
                  in just a tap.
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
                  Cash Handling Control
                </h3>
                <p class="ud-blog-desc">
                  Secure your finances with
                  Payout, Cash Fund, and
                  Safekeeping features—no
                  losses, no worries.
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
                  Spot Audit & Item Void
                </h3>
                <p class="ud-blog-desc">
                  Conduct surprise audits and
                  void transactions securely for
                  complete transparency.
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
                  Automatic Backup
                </h3>
                <p class="ud-blog-desc">
                  Your data is always safe—
                  automatic backups keep your
                  business running smoothly.
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
              <h2>Why Choose Our Restaurant POS?</h2>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-12">
            <ul>
                <li>
                    <img src="assets/play/assets/images/logo/check.png" style="width:40px" alt="blog">  Manage tables effortlessly
                </li>
                <li>
                    <img src="assets/play/assets/images/logo/check.png" style="width:40px" alt="blog"> Real-time kitchen updates.
                </li>
                <li>
                    <img src="assets/play/assets/images/logo/check.png" style="width:40px" alt="blog"> Flexible payments and discounts.
                </li>
                <li>
                    <img src="assets/play/assets/images/logo/check.png" style="width:40px" alt="blog"> Boost efficiencey and reduce errors.
                </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <section id="features" class="ud-features">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="ud-section-title">
              <h2>features coming soon...</h2>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-12">
            <div>
              <p>
                  Coming Soon to Elevate Your Restaurant Experience!
              </p>
              <p>
                Get ready for next-level efficiency and customer satisfaction with these powerful upcoming features:
              </p>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-6">
            <div>
              <img src="assets/play/assets/images/restaurant/kds.png" style="margin-top:-80px; margin-bottom:-80px" alt="blog">
            </div>
          </div>

          <div class="col-xl-6" style="vertical-align: middle; display: flex; align-items: center;">
            <div>
              <h2>Kitchen Display System (KDS)</h2>
              <p>
                Streamline your kitchen operations with a
                smart display for faster, error-free order
                management.
              </p>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-6" style="vertical-align: middle; display: flex; align-items: center;">
            <div>
              <h2>Built-in CRM</h2>
              <p>
                Know your customers better,
                personalize service, and build loyalty
                like never before.
              </p>
            </div>
          </div>

          <div class="col-xl-6">
            <div>
              <img src="assets/play/assets/images/restaurant/crm.png" style="margin-top:-80px; margin-bottom:-80px" alt="blog">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-6">
            <div>
              <img src="assets/play/assets/images/restaurant/qr-menu.png" style="margin-top:-80px; margin-bottom:-80px" alt="blog">
            </div>
          </div>

          <div class="col-xl-6" style="vertical-align: middle; display: flex; align-items: center;">
            <div>
              <h2>Electronic Menu (QR Code Self-Order)</h2>
              <p>
                Enable guests to order effortlessly
                from their own devices—fast, safe, and
                contactless.
              </p>
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
              <h2>More Advanced Features Coming Up:</h2>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-6">
            <div>
              <img src="assets/play/assets/images/restaurant/ai.png" style="margin-top:-80px; margin-bottom:-80px" alt="blog">
            </div>
          </div>

          <div class="col-xl-6" style="vertical-align: middle; display: flex; align-items: center;">
            <div>
              <h2>Built-in AI & Analytics</h2>
              <p>
                Make data-driven decisions for growth.
              </p>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-6" style="vertical-align: middle; display: flex; align-items: center;">
            <div>
              <h2>Third-Party Integration</h2>
              <p>
                Seamlessly connect with GrabFood, FoodPanda,
and your accounting system.
              </p>
            </div>
          </div>

          <div class="col-xl-6">
            <div>
              <img src="assets/play/assets/images/restaurant/integration.png" style="margin-top:-80px; margin-bottom:-80px" alt="blog">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-6">
            <div>
              <img src="assets/play/assets/images/restaurant/payment.png" style="margin-top:-80px; margin-bottom:-80px" alt="blog">
            </div>
          </div>

          <div class="col-xl-6" style="vertical-align: middle; display: flex; align-items: center;">
            <div>
              <h2>Payment Gateway Integration</h2>
              <p>
                Accept GCash, PayMaya, credit cards, and
more with ease.
              </p>
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
              <h2>STAY TUNED!</h2>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-12">
            <div>
              <p>
                  These features are designed to make your
restaurant smarter, faster, and more profitable.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="about" class="ud-about">
      <div class="container">
        <div class="ud-about-wrapper wow fadeInUp" data-wow-delay=".2s" style="visibility: visible; animation-delay: 0.2s; animation-name: fadeInUp;">
          <div class="ud-about-content-wrapper">
            <div class="ud-about-content">
              <h2>Take control of your business
today—start with the ultimate POS
solution!</h2>
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
