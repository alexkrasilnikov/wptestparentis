<?php
/**
 * Handles loading all the necessary files
 *
 * @package Tutor_Starter
 */

defined( 'ABSPATH' ) || exit;

// Content width.
if ( ! isset( $content_width ) ) {
	$content_width = apply_filters( 'tutorstarter_content_width', get_theme_mod( 'content_width_value', 1140 ) );
}

// Theme GLOBALS.
$theme = wp_get_theme();
define( 'TUTOR_STARTER_VERSION', $theme->get( 'Version' ) );

// Load autoloader.
if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) :
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
endif;

// Include TGMPA class.
if ( file_exists( dirname( __FILE__ ) . '/inc/Custom/class-tgm-plugin-activation.php' ) ) :
	require_once dirname( __FILE__ ) . '/inc/Custom/class-tgm-plugin-activation.php';
endif;

// Register services.
if ( class_exists( 'Tutor_Starter\\Init' ) ) :
	Tutor_Starter\Init::register_services();
endif;



function custom_scripts()
{
    wp_enqueue_style('main-css', get_template_directory_uri() . '/assets/css/main.css');
	 wp_enqueue_style('font-gilroy-css', get_template_directory_uri() . '/assets/css/font-gilroy.css');
}

add_action('wp_enqueue_scripts', 'custom_scripts');


//---///

function restrict_cart_contents($passed, $product_id, $quantity) {
	// Получаем текущую корзину
	
	$cart = WC()->cart->get_cart();
	
	// Инициализируем переменные для проверки продуктов и подписок
	$product_in_cart = false;
	$subscription_in_cart = false;

	// Проверяем содержимое корзины
	foreach ($cart as $cart_item_key => $cart_item) {
		 if ($cart_item['product_id'] === $product_id) {
			  // Если продукт уже есть в корзине, запрещаем добавление
			  wc_add_notice(('Этот товар уже есть в вашей корзине.'), 'error');
			  return false;
		 }
		 foreach ( $cart as $cart_item_key => $cart_item ) {
			// Проверяем товары, которые уже находятся в корзине
			if (has_term(107, 'product_cat', $cart_item['product_id'])) {
				 // Если товар из категории с ID 107 уже есть в корзине
				 // Делаем какие-то действия, например, выводим сообщение
				 $subscription_in_cart = true;				
			}
	  }
	  
	  foreach ( $cart as $cart_item_key => $cart_item ) {
		// Проверяем товары, которые уже находятся в корзине
		if (!has_term(107, 'product_cat', $cart_item['product_id'])) {
			 // Если товар из категории с ID 107 уже есть в корзине
			 // Делаем какие-то действия, например, выводим сообщение
			 $product_in_cart = true;				
		}
  }

		 // Проверяем, есть ли в корзине продукт и подписка
		 if (has_term(107, 'product_cat', $product_id) ) {
			  $subscription_in_cart = true;
	
			 
		  } else {
		 	  $product_in_cart = true;
		 }
	}
	
	// Если есть и продукт, и подписка в корзине, запрещаем добавление
	if ($product_in_cart && $subscription_in_cart) {
		 wc_add_notice(('Вы не можете добавить товар и подписку одновременно.'), 'error');
		 return false;
	}

	return $passed;
}
add_filter('woocommerce_add_to_cart_validation', 'restrict_cart_contents', 10, 3);


//Исключаем категорию тарифы из страницы магазина
add_action( 'pre_get_posts', 'exclude_tag_id_107_products' );
function exclude_tag_id_107_products( $query ) {
    if ( ! is_admin() && $query->is_main_query() && is_post_type_archive( 'product' ) ) {
        $tax_query = $query->get( 'tax_query' );

        // Добавляем условие исключения по категории с tag_id = 107
        $tax_query[] = array(
            'taxonomy' => 'product_cat',
            'field'    => 'term_id',
            'terms'    =>  107,
            'operator' => 'NOT IN',
        );

        $query->set( 'tax_query', $tax_query );
    }
}
// Ограничиваем выбор количества товара до одного экземпляра
function custom_wc_quantity_input_min_max( $min, $product ) {
	return 1;
}
add_filter( 'woocommerce_quantity_input_min', 'custom_wc_quantity_input_min_max', 10, 2 );
add_filter( 'woocommerce_quantity_input_max', 'custom_wc_quantity_input_min_max', 10, 2 );




add_filter( 'woocommerce_is_purchasable', 'disable_product_purchase_if_already_bought', 10, 2 );

