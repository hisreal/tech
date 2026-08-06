<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? ($brandName ?? 'School Management System')) ?></title>
<meta name="description" content="<?= e($brandName ?? 'Zionex Solutions') ?> is a modern, all-in-one school management platform for admissions, attendance, results, finance, payroll and communication.">
<link rel="shortcut icon" href="<?= asset('assets/img/logo/school-logo.png') ?>">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">

<link rel="stylesheet" href="<?= asset('assets/css/bootstrap.min.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/plugins/fontawesome/css/all.min.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/plugins/aos/aos.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/plugins/swiper/css/swiper-bundle.min.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/landing.css') ?>">
</head>
<body class="lp-page">

<a href="#mainContent" class="lp-skip-link">Skip to main content</a>

<!-- Page loader -->
<div id="lpLoader" aria-hidden="true"><span class="lp-loader-mark"></span></div>
<!-- Scroll progress indicator -->
<div id="lpScrollProgress" role="presentation"></div>

<!-- ==========================================================================
     Navigation
     ========================================================================== -->
<header class="lp-navbar" id="lpNavbar">
    <div class="container">
        <div class="lp-navbar-inner">
            <a href="#home" class="lp-brand">
                <img src="<?= e($logoUrl ?? asset('assets/img/logo/school-logo.png')) ?>" alt="<?= e($brandName ?? 'School Management System') ?> logo">
                <span><?= e($brandName ?? 'Zionex Solutions') ?></span>
            </a>

            <nav aria-label="Primary">
                <ul class="lp-nav-links">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#solutions">Solutions</a></li>
                    <li><a href="#pricing">Pricing</a></li>
                    <li><a href="#faq">FAQ</a></li>
                    <li><a href="#demo">Contact</a></li>
                </ul>
            </nav>

            <div class="lp-nav-actions">
                <a href="<?= e(url('login.php')) ?>" class="lp-btn lp-btn-outline">Sign In</a>
                <a href="#demo" class="lp-btn lp-btn-primary">Request Demo</a>
                <button type="button" class="lp-nav-toggle" id="lpNavToggle" aria-label="Open menu" aria-expanded="false" aria-controls="lpMobilePanel">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>
    </div>
</header>

<!-- Mobile menu panel -->
<div class="lp-mobile-panel" id="lpMobilePanel">
    <div class="lp-mobile-head">
        <a href="#home" class="lp-brand">
            <img src="<?= e($logoUrl ?? asset('assets/img/logo/school-logo.png')) ?>" alt="">
            <span><?= e($brandName ?? 'Zionex Solutions') ?></span>
        </a>
        <button type="button" class="lp-nav-toggle" id="lpMobileClose" aria-label="Close menu"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <ul>
        <li><a href="#home">Home</a></li>
        <li><a href="#features">Features</a></li>
        <li><a href="#solutions">Solutions</a></li>
        <li><a href="#pricing">Pricing</a></li>
        <li><a href="#faq">FAQ</a></li>
        <li><a href="#demo">Contact</a></li>
        <li><a href="<?= e(url('login.php')) ?>">Sign In</a></li>
    </ul>
    <a href="#demo" class="lp-btn lp-btn-primary lp-btn-block lp-btn-lg mt-4">Request a Demo</a>
</div>

<main id="mainContent">
<!-- ==========================================================================
     1. Hero
     ========================================================================== -->
