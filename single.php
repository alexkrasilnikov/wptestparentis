<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Tutor_Starter
 */

defined( 'ABSPATH' ) || exit;

get_header();
$post_id = get_the_ID()
?>

<main class="main-wrapper">
         <section class="blog-single" id="blog-single">
            <div class="section-heading">
               <div class="container">
                  <nav aria-label="Page breadcrumb">
                     <ul class="breadcrumb">
								<li><a href="<?php echo home_url(); ?>" class="breadcrumb-item" aria-current="page">Главаня</a></li>
                        <li><a href="<?php echo home_url();?>/блог" class="breadcrumb-item">Блог</a></li>
								<li><a href="<?php echo get_permalink() ?>" class="breadcrumb-item current-page"><?php echo get_the_title(); ?></a></li>
                     </ul>
                  </nav>
                  <div class="section-info">
                     <h1 class="section-title"><?php echo get_the_title(); ?></h1>
                     <!--<div class="section-description">
                        <p>
                           Lorem ipsum dolor sit amet, consectetur adipiscing elit orem ipsum dolor sit amet,
                           consectetur adipiscing
                        </p>
                     </div>-->
                  </div>
                  <div class="blog-single__meta">
                     <div class="category-list">
							<?php
							$categories = get_the_category($post_id);; // Получаем список всех категорий

							if ( $categories ) {
								foreach ( $categories as $category ) {
									$category_link = get_category_link( $category->term_id ); // Получаем URL категории
									?>
									<div class="category-list__item">
										<a class="category-list__link" href="<?php echo esc_url( $category_link ); ?>"><?php echo esc_html( $category->name ); ?></a>
									</div>	
									<?php									
								}
							}
							?>                      
                     </div>
                     <div class="post-meta">
                        <div class="post-meta__item">
                           <img src="<?php echo get_template_directory_uri(  ); ?>/img/icons/mdi_heart.svg" alt="Likes">
                           <span>22</span>
                        </div>
                        <div class="post-meta__item">
                           <img src="<?php echo get_template_directory_uri(  ); ?>/img/icons/fa6-solid_message.svg" alt="Likes">
                           <span>8</span>
                        </div>
                     </div>
                     <div class="blog-single__date post-date"><?php echo get_the_date();?></div>
                  </div>
               </div>
            </div>
            <div class="post-content">
               <div class="container">
						<div class="post-image">
							<img src="<?php echo get_the_post_thumbnail_url(null, 'large');?>" alt="img">
						</div>
                  <div class="post-content__wrapper">
                     <p>
                        <?php the_content() ?>
                     </p>
                  </div>
               </div>
            </div>
         </section>
      </main>
<?php
get_footer();