function disable_product_purchase_if_already_bought( $purchasable, $product ) {
    // Проверяем, является ли текущий пользователь администратором или администратором магазина
    if ( current_user_can('administrator') || current_user_can('shop_manager') )
        return $purchasable; // Возвращаем true для администраторов и администраторов магазина

    // Проверяем, является ли товар в настоящее время покупаемым
    if ( ! $purchasable )
        return $purchasable; // Если товар уже не покупаемый, возвращаем его состояние

    // Проверяем, купил ли текущий пользователь этот товар
    $current_user = wp_get_current_user();
    if ( wc_customer_bought_product( $current_user->user_email, $current_user->ID, $product->get_id() ) ) {
        $purchasable = false; // Если товар уже куплен, делаем его непокупаемым
		  add_filter( 'woocommerce_product_single_add_to_cart_text', 'change_button_text' );
    }
	 if ( class_exists( 'WC_Subscriptions_Product' ) && WC_Subscriptions_Product::is_subscription( $product ) ) {
		// Проверяем, имеет ли текущий пользователь активную подписку на этот товар
		$user_id = get_current_user_id();
		$subscriptions = wcs_get_users_subscriptions($user_id);

		foreach ( $subscriptions as $subscription ) {
			 if ( $subscription->get_status() !== 'active' && $subscription->has_product( $product->get_id() ) ) {
				$purchasable = true;  // Если у пользователя нет активной подписки на товар, делаем его покупаемым
			 }
		}
  }

    return $purchasable;
}