<section class="lp-hero" id="home">
    <span class="lp-hero-blob lp-blob-1" aria-hidden="true"></span>
    <span class="lp-hero-blob lp-blob-2" aria-hidden="true"></span>
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-12 lp-hero-content" data-aos="fade-up">
                <span class="lp-kicker"><i class="fa-solid fa-sparkles"></i> All-in-one School Management Platform</span>
                <h1>
                    Run Your School's <span class="lp-typed-wrap"><span id="lpTyped">Admissions</span></span><br>
                    on One Powerful Platform
                </h1>
                <p class="lp-hero-sub"><?= e($brandName ?? 'Zionex Solutions') ?> brings admissions, attendance, results, finance, payroll and CBT exams into a single, secure, cloud-based system — built for modern schools that want to move faster and grow with confidence.</p>

                <div class="lp-hero-cta">
                    <a href="#demo" class="lp-btn lp-btn-primary lp-btn-lg"><i class="fa-solid fa-calendar-check"></i> Request a Demo</a>
                    <a href="#features" class="lp-btn lp-btn-outline lp-btn-lg"><i class="fa-solid fa-compass"></i> Explore Features</a>
                </div>

                <div class="lp-trust-badges">
                    <span><i class="fa-solid fa-shield-halved"></i> Bank-level Security</span>
                    <span><i class="fa-solid fa-cloud"></i> 100% Cloud-based</span>
                    <span><i class="fa-solid fa-bolt"></i> 99.9% Uptime</span>
                    <span><i class="fa-solid fa-headset"></i> 24/7 Support</span>
                </div>
            </div>

        
        </div>
    </div>
</section>

<!-- ==========================================================================
     2. Trusted By
     ========================================================================== -->
<section class="lp-trusted-strip">
    <div class="container">
        <p class="lp-trusted-label">Built for every kind of learning institution</p>
        <div class="lp-logo-grid" data-aos="fade-up">
            <span class="lp-logo-chip"><i class="fa-solid fa-school"></i> Private Schools</span>
            <span class="lp-logo-chip"><i class="fa-solid fa-building-columns"></i> Public Schools</span>
            <span class="lp-logo-chip"><i class="fa-solid fa-graduation-cap"></i> Colleges</span>
            <span class="lp-logo-chip"><i class="fa-solid fa-chalkboard"></i> Tutorial Centres</span>
            <span class="lp-logo-chip"><i class="fa-solid fa-people-group"></i> Educational Groups</span>
            <span class="lp-logo-chip"><i class="fa-solid fa-book-open-reader"></i> Online Academies</span>
        </div>
    </div>
</section>

<!-- ==========================================================================
     3. Why Choose Us
     ========================================================================== -->
