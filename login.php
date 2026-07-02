<?php require_once('includes/header.php'); ?>
		<!-- Main Wrapper -->
		<div class="main-wrapper">
            <div class="login-content">
                <div class="row">
                   <?php require_once('includes/auth-banner.php'); ?>
                    <div class="col-lg-6 login-wrap-bg">
                        <!-- Login -->
                        <div class="login-wrapper">
                            <div class="loginbox">
                                <div class="w-100">
                                    <?php require_once('includes/login-header.php'); ?>
                                    <h1 class="fs-32 fw-bold topic">Sign into Your Account</h1>
                                    <form action="https://dreamslms.dreamstechnologies.com/html/template/instructor-dashboard.html" class="mb-3 pb-3">
                                        <div class="mb-3 position-relative">
                                            <label class="form-label">Registration No.<span class="text-danger ms-1">*</span></label>
                                            <div class="position-relative">
                                                <input type="email" class="form-control form-control-lg">
                                                <span><i class="isax isax-sms input-icon text-gray-7 fs-14"></i></span>
                                            </div>
                                        </div>
                                        <div class="mb-3 position-relative">
                                            <label class="form-label">Password <span class="text-danger ms-1">*</span></label>
                                            <div class="position-relative" id="passwordInput">
                                                <input type="password" class="pass-inputs form-control form-control-lg">
                                                <span class="isax toggle-passwords isax-eye-slash fs-14"></span>
                                            </div>	
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mb-4">
                                            <div class="remember-me d-flex align-items-center">
                                                <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                                                <label class="form-check-label ms-2" for="flexCheckDefault">
                                                    Remember Me
                                                </label>
                                            </div>
                                            <div class="">
                                                <a href="forgot-password.html" class="link-2">
                                                    Forgot Password ?
                                                </a>
                                            </div>
                                        </div>
                                        
                                    </form>

                                 

        
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	 <?php require_once('includes/footer.php'); ?>