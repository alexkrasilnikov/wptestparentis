<?php
/**
 * Course loop price
 *
 * @package Tutor\Templates
 * @subpackage CourseLoopPart
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.4.3
 */

?>
<?php
$course_id    = get_the_ID();
$monetization = tutor_utils()->get_option( 'monetize_by' );
/**
 * If Monetization is PMPRO then ignore ajax enrolment
 * to avoid Paid course enrollment without payment.
 *
 * Note: There is no mapping between Tutor Course and PMPRO
 * That is way there is no way to determine whether course if free
 * or paid
 *
 * @since v2.1.2
 */
$button_class = 'pmpro' === $monetization ? ' ' : ' tutor-course-list-enroll';
if ( ! is_user_logged_in() ) {
	$button_class = apply_filters( 'tutor_enroll_required_login_class', 'tutor-open-login-modal' );
}


$related_product =  get_field( '_related_woo_product_id', get_the_ID());
$product = wc_get_product($related_product);
$enroll_btn = '';
if ($product) {
	// Получите цену продукта
	$price = wc_price($product->get_price());

	// Выведите кнопку покупки курса с ценой
	$enroll_btn = '<div class=""><a href="' . esc_url( $product->add_to_cart_url() ). '" class="tutor-btn tutor-btn-primary tutor-btn-lg tutor-btn-block">В козину  ' . $price . '</a><a class="tutor-btn tutor-btn-primary tutor-btn-lg tutor-btn-block tutor-mt-24 tutor-enroll-course-button tutor-static-loader" href="' . home_url()  . '/тарифы">Подписка</a></div>';
}
// Получаем ID пользователя
$user_id = get_current_user_id();

// Получаем все заказы пользователя
$orders = wc_get_orders( array(
	'customer_id' => $user_id,
	'status'      => array( 'completed' ) // Проверяем только завершенные заказы
) );

// Проходим по каждому заказу пользователя
foreach ( $orders as $order ) {
	// Получаем товары из заказа
	$items = $order->get_items();

	// Проходим по каждому товару в заказе
	foreach ( $items as $item ) {
		// Получаем ID товара
		$product_id = $item->get_product_id();

		// Получаем значение поля _related_course_id с помощью ACF функций
		$related_course_id = get_field( '_related_course_id', $product_id );

		// Проверяем, совпадает ли ID курса с ID, который нужно проверить
		if ( $related_course_id == get_the_ID() ) {
			$enroll_btn = '<div class="tutor-course-list-btn">' . apply_filters( 'tutor_course_restrict_new_entry', '<a href="' . get_the_permalink() . '" class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-btn-block ' . $button_class . ' " data-course-id="' . $course_id . '">' . __( 'Enroll Course', 'tutor' ) . '</a>' ) . '</div>';
		} else{
			$enroll_btn = '<div class=""><a href="' . esc_url( $product->add_to_cart_url() ). '" class="tutor-btn tutor-btn-primary tutor-btn-lg tutor-btn-block">В козину  ' . $price . '</a><a class="tutor-btn tutor-btn-primary tutor-btn-lg tutor-btn-block tutor-mt-24 tutor-enroll-course-button tutor-static-loader" href="' . home_url()  . '/тарифы">Подписка</a></div>';

		}

	}
}

