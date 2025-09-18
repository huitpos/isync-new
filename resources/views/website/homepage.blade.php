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

    <!-- ====== Hero Start ====== -->
    <section class="ud-hero" id="home">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="ud-hero-content wow fadeInUp" data-wow-delay=".2s">
              <h1 class="ud-hero-title">
                Run Your Business on the Go!
              </h1>
              <p class="ud-hero-desc">
                Track sales, monitor inventory, and view reports instantly<br/>—wherever you are, on any device.
              </p>
            </div>
            <div class="ud-hero-image wow fadeInUp" data-wow-delay=".25s">
              <img src="assets/play/assets/images/hero/1.png" alt="hero-image" />
              <img
                src="assets/play/assets/images/hero/dotted-shape.svg"
                alt="shape"
                class="shape shape-1"
              />
              <img
                src="assets/play/assets/images/hero/dotted-shape.svg"
                alt="shape"
                class="shape shape-2"
              />
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- ====== Hero End ====== -->

    <!-- ====== Features Start ====== -->
    <section id="features" class="ud-features">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="ud-section-title">
              <h2>Why Our Cloud POS Stands Out</h2>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-4 col-lg-4 col-sm-6">
            <div class="ud-single-feature wow fadeInUp text-center">
              <div class="ud-feature-icon mx-auto">
                <img src="assets/play/assets/images/logo/bir.png"/>
              </div>
              <div class="ud-feature-content">
                <h3 class="ud-feature-title">BIR-Accredited</h3>
                <p class="ud-feature-desc">
                  Generates BIR-ready reports for hassle-free tax filing.
                </p>
              </div>
            </div>
          </div>

          <div class="col-xl-4 col-lg-4 col-sm-6">
            <div class="ud-single-feature wow fadeInUp text-center">
              <div class="ud-feature-icon mx-auto">
                <img src="assets/play/assets/images/logo/cloud.png"/>
              </div>
              <div class="ud-feature-content">
                <h3 class="ud-feature-title">Cloud-Based System</h3>
                <p class="ud-feature-desc">
                  Real-time updates, automatic backups, and zero data loss, all without complicated installations.
                </p>
              </div>
            </div>
          </div>

          <div class="col-xl-4 col-lg-4 col-sm-6">
            <div class="ud-single-feature wow fadeInUp text-center">
              <div class="ud-feature-icon mx-auto">
                <img src="assets/play/assets/images/logo/inventory.png"/>
              </div>
              <div class="ud-feature-content">
                <h3 class="ud-feature-title">Inventory Control</h3>
                <p class="ud-feature-desc">
                  Real-time stock tracking and management.
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-4 col-lg-4 col-sm-6">
            <div class="ud-single-feature wow fadeInUp text-center">
              <div class="ud-feature-icon mx-auto">
                <img src="assets/play/assets/images/logo/branch.png"/>
              </div>
              <div class="ud-feature-content">
                <h3 class="ud-feature-title">Milti-Branch Management</h3>
                <p class="ud-feature-desc">
                  Manage all locations from one system.
                </p>
              </div>
            </div>
          </div>

          <div class="col-xl-4 col-lg-4 col-sm-6">
            <div class="ud-single-feature wow fadeInUp text-center">
              <div class="ud-feature-icon mx-auto">
                <img src="assets/play/assets/images/logo/eod.png"/>
              </div>
              <div class="ud-feature-content">
                <h3 class="ud-feature-title">End-of-Day Report Generation</h3>
                <p class="ud-feature-desc">
                  Instant, accurate daily summaries.
                </p>
              </div>
            </div>
          </div>

          <div class="col-xl-4 col-lg-4 col-sm-6">
            <div class="ud-single-feature wow fadeInUp text-center">
              <div class="ud-feature-icon mx-auto">
                <img src="assets/play/assets/images/logo/secured.png"/>
              </div>
              <div class="ud-feature-content">
                <h3 class="ud-feature-title">Secure Access</h3>
                <p class="ud-feature-desc">
                  Role-based authentication and full audit trails.
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-4 col-lg-4 col-sm-6">
            <div class="ud-single-feature wow fadeInUp text-center">
              <div class="ud-feature-icon mx-auto">
                <img src="assets/play/assets/images/logo/offline.png"/>
              </div>
              <div class="ud-feature-content">
                <h3 class="ud-feature-title">Offline Mode Architecture</h3>
                <p class="ud-feature-desc">
                  Uninterrupted operations even without an internet connection.
                </p>
              </div>
            </div>
          </div>

          <div class="col-xl-4 col-lg-4 col-sm-6">
            <div class="ud-single-feature wow fadeInUp text-center">
              <div class="ud-feature-icon mx-auto">
                <img src="assets/play/assets/images/logo/user-friendly.png"/>
              </div>
              <div class="ud-feature-content">
                <h3 class="ud-feature-title">User-friendly Interface</h3>
                <p class="ud-feature-desc">
                  Our POS is designed with a clean, intuitive interface anyone can master in minutes.
                </p>
              </div>
            </div>
          </div>

          <div class="col-xl-4 col-lg-4 col-sm-6">
            <div class="ud-single-feature wow fadeInUp text-center">
              <div class="ud-feature-icon mx-auto">
                <img src="assets/play/assets/images/logo/hardware.png"/>
              </div>
              <div class="ud-feature-content">
                <h3 class="ud-feature-title">Refined Hardware</h3>
                <p class="ud-feature-desc">
                  Build to last with a modern design that enhances your store's look while delivering reliable performance.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- ====== Features End ====== -->

    <section class="ud-features">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="ud-section-title mx-auto">
              <h2>What Makes Your Business Better with Our POS</h2>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-6">
            <div class="ud-single-feature wow fadeInUp text-center">
              <div class="ud-faq-icon mx-auto">
                <img src="assets/play/assets/images/homepage/anytime.png"/>
              </div>
              <div class="ud-feature-content">
                <h3 class="ud-feature-title">Anytime, Anywhere Access</h3>
                <p class="ud-feature-desc">
                  Manage your business remotely with real-time insights.
                </p>
              </div>
            </div>

            <div class="ud-single-feature wow fadeInUp text-center">
              <div class="ud-faq-icon mx-auto">
                <img src="assets/play/assets/images/homepage/customer-centric.png"/>
              </div>
              <div class="ud-feature-content">
                <h3 class="ud-feature-title">Customer-Centric Service</h3>
                <p class="ud-feature-desc">
                  Deliver fast, accurate, and hassle-free checkouts.
                </p>
              </div>
            </div>

            <div class="ud-single-feature wow fadeInUp text-center">
              <div class="ud-faq-icon mx-auto">
                <img src="assets/play/assets/images/homepage/unmatched-reliability.png"/>
              </div>
              <div class="ud-feature-content">
                <h3 class="ud-feature-title">Unmatched Reliability</h3>
                <p class="ud-feature-desc">
                  Secure data, backups, and disaster recovery you can trust.
                </p>
              </div>
            </div>

          </div>
          <div class="col-lg-6">
            <div class="ud-single-feature wow fadeInUp text-center">
              <div class="ud-faq-icon mx-auto">
                <img src="assets/play/assets/images/homepage/maximum-efficiency.png"/>
              </div>
              <div class="ud-feature-content">
                <h3 class="ud-feature-title">Maximum Efficiency</h3>
                <p class="ud-feature-desc">
                  Speed up transactions and automate processes to save time.
                </p>
              </div>
            </div>

            <div class="ud-single-feature wow fadeInUp text-center">
              <div class="ud-faq-icon mx-auto">
                <img src="assets/play/assets/images/homepage/full-customization.png"/>
              </div>
              <div class="ud-feature-content">
                <h3 class="ud-feature-title">Full Customization</h3>
                <p class="ud-feature-desc">
                  Configure the system to match your workflows and needs.
                </p>
              </div>
            </div>

            <div class="ud-single-feature wow fadeInUp text-center">
              <div class="ud-faq-icon mx-auto">
                <img src="assets/play/assets/images/homepage/full-customization.png"/>
              </div>
              <div class="ud-feature-content">
                <h3 class="ud-feature-title">Continuous Innovation</h3>
                <p class="ud-feature-desc">
                  Stay ahead with regular updates and smart features.
                </p>
              </div>
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