function change_button_text( $text ) {
	return __( 'Вы уже купили', 'woocommerce' );
}
function get_home() {
	?>
	
	<link rel="stylesheet" href="/wp-content/themes/tutorstarter/css/style.css">
	<link rel="stylesheet" href="/wp-content/themes/tutorstarter/css/responsive.css">
	<main class="main-wrapper">
			<section class="materials" id="materials">
				<div class="section-heading">
					<div class="container2">
						<h1 class="section-title">материалы</h1>
					</div>
				</div>
				<div class="section-body materials-body">
					<div class="container2">
						<ul class="section-tabs materials-tabs">
							<li class="section-tabs__item active-tab">
								<a href="#">Мои курсы</a>
							</li>
							<li class="section-tabs__item">
								<a href="#">Все курсы</a>
							</li>
						</ul>
						<div class="materials-list">
							<div class="materials-item">
								<div class="materials-item__info">
									<div class="materials-item__status not-started-status">Не начат</div>
									<div class="materials-item__label">Подростковый возраст</div>
									<h2 class="materials-item__title">
										<a href="#">Как пережить подростковый период</a>
									</h2>
									<div class="materials-item__description">
										<ul>
											<li>
												Узнайте, как помочь вашему малышу развить языковые навыки
											</li>
											<li>
												Будьте уверены в той роли, которую вы играете в развитии вашего малыша
											</li>
										</ul>
									</div>
								</div>
								<a href="#" class="materials-item__link">
									<img src="/wp-content/themes/tutorstarter/img/materials-image-1.jpg" alt="Как пережить подростковый период">
									<span class="materials-item__link-arrow">
										<img src="/wp-content/themes/tutorstarter/img/icons/arrow-up-outline.svg" alt="Link Arrow">
									</span>
								</a>
							</div>
							<div class="materials-item">
								<div class="materials-item__info">
									<div class="materials-item__status started-status">Начат</div>
									<div class="materials-item__label">Подростковый возраст</div>
									<h2 class="materials-item__title">
										<a href="#">Как пережить подростковый период</a>
									</h2>
									<div class="materials-item__description">
										<ul>
											<li>
												Узнайте, как помочь вашему малышу развить языковые навыки
											</li>
											<li>
												Будьте уверены в той роли, которую вы играете в развитии вашего малыша
											</li>
										</ul>
									</div>
								</div>
								<a href="#" class="materials-item__link">
									<img src="/wp-content/themes/tutorstarter/img/materials-image-1.jpg" alt="Как пережить подростковый период">
									<span class="materials-item__link-arrow">
										<img src="/wp-content/themes/tutorstarter/img/icons/arrow-up-outline.svg" alt="Link Arrow">
									</span>
								</a>
							</div>
							<div class="materials-item">
								<div class="materials-item__info">
									<div class="materials-item__status finished-status">Завершен</div>
									<div class="materials-item__label">Подростковый возраст</div>
									<h2 class="materials-item__title">
										<a href="#">Как пережить подростковый период</a>
									</h2>
									<div class="materials-item__description">
										<ul>
											<li>
												Узнайте, как помочь вашему малышу развить языковые навыки
											</li>
											<li>
												Будьте уверены в той роли, которую вы играете в развитии вашего малыша
											</li>
										</ul>
									</div>
								</div>
								<a href="#" class="materials-item__link">
									<img src="/wp-content/themes/tutorstarter/img/materials-image-1.jpg" alt="Как пережить подростковый период">
									<span class="materials-item__link-arrow">
										<img src="/wp-content/themes/tutorstarter/img/icons/arrow-up-outline.svg" alt="Link Arrow">
									</span>
								</a>
							</div>
							<div class="materials-item">
								<div class="materials-item__info">
									<div class="materials-item__status not-started-status">Не начат</div>
									<div class="materials-item__label">Подростковый возраст</div>
									<h2 class="materials-item__title">
										<a href="#">Как пережить подростковый период</a>
									</h2>
									<div class="materials-item__description">
										<ul>
											<li>
												Узнайте, как помочь вашему малышу развить языковые навыки
											</li>
											<li>
												Будьте уверены в той роли, которую вы играете в развитии вашего малыша
											</li>
										</ul>
									</div>
								</div>
								<a href="#" class="materials-item__link">
									<img src="/wp-content/themes/tutorstarter/img/materials-image-1.jpg" alt="Как пережить подростковый период">
									<span class="materials-item__link-arrow">
										<img src="/wp-content/themes/tutorstarter/img/icons/arrow-up-outline.svg" alt="Link Arrow">
									</span>
								</a>
							</div>
							<div class="materials-item">
								<div class="materials-item__info">
									<div class="materials-item__status started-status">Начат</div>
									<div class="materials-item__label">Подростковый возраст</div>
									<h2 class="materials-item__title">
										<a href="#">Как пережить подростковый период</a>
									</h2>
									<div class="materials-item__description">
										<ul>
											<li>
												Узнайте, как помочь вашему малышу развить языковые навыки
											</li>
											<li>
												Будьте уверены в той роли, которую вы играете в развитии вашего малыша
											</li>
										</ul>
									</div>
								</div>
								<a href="#" class="materials-item__link">
									<img src="/wp-content/themes/tutorstarter/img/materials-image-1.jpg" alt="Как пережить подростковый период">
									<span class="materials-item__link-arrow">
										<img src="/wp-content/themes/tutorstarter/img/icons/arrow-up-outline.svg" alt="Link Arrow">
									</span>
								</a>
							</div>
							<div class="materials-item">
								<div class="materials-item__info">
									<div class="materials-item__status finished-status">Завершен</div>
									<div class="materials-item__label">Подростковый возраст</div>
									<h2 class="materials-item__title">
										<a href="#">Как пережить подростковый период</a>
									</h2>
									<div class="materials-item__description">
										<ul>
											<li>
												Узнайте, как помочь вашему малышу развить языковые навыки
											</li>
											<li>
												Будьте уверены в той роли, которую вы играете в развитии вашего малыша
											</li>
										</ul>
									</div>
								</div>
								<a href="#" class="materials-item__link">
									<img src="/wp-content/themes/tutorstarter/img/materials-image-1.jpg" alt="Как пережить подростковый период">
									<span class="materials-item__link-arrow">
										<img src="/wp-content/themes/tutorstarter/img/icons/arrow-up-outline.svg" alt="Link Arrow">
									</span>
								</a>
							</div>
						</div>
					</div>
				</div>
			</section>
		</main>
		<?
}
add_shortcode('get_pricing1','get_pricing');
function get_pricing() {
	?>
	<style>
	.container {
		margin: 0px !important;
		padding: 0px !important;
		max-width: 100% !important;
	}
	</style>
	<link rel="stylesheet" href="/wp-content/themes/tutorstarter/css/style.css">
    <link rel="stylesheet" href="/wp-content/themes/tutorstarter/libs/swiper-bundle.min.css">
    <link rel="stylesheet" href="/wp-content/themes/tutorstarter/css/responsive.css">
	                                 <div class="wrapper">
    
      <main class="main-wrapper">
         <section class="pricing" id="pricing">
            <div class="section-heading">
               <div class="container2">
                  <nav aria-label="Page breadcrumb">
                     <ul class="breadcrumb">
                        <a href="#" class="breadcrumb-item" aria-current="page">Главаня</a></li>
                        <span class="breadcrumb-item current-page">Тарифы</span>
                     </ul>
                  </nav>
                  <h1 class="pricing-title section-title"><?php echo get_field("h1", 1125); ?></h1>
               </div>
            </div>
            <div class="pricing-body">
               <div class="container2">
                  <div class="pricing-list">
                     <div class="pricing-list__item">
                        <h2 class="pricing-list__title">Базовый</h2>
                        <div class="pricing-list__info">
                           <div class="pricing-list__description">
                              Базовый план "Старт" предоставляет ограниченный доступ к основным курсам, идеальный выбор
                              для
                              тех, кто только начинает свой путь в области воспитания.
                           </div>
                           <div class="pricing-list__cost">
                              <span>$</span> 70
                           </div>
                        </div>
                     </div>
                     <div class="pricing-list__item">
                        <h2 class="pricing-list__title">Стандарт</h2>
                        <div class="pricing-list__info">
                           <div class="pricing-list__description">
                              Базовый план "Старт" предоставляет ограниченный доступ к основным курсам, идеальный выбор
                              для
                              тех, кто только начинает свой путь в области воспитания.
                           </div>
                           <div class="pricing-list__cost">
                              <span>$</span> 130
                           </div>
                        </div>
                     </div>
                     <div class="pricing-list__item pricing-premium">
                        <h2 class="pricing-list__title">Премиум</h2>
                        <div class="pricing-list__info">
                           <div class="pricing-list__description">
                              Базовый план "Старт" предоставляет ограниченный доступ к основным курсам, идеальный выбор
                              для
                              тех, кто только начинает свой путь в области воспитания. предоставляет ограниченный доступ
                              к
                              основным курсам, идеальный выбор для тех, кто только начинает свой путь в области
                              воспитания
                              ограниченный доступ к основным курсам .
                           </div>
                           <div class="pricing-list__cost">
                              <span>$</span> 440
                           </div>
                        </div>
                        <div class="pricing-list__benefits">
                           <div class="pricing-list__benefits-item">
                              <div class="pricing-list__benefits-description">Статьи</div>
                              <div class="pricing-list__benefits-num">8</div>
                           </div>
                           <div class="pricing-list__benefits-item">
                              <div class="pricing-list__benefits-description">Подкасты (запись)</div>
                              <div class="pricing-list__benefits-num">8</div>
                           </div>
                           <div class="pricing-list__benefits-item">
                              <div class="pricing-list__benefits-description">Записи вебинаров и групповых сессий</div>
                              <div class="pricing-list__benefits-num">6</div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </section>
         <section class="pricing-compare" id="pricing-compare">
            <div class="container2">
               <div class="pricing-compare-label section-label">сравнительная таблица</div>
               <h2 class="pricing-compare-title">Доступные тарифы для каждой семьи</h2>
               <div class="subscription-list">
                  <div class="subscription-item subscription-base">
                     <div class="subscription-item__card">
                        <div class="subscription-item__package">
                           <span>Базовый</span>
                           <button type="button" class="subscription-item__more">
                              <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                 <path d="M10 16.9994L15 11.9995L10 6.99945" stroke="black" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" />
                              </svg>
                           </button>
                        </div>
                        <div class="subscription-item__price">
                           <span class="subscription-item__price-before">$</span>
                           <span class="subscription-item__price-text">70</span>
                           <span class="subscription-item__price-after">/Месяц</span>
                        </div>
                        <div class="subscription-item__description">
                           <p>
                              План "Базовый" предоставляет ограниченный доступ к основным курсам, идеальный выбор для
                              тех, кто только начинает свой путь в области воспитания.
                           </p>
                        </div>
                        <div class="subscription-item__actions">
                           <button type="button" class="subscription-item__btn btn-outline">Приобрести</button>
                        </div>
                     </div>
                     <div class="subscription-item__benefits">
                        <div class="subscription-benefits__title">Что получаете</div>
                        <div class="subscription-benefits__list">
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Вебинары интерактивные на любую тему</div>
                              <div class="subscription-benefits__content">1</div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Частные консультации</div>
                              <div class="subscription-benefits__content">Возможность покупки за 120 евро</div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Статьи</div>
                              <div class="subscription-benefits__content">8</div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Статьи</div>
                              <div class="subscription-benefits__content">8</div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Сопровождение личного менеджера </div>
                              <div class="subscription-benefits__content">
                                 <img src="/wp-content/themes/tutorstarter/img/icons/ph_close.svg" alt="">
                              </div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Инструменты (мультики, аудио книги и тд)
                              </div>
                              <div class="subscription-benefits__content">
                                 <img src="/wp-content/themes/tutorstarter/img/icons/ph_close.svg" alt="">
                              </div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Групповые сессии
                              </div>
                              <div class="subscription-benefits__content">
                                 <img src="/wp-content/themes/tutorstarter/img/icons/ph_close.svg" alt="">
                              </div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Доступ ко всем курсам
                              </div>
                              <div class="subscription-benefits__content">
                                 <img src="/wp-content/themes/tutorstarter/img/icons/icon-park-solid_check-one.svg" alt="">
                              </div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Подкасты (запись)
                              </div>
                              <div class="subscription-benefits__content">
                                 5
                              </div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Телеграм Канал
                              </div>
                              <div class="subscription-benefits__content">
                                 <img src="/wp-content/themes/tutorstarter/img/icons/icon-park-solid_check-one.svg" alt="">
                              </div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Коммуникация с экспертом (в тексте)
                              </div>
                              <div class="subscription-benefits__content">
                                 <img src="/wp-content/themes/tutorstarter/img/icons/ph_close.svg" alt="">
                              </div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Записи вебинаров и групповых сессий
                              </div>
                              <div class="subscription-benefits__content">
                                 1
                              </div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Посещение вебинаров пригл. спикеров
                              </div>
                              <div class="subscription-benefits__content">
                                 за деньги (1 в мес)
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="subscription-item subscription-standart">
                     <div class="subscription-item__card">
                        <div class="subscription-item__package">
                           <span>Стандарт</span>
                        </div>
                        <div class="subscription-item__price">
                           <span class="subscription-item__price-before">$</span>
                           <span class="subscription-item__price-text">130</span>
                           <span class="subscription-item__price-after">/Месяц</span>
                        </div>
                        <div class="subscription-item__description">
                           План "Стандарт" предоставляет ограниченный доступ к основным курсам, идеальный выбор для
                           тех, кто только начинает свой путь в области воспитания.
                        </div>
                        <div class="subscription-item__actions">
                           <button type="button" class="subscription-item__btn btn-black">Приобрести</button>
                        </div>
                     </div>
                     <div class="subscription-item__benefits">
                        <div class="subscription-benefits__title">Что получаете</div>
                        <div class="subscription-benefits__list">
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Вебинары интерактивные на любую тему</div>
                              <div class="subscription-benefits__content">1</div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Частные консультации</div>
                              <div class="subscription-benefits__content">Возможность покупки за 120 евро</div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Статьи</div>
                              <div class="subscription-benefits__content">8</div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Статьи</div>
                              <div class="subscription-benefits__content">8</div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Сопровождение личного менеджера </div>
                              <div class="subscription-benefits__content">
                                 <img src="/wp-content/themes/tutorstarter/img/icons/ph_close.svg" alt="">
                              </div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Инструменты (мультики, аудио книги и тд)
                              </div>
                              <div class="subscription-benefits__content">
                                 <img src="/wp-content/themes/tutorstarter/img/icons/ph_close.svg" alt="">
                              </div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Групповые сессии
                              </div>
                              <div class="subscription-benefits__content">
                                 <img src="/wp-content/themes/tutorstarter/img/icons/ph_close.svg" alt="">
                              </div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Доступ ко всем курсам
                              </div>
                              <div class="subscription-benefits__content">
                                 <img src="/wp-content/themes/tutorstarter/img/icons/icon-park-solid_check-one.svg" alt="">
                              </div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Подкасты (запись)
                              </div>
                              <div class="subscription-benefits__content">
                                 5
                              </div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Телеграм Канал
                              </div>
                              <div class="subscription-benefits__content">
                                 <img src="/wp-content/themes/tutorstarter/img/icons/icon-park-solid_check-one.svg" alt="">
                              </div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Коммуникация с экспертом (в тексте)
                              </div>
                              <div class="subscription-benefits__content">
                                 <img src="/wp-content/themes/tutorstarter/img/icons/ph_close.svg" alt="">
                              </div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Записи вебинаров и групповых сессий
                              </div>
                              <div class="subscription-benefits__content">
                                 1
                              </div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Посещение вебинаров пригл. спикеров
                              </div>
                              <div class="subscription-benefits__content">
                                 за деньги (1 в мес)
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="subscription-item subscription-premium">
                     <div class="subscription-item__card">
                        <div class="subscription-item__package">
                           <span>Премиум</span>
                        </div>
                        <div class="subscription-item__price">
                           <span class="subscription-item__price-before">$</span>
                           <span class="subscription-item__price-text">440</span>
                           <span class="subscription-item__price-after">/Месяц</span>
                        </div>
                        <div class="subscription-item__description">
                           План "Премиум" предоставляет ограниченный доступ к основным курсам, идеальный выбор для
                           тех, кто только начинает свой путь в области воспитания.
                        </div>
                        <div class="subscription-item__actions">
                           <button type="button" class="subscription-item__btn btn-outline">Приобрести</button>
                        </div>
                     </div>
                     <div class="subscription-item__benefits">
                        <div class="subscription-benefits__title">Что получаете</div>
                        <div class="subscription-benefits__list">
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Вебинары интерактивные на любую тему</div>
                              <div class="subscription-benefits__content">1</div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Частные консультации</div>
                              <div class="subscription-benefits__content">Возможность покупки за 120 евро</div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Статьи</div>
                              <div class="subscription-benefits__content">8</div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Статьи</div>
                              <div class="subscription-benefits__content">8</div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Сопровождение личного менеджера </div>
                              <div class="subscription-benefits__content">
                                 <img src="/wp-content/themes/tutorstarter/img/icons/ph_close.svg" alt="">
                              </div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Инструменты (мультики, аудио книги и тд)
                              </div>
                              <div class="subscription-benefits__content">
                                 <img src="/wp-content/themes/tutorstarter/img/icons/ph_close.svg" alt="">
                              </div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Групповые сессии
                              </div>
                              <div class="subscription-benefits__content">
                                 <img src="/wp-content/themes/tutorstarter/img/icons/ph_close.svg" alt="">
                              </div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Доступ ко всем курсам
                              </div>
                              <div class="subscription-benefits__content">
                                 <img src="/wp-content/themes/tutorstarter/img/icons/icon-park-solid_check-one.svg" alt="">
                              </div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Подкасты (запись)
                              </div>
                              <div class="subscription-benefits__content">
                                 5
                              </div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Телеграм Канал
                              </div>
                              <div class="subscription-benefits__content">
                                 <img src="/wp-content/themes/tutorstarter/img/icons/icon-park-solid_check-one.svg" alt="">
                              </div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Коммуникация с экспертом (в тексте)
                              </div>
                              <div class="subscription-benefits__content">
                                 <img src="/wp-content/themes/tutorstarter/img/icons/ph_close.svg" alt="">
                              </div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Записи вебинаров и групповых сессий
                              </div>
                              <div class="subscription-benefits__content">
                                 1
                              </div>
                           </div>
                           <div class="subscription-benefits__item">
                              <div class="subscription-benefits__label">Посещение вебинаров пригл. спикеров
                              </div>
                              <div class="subscription-benefits__content">
                                 за деньги (1 в мес)
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="subscription-modal">
               <div class="container2">
                  <div class="subscription-modal__wrapper">
                     <button type="button" class="subscription-modal__back">
                        <img src="/wp-content/themes/tutorstarter/img/icons/solar_arrow-up-outline.svg" alt="Back">
                     </button>
                     <div class="slider-navigation">
                        <button type="button" class="slider-button-prev" id="subscription-button-prev">
                           <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                              xmlns="http://www.w3.org/2000/svg">
                              <path fill-rule="evenodd" clip-rule="evenodd"
                                 d="M3.47032 12.5301C3.32987 12.3895 3.25098 12.1988 3.25098 12.0001C3.25098 11.8013 3.32987 11.6107 3.47032 11.4701L9.47032 5.47009C9.53898 5.3964 9.62178 5.3373 9.71378 5.29631C9.80578 5.25532 9.90509 5.23328 10.0058 5.2315C10.1065 5.22972 10.2065 5.24825 10.2999 5.28597C10.3933 5.32369 10.4781 5.37984 10.5494 5.45105C10.6206 5.52227 10.6767 5.60711 10.7144 5.70049C10.7522 5.79388 10.7707 5.89391 10.7689 5.99461C10.7671 6.09532 10.7451 6.19463 10.7041 6.28663C10.6631 6.37863 10.604 6.46143 10.5303 6.53009L5.81032 11.2501L20.0003 11.2501C20.1992 11.2501 20.39 11.3291 20.5306 11.4698C20.6713 11.6104 20.7503 11.8012 20.7503 12.0001C20.7503 12.199 20.6713 12.3898 20.5306 12.5304C20.39 12.6711 20.1992 12.7501 20.0003 12.7501L5.81032 12.7501L10.5303 17.4701C10.604 17.5388 10.6631 17.6216 10.7041 17.7136C10.7451 17.8056 10.7671 17.9049 10.7689 18.0056C10.7707 18.1063 10.7522 18.2063 10.7144 18.2997C10.6767 18.3931 10.6206 18.4779 10.5494 18.5491C10.4781 18.6203 10.3933 18.6765 10.2999 18.7142C10.2065 18.7519 10.1065 18.7705 10.0058 18.7687C9.90509 18.7669 9.80578 18.7449 9.71378 18.7039C9.62178 18.6629 9.53898 18.6038 9.47032 18.5301L3.47032 12.5301Z"
                                 fill="white" />
                           </svg>
                        </button>
                        <button type="button" class="slider-button-next" id="subscription-button-next">
                           <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                              xmlns="http://www.w3.org/2000/svg">
                              <path fill-rule="evenodd" clip-rule="evenodd"
                                 d="M20.5299 12.5301C20.6704 12.3895 20.7493 12.1988 20.7493 12.0001C20.7493 11.8013 20.6704 11.6107 20.5299 11.4701L14.5299 5.47009C14.4613 5.3964 14.3785 5.3373 14.2865 5.29631C14.1945 5.25532 14.0952 5.23328 13.9944 5.2315C13.8937 5.22972 13.7937 5.24825 13.7003 5.28597C13.6069 5.32369 13.5221 5.37984 13.4509 5.45105C13.3797 5.52227 13.3235 5.60711 13.2858 5.70049C13.2481 5.79388 13.2296 5.89391 13.2313 5.99461C13.2331 6.09532 13.2552 6.19463 13.2961 6.28663C13.3371 6.37863 13.3962 6.46143 13.4699 6.53009L18.1899 11.2501L3.99993 11.2501C3.80101 11.2501 3.61025 11.3291 3.4696 11.4698C3.32894 11.6104 3.24993 11.8012 3.24993 12.0001C3.24993 12.199 3.32894 12.3898 3.4696 12.5304C3.61025 12.6711 3.80102 12.7501 3.99993 12.7501L18.1899 12.7501L13.4699 17.4701C13.3962 17.5388 13.3371 17.6216 13.2961 17.7136C13.2552 17.8056 13.2331 17.9049 13.2313 18.0056C13.2296 18.1063 13.2481 18.2063 13.2858 18.2997C13.3235 18.3931 13.3797 18.4779 13.4509 18.5491C13.5221 18.6203 13.6069 18.6765 13.7003 18.7142C13.7937 18.7519 13.8937 18.7705 13.9944 18.7687C14.0952 18.7669 14.1945 18.7449 14.2865 18.7039C14.3785 18.6629 14.4613 18.6038 14.5299 18.5301L20.5299 12.5301Z"
                                 fill="white" />
                           </svg>
                        </button>
                     </div>
                     <div class="swiper subscription-slider" id="subscriptionSlider">
                        <div class="swiper-wrapper">
                           <div class="swiper-slide subscription-item subscription-base">
                              <h3 class="subscription-item__title">
                                 Базовый
                              </h3>
                              <div class="subscription-item__benefits">
                                 <div class="subscription-benefits__title">Что получаете</div>
                                 <div class="subscription-benefits__list">
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Вебинары интерактивные на любую тему
                                       </div>
                                       <div class="subscription-benefits__content">1</div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Частные консультации</div>
                                       <div class="subscription-benefits__content">Возможность покупки за 120 евро</div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Статьи</div>
                                       <div class="subscription-benefits__content">8</div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Статьи</div>
                                       <div class="subscription-benefits__content">8</div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Сопровождение личного менеджера </div>
                                       <div class="subscription-benefits__content">
                                          <img src="/wp-content/themes/tutorstarter/img/icons/ph_close.svg" alt="">
                                       </div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Инструменты (мультики, аудио книги и
                                          тд)
                                       </div>
                                       <div class="subscription-benefits__content">
                                          <img src="/wp-content/themes/tutorstarter/img/icons/ph_close.svg" alt="">
                                       </div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Групповые сессии
                                       </div>
                                       <div class="subscription-benefits__content">
                                          <img src="/wp-content/themes/tutorstarter/img/icons/ph_close.svg" alt="">
                                       </div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Доступ ко всем курсам
                                       </div>
                                       <div class="subscription-benefits__content">
                                          <img src="/wp-content/themes/tutorstarter/img/icons/icon-park-solid_check-one.svg" alt="">
                                       </div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Подкасты (запись)
                                       </div>
                                       <div class="subscription-benefits__content">
                                          5
                                       </div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Телеграм Канал
                                       </div>
                                       <div class="subscription-benefits__content">
                                          <img src="/wp-content/themes/tutorstarter/img/icons/icon-park-solid_check-one.svg" alt="">
                                       </div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Коммуникация с экспертом (в тексте)
                                       </div>
                                       <div class="subscription-benefits__content">
                                          <img src="/wp-content/themes/tutorstarter/img/icons/ph_close.svg" alt="">
                                       </div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Записи вебинаров и групповых сессий
                                       </div>
                                       <div class="subscription-benefits__content">
                                          1
                                       </div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Посещение вебинаров пригл. спикеров
                                       </div>
                                       <div class="subscription-benefits__content">
                                          за деньги (1 в мес)
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="swiper-slide subscription-item subscription-standart">
                              <h3 class="subscription-item__title">
                                 Стандарт
                              </h3>
                              <div class="subscription-item__benefits">
                                 <div class="subscription-benefits__title">Что получаете</div>
                                 <div class="subscription-benefits__list">
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Вебинары интерактивные на любую тему
                                       </div>
                                       <div class="subscription-benefits__content">1</div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Частные консультации</div>
                                       <div class="subscription-benefits__content">Возможность покупки за 120 евро</div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Статьи</div>
                                       <div class="subscription-benefits__content">8</div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Статьи</div>
                                       <div class="subscription-benefits__content">8</div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Сопровождение личного менеджера </div>
                                       <div class="subscription-benefits__content">
                                          <img src="/wp-content/themes/tutorstarter/img/icons/ph_close.svg" alt="">
                                       </div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Инструменты (мультики, аудио книги и
                                          тд)
                                       </div>
                                       <div class="subscription-benefits__content">
                                          <img src="/wp-content/themes/tutorstarter/img/icons/ph_close.svg" alt="">
                                       </div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Групповые сессии
                                       </div>
                                       <div class="subscription-benefits__content">
                                          <img src="/wp-content/themes/tutorstarter/img/icons/ph_close.svg" alt="">
                                       </div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Доступ ко всем курсам
                                       </div>
                                       <div class="subscription-benefits__content">
                                          <img src="/wp-content/themes/tutorstarter/img/icons/icon-park-solid_check-one.svg" alt="">
                                       </div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Подкасты (запись)
                                       </div>
                                       <div class="subscription-benefits__content">
                                          5
                                       </div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Телеграм Канал
                                       </div>
                                       <div class="subscription-benefits__content">
                                          <img src="/wp-content/themes/tutorstarter/img/icons/icon-park-solid_check-one.svg" alt="">
                                       </div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Коммуникация с экспертом (в тексте)
                                       </div>
                                       <div class="subscription-benefits__content">
                                          <img src="/wp-content/themes/tutorstarter/img/icons/ph_close.svg" alt="">
                                       </div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Записи вебинаров и групповых сессий
                                       </div>
                                       <div class="subscription-benefits__content">
                                          1
                                       </div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Посещение вебинаров пригл. спикеров
                                       </div>
                                       <div class="subscription-benefits__content">
                                          за деньги (1 в мес)
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="swiper-slide subscription-item subscription-premium">
                              <h3 class="subscription-item__title">
                                 Премиум
                              </h3>
                              <div class="subscription-item__benefits">
                                 <div class="subscription-benefits__title">Что получаете</div>
                                 <div class="subscription-benefits__list">
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Вебинары интерактивные на любую тему
                                       </div>
                                       <div class="subscription-benefits__content">1</div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Частные консультации</div>
                                       <div class="subscription-benefits__content">Возможность покупки за 120 евро</div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Статьи</div>
                                       <div class="subscription-benefits__content">8</div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Статьи</div>
                                       <div class="subscription-benefits__content">8</div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Сопровождение личного менеджера </div>
                                       <div class="subscription-benefits__content">
                                          <img src="/wp-content/themes/tutorstarter/img/icons/ph_close.svg" alt="">
                                       </div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Инструменты (мультики, аудио книги и
                                          тд)
                                       </div>
                                       <div class="subscription-benefits__content">
                                          <img src="/wp-content/themes/tutorstarter/img/icons/ph_close.svg" alt="">
                                       </div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Групповые сессии
                                       </div>
                                       <div class="subscription-benefits__content">
                                          <img src="/wp-content/themes/tutorstarter/img/icons/ph_close.svg" alt="">
                                       </div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Доступ ко всем курсам
                                       </div>
                                       <div class="subscription-benefits__content">
                                          <img src="/wp-content/themes/tutorstarter/img/icons/icon-park-solid_check-one.svg" alt="">
                                       </div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Подкасты (запись)
                                       </div>
                                       <div class="subscription-benefits__content">
                                          5
                                       </div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Телеграм Канал
                                       </div>
                                       <div class="subscription-benefits__content">
                                          <img src="/wp-content/themes/tutorstarter/img/icons/icon-park-solid_check-one.svg" alt="">
                                       </div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Коммуникация с экспертом (в тексте)
                                       </div>
                                       <div class="subscription-benefits__content">
                                          <img src="/wp-content/themes/tutorstarter/img/icons/ph_close.svg" alt="">
                                       </div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Записи вебинаров и групповых сессий
                                       </div>
                                       <div class="subscription-benefits__content">
                                          1
                                       </div>
                                    </div>
                                    <div class="subscription-benefits__item">
                                       <div class="subscription-benefits__label">Посещение вебинаров пригл. спикеров
                                       </div>
                                       <div class="subscription-benefits__content">
                                          за деньги (1 в мес)
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </section>

         <div class="banner-marqee">
            <div class="swiper marqee-slider">
               <div class="swiper-wrapper">
                  <div class="swiper-slide marqee-slide">
                     <div class="marqee-slide__description">
                        Эффективное родительство: основы и практика
                     </div>
                  </div>
                  <div class="swiper-slide marqee-slide">
                     <div class="marqee-slide__description">
                        Семейная мудрость: учимся вместе с детьми
                     </div>
                  </div>
                   <div class="swiper-slide marqee-slide">
                     <div class="marqee-slide__description">
                        Эффективное родительство: основы и практика
                     </div>
                  </div>
                  <div class="swiper-slide marqee-slide">
                     <div class="marqee-slide__description">
                        Семейная мудрость: учимся вместе с детьми
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </main>

     
   </div>
	<script src="/wp-content/themes/tutorstarter/js/script.js"></script>
	<script src="/wp-content/themes/tutorstarter/libs/swiper-bundle.min.js"></script>
	  <?
}
add_shortcode('get_pricing1','get_pricing');