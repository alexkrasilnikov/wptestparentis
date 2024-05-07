<?php
/*
Template Name: Тарифы
*/
?>

<?php get_header(); ?>
<div class="container">
<?php
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