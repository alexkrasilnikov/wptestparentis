document.addEventListener("DOMContentLoaded", function () {
	//Open hidden navigation menu
	var navigationMenuBtn = document.querySelector(".header-btn");
	var navigationMenu = document.querySelector(".header-nav-menu");

	// navigationMenuBtn.addEventListener("click", function () {
	// 	navigationMenuBtn.classList.toggle("active");
	// 	navigationMenu.classList.toggle("menu-active");
	// });


	/*
	* Swiper Sliders
	*/
	const tearcherSliderElement = document.getElementById("tearcherSlider");
	if (tearcherSliderElement) {
		const tearcherSlider = new Swiper(tearcherSliderElement, {
			slidesPerView: 1,
			spaceBetween: 16,
			centeredSlides: true,
			loop: true,
			navigation: {
				prevEl: "#teacher-button-prev",
				nextEl: "#teacher-button-next",
			},
			breakpoints: {
				767: {
					slidesPerView: 1.5,
					centeredSlides: false,
					loop: false,
				},
				992: {
					slidesPerView: 2,
					centeredSlides: false,
					loop: false,
				},
			},
		});
	}

	const subscriptionSliderElement = document.getElementById("subscriptionSlider");
	if (subscriptionSliderElement) {
		const subscriptionSlider = new Swiper(subscriptionSliderElement, {
			slidesPerView: 1,
			spaceBetween: 6,
			navigation: {
				prevEl: "#subscription-button-prev",
				nextEl: "#subscription-button-next",
			}
		});
	}

	const bannerMarqeeElement = document.querySelector(".marqee-slider");
	if (bannerMarqeeElement) {
		const marqeeSlider = new Swiper(bannerMarqeeElement, {
			slidesPerView: "auto",
			loop: true,
			speed: 30000,
			freeMode: true,
			autoplay: {
				delay: 0,
				disableOnInteraction: false
			},
			breakpoints: {
				1280: {
					slidesPerView: 1.4,
				}
			}
		});
	}

	const expertVideoElement = document.getElementById("expertVideosSlider");
	if (expertVideoElement) {
		const expertVideoSlider = new Swiper(expertVideoElement, {
			slidesPerView: 1,
			navigation: {
				prevEl: "#expert-button-prev",
				nextEl: "#expert-button-next",
			},
			breakpoints: {
				992: {
					slidesPerView: 1.2,
				},
			},
		});
	}


	const reviewsSliderElement = document.getElementById("reviewsSlider");
	if (reviewsSliderElement) {
		const reviewsSlider = new Swiper(reviewsSliderElement, {
			slidesPerView: 1,
			spaceBetween: 16,
			centeredSlides: true,
			loop: true,
			navigation: {
				prevEl: "#reviews-button-prev",
				nextEl: "#reviews-button-next",
			},
			breakpoints: {
				767: {
					slidesPerView: 1.5,
					centeredSlides: false,
					loop: false,
				},
				992: {
					slidesPerView: 3,
					centeredSlides: false,
					loop: false,
				},
			},
		});
	}


	/*
	* Hiding and opening a modal window with detailed information about subscription rates
	*/
	const subscriptionButtonMore = document.querySelector('.subscription-item__more');
	const subscriptionButtonClose = document.querySelector('.subscription-modal__back');

	if (subscriptionButtonMore) {
		const subscriptionModal = document.querySelector('.subscription-modal');

		subscriptionButtonMore.addEventListener('click', function () {
			subscriptionModal.classList.add('active');
		});
	}

	if (subscriptionButtonClose) {
		const subscriptionModal = document.querySelector('.subscription-modal');

		subscriptionButtonClose.addEventListener('click', function () {
			subscriptionModal.classList.remove('active');
		});
	}




	/*
	* Hiding the preview and showing the video when clicking on the "Play" button 
	*/
	const playButtons = document.querySelectorAll('.expert-video__play');

	// Go through each button and add a handler for the click event
	playButtons.forEach(function (button) {
		button.addEventListener('click', function () {

			// Find the parent element of the button
			const parentSlide = button.closest('.swiper-slide');

			// Find the block with video and add the class show
			let wrapper = parentSlide.querySelector('.expert-video__wrapper');
			wrapper.classList.add('show');

			// Find the iframe inside the video block and run it
			let video = wrapper.querySelector('iframe');
			video.src += "&autoplay=1";

			button.classList.add('hide');
		});
	});

	const allCat = document.querySelectorAll('.category-list__link')

	allCat.forEach(elem => {
		if (elem.href == window.location.href) {
			elem.classList.add('active')

		}
		console.log(elem.href)
		console.log(window.location.href)
	})
});