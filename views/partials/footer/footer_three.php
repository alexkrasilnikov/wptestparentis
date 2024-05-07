<?php
/**
 * Footer three 4 columns extended
 */
?>
<?php
    $page_meta         = get_post_meta( get_the_ID(), '_tutorstarter_page_metadata', true );
	$selected_footer   = ( ! empty( $page_meta['footer_select'] ) ? $page_meta['footer_select'] : '' );
	$footer_style      = get_theme_mod( 'footer_type_select' );
?>
<div class="footer-back">
<section class="footer-widgets">
	<div class="container">
		<div class="row justify-between align-top">
		<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
				<?php is_active_sidebar( 'tutorstarter-footer-widget4' ) ? dynamic_sidebar( 'tutorstarter-footer-widget4' ) : null; ?>
				<div class="left-footer-block">
					<h3 class="left-footer-block__title">Советы экспертов, обновления и бонусы для вашего почтового ящика.</h3>
					<form action="" class="left-footer-block__form">
						<input type="text" class="left-footer-block__form-input" placeholder="example@mail.com">
						<button class="left-footer-block__form-btn">Подписаться</button>
					</form>
					<div class="left-footer-block__socials">
						<a href="#" class="socials-item">
						<img src="<?php echo get_template_directory_uri(); ?>/assets/img/faceboock.png" alt="faceboock">
						</a>
						<a href="#" class="socials-item">
						<img src="<?php echo get_template_directory_uri(); ?>/assets/img/mingcute_youtube-fill.png" alt="youtube">
						</a>
						<a href="#" class="socials-item">
						<img src="<?php echo get_template_directory_uri(); ?>/assets/img/ri_instagram-fill.png" alt="instagramm">
						</a>
					</div>
				</div>
			</div><!-- right widget container -->
			<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12 ml-0 pl-0">
				
				<div class="row align-top">
					<div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-xs-12">
					<?php if ( 'footer_five' !== $selected_footer || 'footer_five' !== $footer_style ) : ?>
						<?php tutorstarter_footer_logo();
					endif; ?>
						<?php is_active_sidebar( 'tutorstarter-footer-widget1' ) ? dynamic_sidebar( 'tutorstarter-footer-widget1' ) : null; ?>
					</div>
					<div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-xs-12">
						<?php is_active_sidebar( 'tutorstarter-footer-widget2' ) ? dynamic_sidebar( 'tutorstarter-footer-widget2' ) : null; ?>
					</div>
					<div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-xs-12">
						<?php is_active_sidebar( 'tutorstarter-footer-widget3' ) ? dynamic_sidebar( 'tutorstarter-footer-widget3' ) : null; ?>
					</div>
				</div>
			</div><!-- left widget container-->

		</div><!-- .row -->
	</div><!-- .container -->
</section><!-- .footer-widgets -->
