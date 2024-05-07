<?php
/**
 *Template Name: Товары
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

 
 defined( 'ABSPATH' ) || exit;
 
 get_header( 'shop' );?>
 <div class="container">
 <?php
 /**
  * Hook: woocommerce_before_main_content.
  *
  * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
  * @hooked woocommerce_breadcrumb - 20
  * @hooked WC_Structured_Data::generate_website_data() - 30
  */
 do_action( 'woocommerce_before_main_content' );
 
 /**
  * Hook: woocommerce_shop_loop_header.
  *
  * @since 8.6.0
  *
  * @hooked woocommerce_product_taxonomy_archive_header - 10
  */
 do_action( 'woocommerce_shop_loop_header' );
 
 // Параметры запроса для поиска товаров из категории с ID 107
 $args = array(
	  'post_type'      => 'product',
	  'posts_per_page' => -1,
	  'tax_query'      => array(
			array(
				 'taxonomy' => 'product_cat',
				 'field'    => 'term_id',
				 'operator' => 'NOT IN',
				 'terms'    => 107, // ID вашей категории тарифов
			),
	  ),
 );
 
 // Создаем новый запрос WP_Query
 $products_query = new WP_Query( $args );
 
 // Проверяем, есть ли товары в категории
 if ( $products_query->have_posts() ) {
 
	  /**
		* Hook: woocommerce_before_shop_loop.
		*
		* @hooked woocommerce_output_all_notices - 10
		* @hooked woocommerce_result_count - 20
		* @hooked woocommerce_catalog_ordering - 30
		*/
	  do_action( 'woocommerce_before_shop_loop' );
 
	  woocommerce_product_loop_start();
 
	  // Запускаем цикл товаров
	  while ( $products_query->have_posts() ) {
			$products_query->the_post();
 
			// Выводим содержимое товара
			wc_get_template_part( 'content', 'product' );
	  }
 
	  woocommerce_product_loop_end();
 
	  /**
		* Hook: woocommerce_after_shop_loop.
		*
		* @hooked woocommerce_pagination - 10
		*/
	  do_action( 'woocommerce_after_shop_loop' );
 } else {
	  /**
		* Hook: woocommerce_no_products_found.
		*
		* @hooked wc_no_products_found - 10
		*/
	  do_action( 'woocommerce_no_products_found' );
 }
 
 // Сбрасываем запрос
 wp_reset_postdata();
 
 /**
  * Hook: woocommerce_after_main_content.
  *
  * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
  */
 do_action( 'woocommerce_after_main_content' );
 
 /**
  * Hook: woocommerce_sidebar.
  *
  * @hooked woocommerce_get_sidebar - 10
  */
 do_action( 'woocommerce_sidebar' );?>
 </div>
 <?php
 get_footer( 'shop' );