$user_subscriptions = wcs_get_users_subscriptions( $user_id );
foreach ( $user_subscriptions as $subscription ) {

	$order_id= $subscription->get_parent_id();
	$order = wc_get_order( $order_id );
	$product_id = '';
	if ( $order ) {
		// Получаем массив объектов товаров из заказа
		$items = $order->get_items();

		foreach ( $items as $item ) {
			$product_id = $item->get_product_id();
			// Используйте $product_id по вашему усмотрению
		}
	}

	$category_id = get_field( 'category_id', $product_id);	
	if (has_term($category_id , 'course-category', get_the_ID())){
		$enroll_btn = '<div class="tutor-course-list-btn">' . apply_filters( 'tutor_course_restrict_new_entry', '<a href="' . get_the_permalink() . '" class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-btn-block ' . $button_class . ' " data-course-id="' . $course_id . '">' . __( 'Enroll Course', 'tutor' ) . '</a>' ) . '</div>';
	} else {
		$enroll_btn = '<div class=""><a href="' . esc_url( $product->add_to_cart_url() ). '" class="tutor-btn tutor-btn-primary tutor-btn-lg tutor-btn-block">В козину  ' . $price . '</a><a class="tutor-btn tutor-btn-primary tutor-btn-lg tutor-btn-block tutor-mt-24 tutor-enroll-course-button tutor-static-loader" href="' . home_url()  . '/тарифы">Подписка</a></div>';

	}
}




$free_html  = $enroll_btn;

if ( tutor_utils()->is_course_purchasable() ) {
	$enroll_btn = tutor_course_loop_add_to_cart( false );

	$product_id = tutor_utils()->get_course_product_id( $course_id );
	$product    = wc_get_product( $product_id );

	$total_enrolled   = tutor_utils()->count_enrolled_users_by_course( $course_id );
	$maximum_students = tutor_utils()->get_course_settings( $course_id, 'maximum_students' );

	if ( 0 != $maximum_students && $total_enrolled != $maximum_students ) {
		$total_booked = 100 / $maximum_students * $total_enrolled;
		$b_total      = $total_booked;
		//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="tutor-d-flex tutor-align-center tutor-justify-between">
                    <div> 
                        <span class="tutor-course-price tutor-fs-6 tutor-fw-bold tutor-color-black">' .
						$product->get_price_html() . ' 
                        </span>
                    </div>

                    <div class="tutor-course-booking-progress tutor-d-flex tutor-align-center">
                        <div class="tutor-mr-8">
                            <div class="tutor-progress-circle" style="--pro: ' . esc_html( $b_total ) . '%;" area-hidden="true"></div>
                        </div>
                        <div class="tutor-fs-7 tutor-fw-medium tutor-color-black">' .
						esc_html( $b_total ) . __( '% Booked', 'tutor' ) . '
                        </div>
                    </div>
                </div>
                <div class="tutor-course-booking-availability tutor-mt-16">
                    <button class="tutor-btn tutor-btn-outline-primary tutor-btn-md tutor-btn-block">' .
					apply_filters( 'tutor_course_restrict_new_entry', $enroll_btn ) . ' 
                    </button>
                </div>';
	}

	if ( $product && $maximum_students == $total_enrolled && 0 != $maximum_students ) {
		$price_html = '<div class="tutor-d-flex tutor-align-center tutor-justify-between"><div class="list-item-price tutor-d-flex tutor-align-center"> <span class="price tutor-fs-6 tutor-fw-bold tutor-color-black">' . $product->get_price_html() . ' </span></div>';
		$restrict   = '<div class="list-item-booking booking-full tutor-d-flex tutor-align-center"><div class="booking-progress tutor-d-flex"><span class="tutor-mr-8 tutor-color-warning tutor-icon-circle-info"></span></div><div class="tutor-fs-7 tutor-fw-medium tutor-color-black">' . __( 'Fully Booked', 'tutor' ) . '</div></div></div>';
		echo wp_kses_post( $price_html );
		echo wp_kses_post( $restrict );
	}

	if ( $product && 0 == $maximum_students ) {
		$price_html = '<div class="tutor-d-flex tutor-align-center tutor-justify-between"><div class="list-item-price tutor-d-flex tutor-align-center"> <span class="price tutor-fs-6 tutor-fw-bold tutor-color-black">' . $product->get_price_html() . ' </span></div>';
		$cart_html  = '<div class="list-item-button"> ' . apply_filters( 'tutor_course_restrict_new_entry', $enroll_btn ) . ' </div></div>';
		echo wp_kses_post( $price_html );
		echo wp_kses_post( $cart_html );
	}
} else {
	echo wp_kses_post( $free_html );
}

