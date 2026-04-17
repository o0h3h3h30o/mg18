
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Manga18.club - Login</title>
	<!-- font -->
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="stylesheet" href="/vendor/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="/manga-club/ti-icons/css/themify-icons.css">
	<!-- Lib css-->
	<link rel="stylesheet" href="/vendor/css/bootstrap-3.3.7.min.css">
	<link rel="stylesheet" href="/vendor/css/owl.carousel.min.css">
	<link rel="stylesheet" href="/vendor/css/owl.theme.default.css">
	<link rel='stylesheet' href='/manga-club/css/animate.css'>
	<link rel="stylesheet" href="/vendor/css/jquery.fancybox.min.css" />
	<!-- custom css -->
	<link rel="stylesheet" href="/manga-club/css/custom.css?v=<?=time()?>" />
	<link rel="stylesheet" href="/manga-club/css/responsive.css" />
	<!-- jquery -->
	<script src="/vendor/js/jquery-2.2.3.min.js"></script>
</head>
<body>
	<!-- Login -->
	<div class="iedu_login">
		<div class="container">
			<div class="iedu_login__content">
				<div class="login_head">
					<div class="logo">
						<a href="<?=base_url();?>"><img src="<?=base_url()?>manga18.png" alt="" class="img-responsive"></a>
					</div>
					<div class="login_regis">
						<a href="<?=base_url();?>register">Sign Up</a>
					</div>
				</div>
				<div class="login_info">
					<div class="row">
						<div class="col-md-6 col-sm-6 col-xs-12">
							<div class="login_form">
								
								<p>Read Manhwa Manga 18+ For Free</p>
								
								<div class="login_block">
									<h4>Login</h4>
									<form action="<?=base_url();?>checkLogin" method="POST">
									<?= csrf_field() ?>
										<?php if(isset($message_type)){ ?>
										<div class="alert alert-danger"><i class="fa fa-times"></i> <span><?= $message; ?></span></div>
										<?php } ?>								
										<div class="login_form__content">
											<div class="form_item">
												<input type="text" placeholder="Email or Username" name="username">
											</div>
											<div class="form_item">
												<input type="password" placeholder="Password" name="password">
											</div>
											<?php if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'])): ?>
											<script src="https://www.google.com/recaptcha/api.js" async defer></script>
											<div class="g-recaptcha" data-sitekey="<?=$recaptcha_publickey;?>"></div>
											<br>
											<?php endif; ?>
										</div>
										<div class="form_item" style="margin-bottom:10px;">
											<label><input type="checkbox" name="remember_me" value="1"> Remember me for 7 days</label>
										</div>
										<div class="login_form__button">
											<button type="submit">Login</button>
											<a href="#">Forgot Password?</a>
										</div>
									</form>
								</div>
							</div>
						</div>
						<div class="col-md-6 col-sm-6 hidden-xs">
							<div class="login_images">
								<img src="img/login_images.png" alt="" class="img-responsive">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- End Login -->
	<!-- Lib js -->
	<script src="/vendor/js/jquery.fancybox.min.js"></script>
	<script src="/vendor/js/bootstrap-3.3.7.min.js"></script>
	<script src="/vendor/js/owl.carousel.min.js"></script>
	<script src="/vendor/js/wow.min.js"></script>
	<!-- Custom js -->
	<script type="text/javascript" src="<?=base_url();?>js/custom.js"></script>
</body>
</html>