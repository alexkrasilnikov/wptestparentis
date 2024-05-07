<?php
/**
 *Template Name: Блог
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Tutor_Starter
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="wrapper">
<div class="container">

<main class="main-wrapper">
         <section class="blog-page" id="blog-page">
            <div class="section-heading">
               <div class="container">
                  <nav aria-label="Page breadcrumb">
                     <ul class="breadcrumb">
                        <li><a href="<?php echo home_url(); ?>" class="breadcrumb-item" aria-current="page">Главаня</a></li>
                        <li><a href="<?php echo home_url();?>/блог" class="breadcrumb-item current-page">Блог</a></li>
                     </ul>
                  </nav>
                  <div class="section-info">
							<?php 
							$current_category = single_cat_title('', false);
							if (empty($current_category)) {
								$page_title = 'Блог';
						  } else {
								$page_title = $current_category;
						  }
						  ?>
							
                     <h1 class="section-title"><?php echo $page_title;?></h1>
                     <div class="section-description">
                        <p>
                           <?php the_excerpt(); ?>
                        </p>
                     </div>
                  </div>
                  <div class="category-list">
                     
							<?php
							$categories = get_categories(); // Получаем список всех категорий

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
               </div>
            </div>
            <div class="section-body section-body__blog">
               <div class="container">
                  <div class="blog-list">
							<?php 
								$args = array(
									'post_type'      => 'post',
									'posts_per_page' => 2,
									'paged' => get_query_var('paged') ? get_query_var('paged') : 1 // Для пагинации

							);
							$post_query = new WP_Query( $args );
							if ( $post_query->have_posts() ) {	
							while ($post_query->have_posts() ) :

								$post_query->the_post();
								$post_id = get_the_ID()
								?>
	
							<div class="blog-item">
                        <div class="blog-item__meta">
									<?php 
									  $categories = get_the_category($post_id);
									  if ($categories) {
										
										foreach ($categories as $category) { ?>
											<div class="blog-item__category"><?php echo $category->name;?></div>											
									<?php	}
									
								  }
									?>
                           
                           <div class="blog-item__date post-date"><?php     echo get_the_date();?></div>
                        </div>
                        <div class="blog-item__info">
                           <h3 class="blog-item__title">
                              <?php echo get_the_title(); ?>
                           </h3>
                           <div class="blog-item__description">
									<?php the_excerpt(); ?>
                           </div>
                           <div class="blog-item__meta post-meta">
                              <div class="post-meta__item">
                                 <img src="<?php echo get_template_directory_uri(  ); ?>/img/icons/mdi_heart.svg" alt="Likes">
                                 <span>22</span>
                              </div>
                              <div class="post-meta__item">
                                 <img src="<?php echo get_template_directory_uri(  ); ?>/img/icons/fa6-solid_message.svg" alt="Likes">
                                 <span>8</span>
                              </div>
                           </div>
							      <a href="<?php echo get_permalink() ?>" class="blog-item__image">
										<img src="<?php echo get_the_post_thumbnail_url();?>" alt="img">
										<div class="blog-item__overlay">
                                 <div  class="blog-item__link"></div>
                              </div>							
                           </a>
                        </div>
                     </div>
	
						 <?php endwhile; ?>
						
						 </div>
						 <div class="custom-pagination">
								<?php
									$big = 999999999; // need an unlikely integer

									$pagination_args = array(
										'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
											'format' => '?paged=%#%',
											'current' => max(1, get_query_var('paged')),
											'total' => $post_query->max_num_pages,
											'prev_text' => '<div class="pagging__arrow pagging__arrow-right"><img src="' .  esc_url(get_template_directory_uri(  )) . '/assets/img/icons/Vector.svg"></div>',
											'next_text' => '<div class="pagging__arrow pagging__arrow-right"><img src="' .  esc_url(get_template_directory_uri(  )) . '/assets/img/icons/noun-right-3678530 1.svg"></div>',
											'mid_size' => 2, // Определяет количество чисел с обеих сторон текущей страницы
											'add_args' => false, // отключаем добавление аргументов к ссылкам
											'before_page_number' => '<span class="page-number">', // начало разметки номера страницы
											'after_page_number' => '</span>', // конец разметки номера страницы
											'prev_next' => true 
									);
										// Показываем кнопку "назад" всегда
										if ($pagination_args['current'] < 2) {
											echo get_previous_posts_link($pagination_args['prev_text']);
									}
							
									// Выводим пагинацию
									echo paginate_links($pagination_args);
								?>
				
						


						</div>
						<?php } else {
					
							get_template_part( 'views/content', 'none' ); ?>
						</div>
						<?php  }
					  ?>
                    
 
                 
               </div>
            </div>
         </section>
      </main>

</div><!-- .container -->
</div>


<?php
get_footer();
