<?php
$publicBasePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$pageTitleText = isset($pageTitle) && $pageTitle !== ''
    ? htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') . ' | IdeaHub'
    : 'IdeaHub | Connect Ideas with Builders';
?>
<!DOCTYPE html>
<html lang="zxx" id="pageroot">


<head>
    <meta charset="UTF-8">
    <meta name="author" content="Softnio">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="IdeaHub connects creators, developers, founders, and investors around ideas worth building.">
    <!-- Fav Icon  -->
    <link rel="shortcut icon" href="assets/images/Ideahub.png">
    <title><?= $pageTitleText ?></title>
    <link rel="stylesheet" href="assets/css/appb183.css?v120">
</head>



<header class="nk-header" id="home">
            <div class="nk-menubar js-menubar-fixed is-transparent" id="menu-bar" data-gsap-in='{"opacity": 0, "y": 50}' data-gsap-delay="3.3">
                <div class="nk-menu-background"></div><!-- .nk-menu-background -->
                <div class="container">
                    <div class="nk-menubar-warper">
                        <a class="nk-logo" href="<?= $publicBasePath ?>/home">
                            <img src="assets/images/Ideahub.png"  alt="logo" />
                        </a><!-- .nk-logo -->
                        <div class="nk-menu-toggle d-xl-none">
                            <button data-nk-target="menu-bar" data-nk-overlay="menu-bar-overlay" class="js-menu-toggle icon-switch btn btn-icon btn-glow">
                                <em class="icon icon-inactive ni ni-menu"></em>
                                <em class="icon icon-active ni ni-cross"></em>
                            </button><!-- .js-menu-toggle -->
                        </div>
                        <div class="nk-menu">
                            <ul class="nk-menu-list">
                                <li class="nk-menu-item ">
                                    <a href="<?= $publicBasePath ?>/home" class="nk-menu-link"> <span>Home</span> </a>
                                </li>
                                <li class="nk-menu-item ">
                                    <a href="<?= $publicBasePath ?>/about" class="nk-menu-link"> <span>About</span> </a>
                                </li>
                                <li class="nk-menu-item ">
                                    <a href="<?= $publicBasePath ?>/faqs" class="nk-menu-link"> <span>faq</span> </a>
                                </li>
                               
                                <!--li class="nk-menu-item ">
                                    <a href="pricing" class="nk-menu-link"> <span>Pricing</span> </a>
                                </li-->
                                <li class="nk-menu-item ">
                                    <a href="<?= $publicBasePath ?>/contact" class="nk-menu-link"> <span>Contact</span> </a>
                                </li>
                            </ul><!-- .nk-menu-list -->
                            <ul class="nk-menu-tools flex-xl-row-reverse column-gap-6">
                                <li>
                                            <a  href="<?= $publicBasePath ?>/signup" class="btn btn-line-animated btn-glow-hover btn-pill btn-lg" data-cursor="click">
                                                <span class="text-gradient-heading angle-0">Get Started </span>

                                                <div class="beam-container">
                                                    <div class="beam-slide">
                                                        <div class="beam-conic"></div>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                <li> <a href="<?= $publicBasePath ?>/signin" class="link link-content fw-normal link-hover-title"> Login </a> </li>
                            </ul><!-- .nk-menu-tools -->
                        </div><!-- .nk-menu -->
                        <div id="menu-bar-overlay" class="nk-menu-overlay"></div><!-- .nk-menu-overlay -->
                    </div><!-- .nk-menubar-warper -->
                </div> <!-- .container -->
            </div> <!-- .nk-menubar -->
            <div class="nk-hero">
                <div class="nk-hero-content pt-7 pt-lg-27">
                    <div class="container has-mask">
                        <div data-gsap-in='{"opacity": 0}' data-gsap-delay="3.7" class="bg-pattern-grid-curbed bg-mask mt-sm-55p mt-md-35p mt-lg-30p mt-xl-25p mx-sm-n12 mx-md-n5 mx-xl-n4 d-none d-sm-block"></div>
                        <div data-gsap-in='{"opacity": 0, "y": 200}' data-gsap-delay="4" class="bg-glow-g bg-mask mt-sm-55p mt-md-35p mt-lg-30p mt-xl-25p mx-sm-n12 mx-md-n5 mx-xl-n4 d-none d-sm-block"></div>
                        <div class="row gy-sm-9 gy-lg-14 gy-xl-23 justify-content-center">
                            <div class="col-xxl-8 col-xl-9 col-lg-11 col-md-10">
                                <div class="pb-10 text-center lg:px-8">
                                    <div data-gsap-in='{"opacity": 0, "y": 50}' data-gsap-delay="3.3">
                                        <div class="badge mb-2 border-lighter border-opacity-60 rounded-pill">
                                            <em class="icon ni ni-spark text-gradient-a"></em>
                                            <span class="text-gradient-a">Track  Ideas in seconds</span>
                                        </div>
                                    </div>
                                    <h1 data-gsap-in='{"opacity": 0, "y": 50}' data-gsap-delay="3.4" class="mb-4 text-gradient-heading display-3 fw-semibold w-max mx-auto mw-100">Say hello to IdeaHub</h1>
                                    <p data-gsap-in='{"opacity": 0, "y": 50}' data-gsap-delay="3.5" class="lead px-lg-11 text-pretty"><strong> Idea Deserves More Than a Notebook.</strong> IdeaHub is Where raw ideas meet the people who build them. post your idea. Get Discoverd. Make it real</p>
                                    <ul data-gsap-in='{"opacity": 0, "y": 50}' data-gsap-delay="3.6" class="nk-list-inline justify-content-center mt-5">
                                        <li>
                                            <a  href="signup" class="btn btn-line-animated btn-glow-hover btn-pill btn-lg" data-cursor="click">
                                                <span class="text-gradient-heading angle-0">Browse Ideas </span>

                                                <div class="beam-container">
                                                    <div class="beam-slide">
                                                        <div class="beam-conic"></div>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-12">
                                <div data-gsap-in='{"opacity": 0, "y": 50}' data-gsap-delay="3.6">
                                    <div data-gsap-in='{"rotateX": "40"}' data-gsap-perspective="1000px" data-gsap-scrub="true" class="card p-3 p-sm-4 rounded-top-8 border border-muted border-opacity-90 bg-lighter bg-opacity-80">
                                        <div class="card-overlay-blend card-overlay-blend-bottom"></div>
                                        <img class="rounded-top-6 border border-light border-opacity-50" src="images/shortener/screen-hero1.png" alt="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="nk-hero-footer py-10">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-xl-10">
                                <ul class="nk-list-inline justify-content-center justify-content-lg-around row-gap-2 column-gap-8" data-gsap-in='{"opacity": 0, "y": 50}' data-gsap-delay=".3">
                                    <li> <img src="images/partner/paypal.png" srcSet="images/partner/paypal2x.png 2x" alt=""> </li>
                                    <li> <img src="images/partner/stripe.png" srcSet="images/partner/stripe2x.png 2x" alt=""> </li>
                                    <li> <img src="images/partner/node.png" srcSet="images/partner/node2x.png 2x" alt=""> </li>
                                    <li> <img src="images/partner/php.png" srcSet="images/partner/php2x.png 2x" alt=""> </li>
                                    <li> <img src="images/partner/ebay.png" srcSet="images/partner/ebay2x.png 2x" alt=""> </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
