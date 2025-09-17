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
    <!-- ====== Header Start ====== -->
    <header class="ud-header">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <nav class="navbar navbar-expand-lg">
              <a class="navbar-brand" href="index.html">
                <img src="{{ image('logos/logo-dark.png') }}" alt="Logo" />
              </a>
              <button class="navbar-toggler">
                <span class="toggler-icon"> </span>
                <span class="toggler-icon"> </span>
                <span class="toggler-icon"> </span>
              </button>

              <div class="navbar-collapse">
                <ul id="nav" class="navbar-nav mx-auto">
                  <li class="nav-item">
                    <a class="ud-menu-scroll" href="#home">Home</a>
                  </li>

                  <li class="nav-item">
                    <a class="ud-menu-scroll" href="#about">About</a>
                  </li>
                  <li class="nav-item">
                    <a class="ud-menu-scroll" href="#pricing">Pricing</a>
                  </li>
                  <li class="nav-item">
                    <a class="ud-menu-scroll" href="#team">Team</a>
                  </li>
                  <li class="nav-item">
                    <a class="ud-menu-scroll" href="#contact">Contact</a>
                  </li>
                </ul>
              </div>

              <div class="navbar-btn d-none d-sm-inline-block">
                <a href="/login" class="ud-main-btn ud-login-btn">
                  Log In
                </a>
              </div>
            </nav>
          </div>
        </div>
      </div>
    </header>
    <!-- ====== Header End ====== -->

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
            <div class="col-xl-3 col-lg-4 col-md-6">
              <div class="ud-widget">
                <a href="index.html" class="ud-footer-logo">
                  <img src="{{ image('logos/logo-dark.png') }}" alt="logo" />
                </a>
                <p class="ud-widget-desc">
                  Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum eget.
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