<section class="lp-section" id="why-us">
    <div class="container">
        <div class="lp-section-head" data-aos="fade-up">
            <span class="lp-kicker"><i class="fa-solid fa-star"></i> Why Choose Us</span>
            <h2>Everything your school needs to run smoothly</h2>
            <p>A platform designed with proprietors, administrators, teachers, and parents in mind — powerful enough for large institutions, simple enough for everyone.</p>
        </div>

        <div class="row g-4">
            <?php
            $whyChoose = [
                ['icon' => 'fa-clock', 'color' => '', 'title' => 'Save Time', 'desc' => 'Automate attendance, grading and fee tracking so staff spend less time on paperwork.'],
                ['icon' => 'fa-shield-halved', 'color' => 'lp-dark', 'title' => 'Secure Platform', 'desc' => 'Role-based access, encrypted data and full audit trails keep school records safe.'],
                ['icon' => 'fa-cloud', 'color' => 'lp-teal', 'title' => 'Cloud Ready', 'desc' => 'Access your school from any device, anywhere — no servers or IT team required.'],
                ['icon' => 'fa-mouse-pointer', 'color' => 'lp-amber', 'title' => 'Easy to Use', 'desc' => 'A clean, intuitive interface that staff and parents can pick up in minutes.'],
                ['icon' => 'fa-sack-dollar', 'color' => 'lp-green', 'title' => 'Affordable', 'desc' => 'Transparent, flexible plans that scale with your school\'s size and budget.'],
                ['icon' => 'fa-arrows-up-down-left-right', 'color' => '', 'title' => 'Scalable', 'desc' => 'From a single campus to multi-branch institutions, the platform grows with you.'],
                ['icon' => 'fa-chart-line', 'color' => 'lp-teal', 'title' => 'Real-time Reports', 'desc' => 'Instant dashboards give leadership visibility into performance and finances.'],
                ['icon' => 'fa-headset', 'color' => 'lp-dark', 'title' => 'Professional Support', 'desc' => 'A dedicated onboarding and support team is with you every step of the way.'],
            ];
            foreach ($whyChoose as $i => $item):
            ?>
            <div class="col-sm-6 col-lg-3">
                <div class="lp-icon-card">
                    <div class="lp-icon-wrap <?= e($item['color']) ?>"><i class="fa-solid <?= e($item['icon']) ?>"></i></div>
                    <h5><?= e($item['title']) ?></h5>
                    <p><?= e($item['desc']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ==========================================================================
     4. Complete Features
     ========================================================================== -->
<section class="lp-section" id="features" style="background:#fff;">
    <div class="container">
        <div class="lp-section-head" data-aos="fade-up">
            <span class="lp-kicker"><i class="fa-solid fa-layer-group"></i> Complete Features</span>
            <h2>One platform. Every school operation.</h2>
            <p>Twelve fully integrated modules replace the spreadsheets, notebooks and disconnected apps schools juggle today.</p>
        </div>

        <div class="row g-4">
            <?php
            $features = [
                ['icon' => 'fa-user-shield', 'title' => 'Administration', 'desc' => 'Centralized control over staff, roles, permissions and school-wide settings.'],
                ['icon' => 'fa-user-graduate', 'title' => 'Student Management', 'desc' => 'Digital student records, enrollment, guardians and academic history.'],
                ['icon' => 'fa-chalkboard-user', 'title' => 'Teacher Management', 'desc' => 'Staff profiles, subject and class assignments, and performance tracking.'],
                ['icon' => 'fa-calendar-check', 'title' => 'Attendance', 'desc' => 'One-tap daily attendance for students and staff, with analytics built in.'],
                ['icon' => 'fa-sack-dollar', 'title' => 'Finance', 'desc' => 'Fee structures, invoicing, payments and receipts, fully automated.'],
                ['icon' => 'fa-money-check-dollar', 'title' => 'Payroll', 'desc' => 'Salary structures, allowances, deductions and payslips in a few clicks.'],
                ['icon' => 'fa-laptop-code', 'title' => 'CBT Exams', 'desc' => 'Computer-based testing with auto-marking and instant results.'],
                ['icon' => 'fa-file-lines', 'title' => 'Results', 'desc' => 'Configurable grading, broadsheets and printable report cards.'],
                ['icon' => 'fa-chart-pie', 'title' => 'Reports', 'desc' => 'Exportable PDF, Excel and CSV reports across every module.'],
                ['icon' => 'fa-table-list', 'title' => 'Timetable', 'desc' => 'Conflict-free class and teacher timetables, built visually.'],
                ['icon' => 'fa-chart-simple', 'title' => 'Analytics', 'desc' => 'Real-time insight into attendance, performance and revenue trends.'],
                ['icon' => 'fa-gears', 'title' => 'School Settings', 'desc' => 'Branding, academic sessions, terms and system preferences.'],
            ];
            foreach ($features as $i => $feature):
            ?>
            <div class="col-sm-6 col-lg-3">
                <div class="lp-feature-card">
                    <div class="lp-icon-wrap"><i class="fa-solid <?= e($feature['icon']) ?>"></i></div>
                    <h6><?= e($feature['title']) ?></h6>
                    <p><?= e($feature['desc']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ==========================================================================
     5. How It Works
     ========================================================================== -->
<section class="lp-section" id="how-it-works">
    <div class="container">
        <div class="lp-section-head" data-aos="fade-up">
            <span class="lp-kicker"><i class="fa-solid fa-route"></i> How It Works</span>
            <h2>From first call to full launch in five simple steps</h2>
            <p>Our onboarding team handles the heavy lifting so your school goes live smoothly, with minimal disruption.</p>
        </div>

        <div class="lp-timeline">
            <?php
            $steps = [
                ['icon' => 'fa-calendar-check', 'title' => '1. Book a Demo', 'desc' => 'Tell us about your school and see the platform in action, tailored to your needs.'],
                ['icon' => 'fa-screwdriver-wrench', 'title' => '2. Setup', 'desc' => 'We configure your sessions, classes, subjects, fees and branding for you.'],
                ['icon' => 'fa-chalkboard-user', 'title' => '3. Training', 'desc' => 'Hands-on training for administrators, teachers, accountants and parents.'],
                ['icon' => 'fa-rocket', 'title' => '4. Launch', 'desc' => 'Go live with your student and staff data fully migrated and verified.'],
                ['icon' => 'fa-headset', 'title' => '5. Ongoing Support', 'desc' => 'A dedicated support team stays with you for every term that follows.'],
            ];
            foreach ($steps as $i => $step):
            ?>
            <div class="lp-timeline-item">
                <div class="lp-timeline-num"><?= $i + 1 ?></div>
                <div class="lp-timeline-card">
                    <h5><i class="fa-solid <?= e($step['icon']) ?>" style="color:var(--lp-primary)"></i> <?= e($step['title']) ?></h5>
                    <p><?= e($step['desc']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>



<!-- ==========================================================================
     7. Benefits Comparison
     ========================================================================== -->
<section class="lp-section">
    <div class="container">
        <div class="lp-section-head" data-aos="fade-up">
            <span class="lp-kicker"><i class="fa-solid fa-scale-balanced"></i> The Difference</span>
            <h2>See what changes when you switch</h2>
            <p>Schools that move to <?= e($brandName ?? 'our platform') ?> replace scattered, manual processes with one connected system.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6" data-aos="fade-right">
                <div class="lp-compare-card lp-without">
                    <div class="lp-compare-head"><i class="fa-solid fa-xmark"></i><h5 class="mb-0">Without <?= e($brandName ?? 'Our Platform') ?></h5></div>
                    <ul class="lp-compare-list">
                        <li><i class="fa-solid fa-circle-xmark"></i> Paper-based records that get lost or damaged</li>
                        <li><i class="fa-solid fa-circle-xmark"></i> Manual fee tracking and handwritten receipts</li>
                        <li><i class="fa-solid fa-circle-xmark"></i> Delayed, error-prone report cards</li>
                        <li><i class="fa-solid fa-circle-xmark"></i> No real-time visibility into school performance</li>
                        <li><i class="fa-solid fa-circle-xmark"></i> Communication scattered across calls and paper notes</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6" data-aos="fade-left">
                <div class="lp-compare-card lp-with">
                    <div class="lp-compare-head"><i class="fa-solid fa-check"></i><h5 class="mb-0">With <?= e($brandName ?? 'Our Platform') ?></h5></div>
                    <ul class="lp-compare-list">
                        <li><i class="fa-solid fa-circle-check"></i> Centralized, secure cloud records for every student</li>
                        <li><i class="fa-solid fa-circle-check"></i> Automated fee tracking with instant digital receipts</li>
                        <li><i class="fa-solid fa-circle-check"></i> Instant, accurate digital report cards</li>
                        <li><i class="fa-solid fa-circle-check"></i> Real-time dashboards and analytics for leadership</li>
                        <li><i class="fa-solid fa-circle-check"></i> One unified hub for staff, student and parent communication</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================================================
     8. Statistics
     ========================================================================== -->
<section class="lp-section lp-stats-section">
    <div class="container">
        <div class="row g-4">
            <?php
            $stats = [
                ['value' => 120, 'suffix' => '+', 'label' => 'Schools Onboarded', 'icon' => 'fa-school'],
                ['value' => 45000, 'suffix' => '+', 'label' => 'Active Students', 'icon' => 'fa-user-graduate'],
                ['value' => 3200, 'suffix' => '+', 'label' => 'Teachers Empowered', 'icon' => 'fa-chalkboard-user'],
                ['value' => 280000, 'suffix' => '+', 'label' => 'Results Generated', 'icon' => 'fa-file-lines'],
                ['value' => 15000, 'suffix' => '+', 'label' => 'Hours Saved Monthly', 'icon' => 'fa-clock'],
            ];
            foreach ($stats as $stat):
            ?>
            <div class="col-6 col-lg">
                <div class="lp-stat-item">
                    <div class="lp-stat-num"><span class="lp-counter" data-count="<?= (int) $stat['value'] ?>">0</span><span><?= e($stat['suffix']) ?></span></div>
                    <div class="lp-stat-label"><?= e($stat['label']) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ==========================================================================
     9. Pricing
     ========================================================================== -->
<section class="lp-section" id="pricing" style="background:#fff;">
    <div class="container">
        <div class="lp-section-head" data-aos="fade-up">
            <span class="lp-kicker"><i class="fa-solid fa-tags"></i> Pricing</span>
            <h2>Plans that grow with your school</h2>
            <p>Every plan includes full access to setup support and training. Talk to us for a quote tailored to your school's size and needs.</p>
        </div>

        <div class="row g-4 align-items-center">
            <div class="col-lg-4">
                <div class="lp-price-card">
                    <h4>Starter</h4>
                    <p class="lp-price-desc">For small schools getting started with digital management.</p>
                    <div class="lp-price-value">Contact for Custom Quote</div>
                    <ul class="lp-price-features">
                        <li><i class="fa-solid fa-circle-check"></i> Up to 300 students</li>
                        <li><i class="fa-solid fa-circle-check"></i> Student & teacher management</li>
                        <li><i class="fa-solid fa-circle-check"></i> Attendance & results</li>
                        <li><i class="fa-solid fa-circle-check"></i> Basic fee collection</li>
                        <li><i class="fa-solid fa-circle-check"></i> Email support</li>
                    </ul>
                    <a href="#demo" class="lp-btn lp-btn-outline lp-btn-block">Request a Demo</a>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="lp-price-card lp-featured">
                    <span class="lp-price-badge">Most Popular</span>
                    <h4>Professional</h4>
                    <p class="lp-price-desc">For growing schools that need the full operational suite.</p>
                    <div class="lp-price-value">Contact for Custom Quote</div>
                    <ul class="lp-price-features">
                        <li><i class="fa-solid fa-circle-check"></i> Up to 2,000 students</li>
                        <li><i class="fa-solid fa-circle-check"></i> Everything in Starter</li>
                        <li><i class="fa-solid fa-circle-check"></i> Payroll & finance suite</li>
                        <li><i class="fa-solid fa-circle-check"></i> CBT exams & analytics</li>
                        <li><i class="fa-solid fa-circle-check"></i> Priority support</li>
                    </ul>
                    <a href="#demo" class="lp-btn lp-btn-primary lp-btn-block">Request a Demo</a>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="lp-price-card">
                    <h4>Enterprise</h4>
                    <p class="lp-price-desc">For multi-branch institutions and large education groups.</p>
                    <div class="lp-price-value">Contact for Custom Quote</div>
                    <ul class="lp-price-features">
                        <li><i class="fa-solid fa-circle-check"></i> Unlimited students</li>
                        <li><i class="fa-solid fa-circle-check"></i> Everything in Professional</li>
                        <li><i class="fa-solid fa-circle-check"></i> Multi-branch management</li>
                        <li><i class="fa-solid fa-circle-check"></i> Custom integrations</li>
                        <li><i class="fa-solid fa-circle-check"></i> Dedicated account manager</li>
                    </ul>
                    <a href="#demo" class="lp-btn lp-btn-outline lp-btn-block">Contact Sales</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================================================
     10. Testimonials
     ========================================================================== -->
<!--<section class="lp-section" id="testimonials">
    <div class="container">
        <div class="lp-section-head" data-aos="fade-up">
            <span class="lp-kicker"><i class="fa-solid fa-quote-left"></i> Testimonials</span>
            <h2>Loved by school leaders</h2>
            <p>Illustrative feedback from the kind of results schools see after switching to a unified platform.</p>
        </div>

        <div class="swiper lpTestimonialSwiper" data-aos="fade-up">
            <div class="swiper-wrapper">
                <?php
                $testimonials = [
                    ['name' => 'Mrs. Adaeze Okonkwo', 'role' => 'Proprietress, Greenfield Academy', 'photo' => 'user-02.jpg', 'quote' => 'We moved from paper registers to full digital records in under two weeks. Attendance and fee tracking that used to take days now happen in minutes.'],
                    ['name' => 'Mr. Tunde Bakare', 'role' => 'Principal, Unity International College', 'photo' => 'user-05.jpg', 'quote' => 'Report cards used to take our staff a full week to prepare. Now results are computed and published automatically the moment scores are entered.'],
                    ['name' => 'Mrs. Grace Iheanacho', 'role' => 'Head Administrator, Sunrise Grammar School', 'photo' => 'user-03.jpg', 'quote' => 'The finance dashboard alone paid for itself. We finally have a real-time view of collections and outstanding fees across every class.'],
                    ['name' => 'Mr. Emeka Nwosu', 'role' => 'Director, Crescent Group of Schools', 'photo' => 'user-08.jpg', 'quote' => 'Rolling out CBT exams across our three campuses was seamless. Teachers and students adapted within a single term.'],
                ];
                foreach ($testimonials as $t):
                ?>
                <div class="swiper-slide">
                    <div class="lp-testimonial-card">
                        <div class="lp-testimonial-stars">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                        </div>
                        <p class="lp-testimonial-quote">&ldquo;<?= e($t['quote']) ?>&rdquo;</p>
                        <div class="lp-testimonial-person">
                            <img src="<?= asset('assets/img/user/' . $t['photo']) ?>" alt="<?= e($t['name']) ?>" loading="lazy">
                            <div>
                                <strong><?= e($t['name']) ?></strong>
                                <span><?= e($t['role']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="lp-swiper-nav">
            <button type="button" id="lpSwiperPrev" aria-label="Previous testimonial"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i></button>
            <button type="button" id="lpSwiperPlayPause" aria-label="Pause testimonial autoplay" aria-pressed="false"><i class="fa-solid fa-pause" aria-hidden="true"></i></button>
            <button type="button" id="lpSwiperNext" aria-label="Next testimonial"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
        </div>
    </div>
</section>-->

<!-- ==========================================================================
     11. FAQ
     ========================================================================== -->
<section class="lp-section" id="faq" style="background:#fff;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="lp-section-head" data-aos="fade-up">
                    <span class="lp-kicker"><i class="fa-solid fa-circle-question"></i> FAQ</span>
                    <h2>Frequently asked questions</h2>
                    <p>Can't find the answer you're looking for? Reach out through the contact form below.</p>
                </div>

                <div class="accordion lp-accordion" id="lpFaqAccordion" data-aos="fade-up">
                    <?php
                    $faqs = [
                        ['q' => 'How long does it take to set up our school?', 'a' => 'Most schools are fully onboarded within one to three weeks, depending on the size of your student and staff records and how much historical data needs migrating.'],
                        ['q' => 'Is our data secure and private?', 'a' => 'Yes. Your school\'s data is encrypted, access is controlled by role-based permissions, and every sensitive action is recorded in a full audit trail.'],
                        ['q' => 'Can we migrate our existing student and staff records?', 'a' => 'Yes. Our onboarding team helps import your existing records from spreadsheets or your previous system as part of setup.'],
                        ['q' => 'Do parents and students get their own access?', 'a' => 'Yes. Students get a personal portal for results, attendance and CBT exams, and parent access can be enabled for fee payments and progress updates.'],
                        ['q' => 'What does the pricing include?', 'a' => 'Every plan includes onboarding, training and support. Pricing is tailored to your school\'s size — request a demo and we\'ll prepare a custom quote.'],
                        ['q' => 'What kind of support do you offer after launch?', 'a' => 'Every school gets a dedicated support channel, with priority support available on the Professional and Enterprise plans.'],
                    ];
                    foreach ($faqs as $i => $faq):
                        $collapseId = 'lpFaq' . $i;
                    ?>
                    <div class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button <?= $i === 0 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>" aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>" aria-controls="<?= $collapseId ?>">
                                <?= e($faq['q']) ?>
                            </button>
                        </h3>
                        <div id="<?= $collapseId ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#lpFaqAccordion">
                            <div class="accordion-body"><?= e($faq['a']) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================================================
     12. Request Demo
     ========================================================================== -->
<section class="lp-section" id="demo">
    <div class="container">
        <div class="lp-section-head" data-aos="fade-up">
            <span class="lp-kicker"><i class="fa-solid fa-paper-plane"></i> Get Started</span>
            <h2>See it in action for your school</h2>
            <p>Tell us a little about your school and our team will reach out to schedule a personalized walkthrough.</p>
        </div>

        <div class="lp-demo-wrap" data-aos="fade-up">
            <div class="row g-0">
                <div class="col-lg-5">
                    <div class="lp-demo-info">
                        <h3>What to expect</h3>
                        <p>A short, no-pressure call where we walk through the platform using examples from your own school.</p>
                        <ul>
                            <li><i class="fa-solid fa-circle-check"></i> 30-minute guided walkthrough</li>
                            <li><i class="fa-solid fa-circle-check"></i> Tailored to your school type & size</li>
                            <li><i class="fa-solid fa-circle-check"></i> No cost, no obligation</li>
                            <li><i class="fa-solid fa-circle-check"></i> Custom pricing quote included</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="lp-demo-form">
                        <form id="lpDemoForm" novalidate>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="lp-form-label" for="lpSchoolName">School Name</label>
                                    <input type="text" class="lp-form-control" id="lpSchoolName" name="school_name" placeholder="e.g. Greenfield Academy" aria-describedby="lpErrSchoolName" required>
                                    <span class="lp-form-error" id="lpErrSchoolName" data-error-for="school_name" role="alert">Please enter your school's name.</span>
                                </div>
                                <div class="col-md-6">
                                    <label class="lp-form-label" for="lpContactPerson">Contact Person</label>
                                    <input type="text" class="lp-form-control" id="lpContactPerson" name="contact_person" placeholder="Your full name" aria-describedby="lpErrContactPerson" required>
                                    <span class="lp-form-error" id="lpErrContactPerson" data-error-for="contact_person" role="alert">Please enter your name.</span>
                                </div>
                                <div class="col-md-6">
                                    <label class="lp-form-label" for="lpPhone">Phone</label>
                                    <input type="tel" class="lp-form-control" id="lpPhone" name="phone" placeholder="080X XXX XXXX" aria-describedby="lpErrPhone" required>
                                    <span class="lp-form-error" id="lpErrPhone" data-error-for="phone" role="alert">Please enter a valid phone number.</span>
                                </div>
                                <div class="col-md-6">
                                    <label class="lp-form-label" for="lpEmail">Email</label>
                                    <input type="email" class="lp-form-control" id="lpEmail" name="email" placeholder="you@school.edu" aria-describedby="lpErrEmail" required>
                                    <span class="lp-form-error" id="lpErrEmail" data-error-for="email" role="alert">Please enter a valid email address.</span>
                                </div>
                                <div class="col-md-6">
                                    <label class="lp-form-label" for="lpSchoolType">School Type</label>
                                    <select class="lp-form-control" id="lpSchoolType" name="school_type" aria-describedby="lpErrSchoolType" required>
                                        <option value="">Select type</option>
                                        <option value="private">Private School</option>
                                        <option value="public">Public School</option>
                                        <option value="college">College</option>
                                        <option value="tutorial">Tutorial Centre</option>
                                        <option value="group">Educational Group</option>
                                    </select>
                                    <span class="lp-form-error" id="lpErrSchoolType" data-error-for="school_type" role="alert">Please select a school type.</span>
                                </div>
                                <div class="col-md-6">
                                    <label class="lp-form-label" for="lpStudentPopulation">Student Population</label>
                                    <select class="lp-form-control" id="lpStudentPopulation" name="student_population" aria-describedby="lpErrStudentPopulation" required>
                                        <option value="">Select range</option>
                                        <option value="under-100">Under 100</option>
                                        <option value="100-500">100 - 500</option>
                                        <option value="500-2000">500 - 2,000</option>
                                        <option value="2000-plus">2,000+</option>
                                    </select>
                                    <span class="lp-form-error" id="lpErrStudentPopulation" data-error-for="student_population" role="alert">Please select your student population.</span>
                                </div>
                                <div class="col-12">
                                    <label class="lp-form-label" for="lpMessage">Message <span style="font-weight:500;color:var(--lp-muted)">(optional)</span></label>
                                    <textarea class="lp-form-control" id="lpMessage" name="message" rows="3" placeholder="Tell us anything else that would help us prepare for the demo"></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="lp-btn lp-btn-primary lp-btn-lg lp-btn-block"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Request My Demo</button>
                                    <p class="lp-form-reassurance"><i class="fa-solid fa-lock" aria-hidden="true"></i> Your details are only used to schedule your demo — no spam, ever.</p>
                                </div>
                            </div>
                        </form>
                        <div id="lpDemoSuccess" role="status" aria-live="polite">
                            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                            <h4>Request received!</h4>
                            <p class="mb-0">Thank you — our team will reach out within one business day to schedule your walkthrough.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================================================
     13. Final CTA
     ========================================================================== -->
<section class="lp-section-tight">
    <div class="container">
        <div class="lp-final-cta" data-aos="zoom-in">
            <h2>Ready to transform how your school runs?</h2>
            <p>Join the schools already saving hours every week with a single, connected platform for everything from admissions to results.</p>
            <a href="#demo" class="lp-btn lp-btn-primary lp-btn-lg"><i class="fa-solid fa-calendar-check"></i> Request a Demo</a>
        </div>
    </div>
</section>
</main>

<!-- ==========================================================================
     Footer
     ========================================================================== -->
<footer class="lp-footer" id="footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="lp-footer-brand">
                    <img src="<?= e($logoUrl ?? asset('assets/img/logo/school-logo.png')) ?>" alt="">
                    <strong><?= e($brandName ?? 'Zionex Solutions') ?></strong>
                </div>
                <p>An all-in-one school management platform for admissions, attendance, results, finance, payroll and communication.</p>
                <div class="lp-social">
                    <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" aria-label="Twitter / X"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <h6>Quick Links</h6>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#pricing">Pricing</a></li>
                    <li><a href="#faq">FAQ</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2">
                <h6>Solutions</h6>
                <ul>
                    <li><a href="#solutions">Admin Dashboard</a></li>
                    <li><a href="#solutions">Teacher Portal</a></li>
                    <li><a href="#solutions">Student Portal</a></li>
                    <li><a href="#solutions">Accountant Suite</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2">
                <h6>Resources</h6>
                <ul>
                    <li><a href="#how-it-works">How It Works</a></li>
                    <li><a href="#testimonials">Testimonials</a></li>
                    <li><a href="#demo">Request Demo</a></li>
                    <li><a href="<?= e(url('login.php')) ?>">Sign In</a></li>
                </ul>
            </div>

            <div class="col-lg-2">
                <h6>Newsletter</h6>
                <p>Product updates, occasionally.</p>
                <form class="lp-newsletter" id="lpNewsletterForm">
                    <input type="email" placeholder="Your email" aria-label="Email for newsletter" required>
                    <button type="submit">Subscribe</button>
                </form>
            </div>
        </div>

        <div class="lp-footer-bottom">
            <span>&copy; <?= date('Y') ?> <?= e($brandName ?? 'Zionex Solutions') ?>. All rights reserved.</span>
            <span>
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
            </span>
        </div>
    </div>
</footer>

<?php if (!empty($whatsappNumber)): ?>
    <a href="https://wa.me/2349139298009?text=<?= rawurlencode('Hi, I would like to know more about ' . ($brandName ?? 'your school management platform') . '.') ?>" class="lp-whatsapp-btn" id="lpWhatsapp" target="_blank" rel="noopener" aria-label="Chat with us on WhatsApp">
        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
    </a>
<?php endif; ?>
<button type="button" id="lpBackToTop" aria-label="Back to top"><i class="fa-solid fa-arrow-up"></i></button>

<script src="<?= asset('assets/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= asset('assets/plugins/aos/aos.js') ?>"></script>
<script src="<?= asset('assets/plugins/swiper/js/swiper-bundle.min.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/typed.js@2.1.0/dist/typed.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/countup.js@2.8.0/dist/countUp.umd.js"></script>
<script src="<?= asset('assets/js/landing.js') ?>"></script>
</body>
</html>
