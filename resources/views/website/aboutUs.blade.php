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
              <h1>ABOUT US</h1>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="features" class="ud-features text-center">
      <div class="container">
        <div class="row">
          <div class="col-xl-12">
            <div class="text-center">
                <p>
                  iSync Enterprise Inc. is a future-ready company committed to redefining the retail and restaurant landscape through smart, innovative digital solutions. Established in October 2024, our mission is clear: to empower businesses with advanced, cloud-based Point of Sale (POS) systems that simplify operations, elevate customer experiences, and fuel business growth.
                </p>

                <p style="margin-top:20px">
                    We go beyond traditional POS systems by delivering secure, scalable, and intuitive solutions that adapt to the evolving needs of modern businesses. At iSync, we believe technology should work for you—helping you save time, cut costs, and stay ahead in a fast-changing market.
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
              <h2>OUR TEAM</h2>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-12">
            <div class="text-center">
              <p>
                  iSync Enterprise Inc. takes pride in its diverse team of highly skilled Filipino professionals. With years of experience in crafting tailored applications and solutions for the retail and restaurant sectors, our team brings a unique blend of technical expertise and deep industry insight. This powerful combination allows us to deliver innovative, reliable, and results-driven solutions that help businesses thrive in an ever-changing market.
                </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="team" class="ud-team">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="ud-section-title mx-auto text-center">
              <h2>MEET OUR EXPERTS</h2>
              <span>TEAM MEMBERS</span>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-12">
            <div class="ud-single-team wow fadeInUp" data-wow-delay=".1s">
              <div class="ud-team-image-wrapper">
                <div class="ud-team-image">
                  <img src="assets/play/assets/images/about-us/rejee.jpg" alt="team" />
                </div>

                <img
                  src="assets/play/assets/images/team/dotted-shape.svg"
                  alt="shape"
                  class="shape shape-1"
                />
                <img
                  src="assets/play/assets/images/team/shape-2.svg"
                  alt="shape"
                  class="shape shape-2"
                />
              </div>
              <div class="ud-team-info">
                <h5>Reginald "Rejee" Dijamco </h5>
                <h6>Co-Founder & CTO</h6>

                <p>Visionary tech leader driving the overall<br/>
                    product strategy, system architecture, and<br/>
                    technology roadmap.
                </p>
              </div>
              
            </div>
          </div>

          <div class="col-xl-12">
            <div class="ud-single-team wow fadeInUp" data-wow-delay=".1s">
              <div class="ud-team-image-wrapper">
                <div class="ud-team-image">
                  <img src="assets/play/assets/images/about-us/gelo.jpg" style="height:230px" alt="team" />
                </div>

                <img
                  src="assets/play/assets/images/team/dotted-shape.svg"
                  alt="shape"
                  class="shape shape-1"
                />
                <img
                  src="assets/play/assets/images/team/shape-2.svg"
                  alt="shape"
                  class="shape shape-2"
                />
              </div>
              <div class="ud-team-info">
                <h5>Angelo “Gelo” Del Mundo</h5>
                <h6>Co-Founder & Mobile App Developer</h6>

                <p>Specialist in Android and iOS development,<br/>
focused on delivering a seamless mobile<br/>
experience for retailers and restaurants.<br/>
                </p>
              </div>
              
            </div>
          </div>

          <div class="col-xl-12">
            <div class="ud-single-team wow fadeInUp" data-wow-delay=".1s">
              <div class="ud-team-image-wrapper">
                <div class="ud-team-image">
                  <img src="assets/play/assets/images/about-us/jim.jpg" alt="team" />
                </div>

                <img
                  src="assets/play/assets/images/team/dotted-shape.svg"
                  alt="shape"
                  class="shape shape-1"
                />
                <img
                  src="assets/play/assets/images/team/shape-2.svg"
                  alt="shape"
                  class="shape shape-2"
                />
              </div>
              <div class="ud-team-info">
                <h5>Jimmy Callada</h5>
                <h6>Tech Lead</h6>

                <p>Oversees the development team, ensuring coding standards,<br/> best practices, and alignment with product goals.
                </p>
              </div>
              
            </div>
          </div>

          <div class="col-xl-12">
            <div class="ud-single-team wow fadeInUp" data-wow-delay=".1s">
              <div class="ud-team-image-wrapper">
                <div class="ud-team-image">
                  <img src="assets/play/assets/images/about-us/airra.jpg" style="height:230px" alt="team" />
                </div>

                <img
                  src="assets/play/assets/images/team/dotted-shape.svg"
                  alt="shape"
                  class="shape shape-1"
                />
                <img
                  src="assets/play/assets/images/team/shape-2.svg"
                  alt="shape"
                  class="shape shape-2"
                />
              </div>
              <div class="ud-team-info">
                <h5>Airra Mae Arano </h5>
                <h6>QA & Implementation Lead</h6>

                <p>Ensures the quality and reliability of our<br/>
                applications through testing, while also managing<br/>
                implementation and client support to guarantee
                smooth adoption.
                </p>
              </div>
              
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="ud-blog-details">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="ud-section-title">
              <h2>OUR SERVICES</h2>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="ud-blog-details-image">
              <img
                src="assets/play/assets/images/about-us/consulting.png"
                alt="blog details"
              />
            </div>

            <p style="margin-top:20px">Transform your business with confidence. Our expert digitalization consulting services guide you through every stage of your digital transformation journey—helping you adopt the right technologies, optimize processes, and unlock new growth opportunities. Stay ahead in today’s fast-paced, tech-driven world.</p>
          </div>
        </div>

        <div class="row" style="margin-top:20px">
          <div class="col-lg-12">
            <div class="ud-blog-details-image">
              <img
                src="assets/play/assets/images/about-us/pos.png"
                alt="blog details"
              />
            </div>

            <p style="margin-top:20px">Revolutionize your retail experience with our advanced cloud-based Point of Sale system. Enjoy real-time sales tracking, seamless transactions, and powerful integrations that enhance efficiency while delivering a smooth and personalized customer experience—anytime, anywhere.</p>
          </div>
        </div>

        <div class="row" style="margin-top:20px">
          <div class="col-lg-12">
            <div class="ud-blog-details-image">
              <img
                src="assets/play/assets/images/about-us/erp.png"
                alt="blog details"
              />
            </div>

            <p style="margin-top:20px">Simplify and supercharge your business operations with our intelligent cloud-based ERP solution. Gain full visibility, streamline workflows, and make smarter decisions with real-time data insights. Designed to grow with your business, it ensures maximum efficiency and agility.</p>
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
today—start with the ultimate
POS solution!</h2>
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
