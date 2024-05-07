<?php
/*
Template Name: Тарифы
*/
?>

<?php get_header(); ?>
<div class="container">

<?php

$current_user = wp_get_current_user();
$subscriptions = wcs_get_users_subscriptions( $current_user->ID, array( 'subscriptions_per_page' => -1 ) );
if($subscriptions){
	foreach ( $subscriptions as $subscription ) {
		if ( $subscription->has_status( 'active' ) ) {
			
			$order_id= $subscription->get_parent_id();
				$order = wc_get_order( $order_id );
				$product_id = '';
				if ( $order ) {
				// Получаем массив объектов товаров из заказа
					$items = $order->get_items();
					foreach ( $items as $item ) {
						$product_id = $item->get_product_id();
				
				}
			}

			 echo 'Текущая подписка: ' . get_the_title($product_id) . '<br>';			 
			 echo 'Статус подписки: ' . $subscription->get_status() . '<br>';
			 echo 'Окончание подписки: ' . $subscription->get_date( 'end' ) . '<br>';
		} 
	  }
  
}else {
	echo '<span>нет активной подписки</span>';
 }

	

// Параметры запроса для поиска товаров из категории с ID 107
$args = array(
    'post_type'      => 'product',
    'posts_per_page' => -1,
    'tax_query'      => array(
        array(
            'taxonomy' => 'product_cat',
            'field'    => 'term_id',
            'terms'    => 107, // ID вашей категории тарифов
        ),
    ),
);

// Создаем новый запрос WP_Query
$products_query = new WP_Query( $args );

// Проверяем, есть ли товары в категории
if ( $products_query->have_posts() ) {

    // Выводим заголовок категории
    woocommerce_product_loop_start();

    // Запускаем цикл товаров
    while ( $products_query->have_posts() ) {
        $products_query->the_post();

        // Выводим содержимое товара
        wc_get_template_part( 'content', 'product' );
    }

    // Завершаем вывод товаров
    woocommerce_product_loop_end();

} else {
    // Если товаров нет, выводим сообщение
    echo '<p class="woocommerce-info">Товары в данной категории не найдены.</p>';
}

// Сбрасываем запрос
wp_reset_postdata(); ?>
</div>
<?php
// Загружаем подвал сайта
get_footer();
?>