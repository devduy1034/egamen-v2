/* Validation form */
validateForm('validation-contact');
validateForm('validation-newsletter');
// validateForm('validation-cart');
// validateForm('validation-user');

/* Load name input file */
NN_FRAMEWORK.loadNameInputFile = function () {
	if (isExist($('.custom-file input[type=file]'))) {
		$('body').on('change', '.custom-file input[type=file]', function () {
			var fileName = $(this).val();
			fileName = fileName.substr(fileName.lastIndexOf('\\') + 1, fileName.length);
			$(this).siblings('label').html(fileName);
		});
	}
};

/* Back to top */
NN_FRAMEWORK.GoTop = function () {
	$(window).scroll(function () {
		if (!$('.scrollToTop').length)
			$('body').append('<div class="scrollToTop"><img src="' + GOTOP + '" alt="Go Top"/></div>');
		if ($(this).scrollTop() > 100) $('.scrollToTop').fadeIn();
		else $('.scrollToTop').fadeOut();
	});

	$('body').on('click', '.scrollToTop', function () {
		$('html, body').animate({ scrollTop: 0 }, 800);
		return false;
	});
};

/* Menu */
NN_FRAMEWORK.Menu = function () {
	if ($('.navigation').length) {
		let navHeight = $('.navigation').outerHeight();
		function checkScrollDirection(callback) {
			let lastScrollTop = 0;
			$(window).on('scroll', function () {
				let currentScroll = $(this).scrollTop();
				if (currentScroll > lastScrollTop) {
					callback('down');
				} else {
					callback('up');
				}
				lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
			});
		}
		function detectStatusOfNavigation(event) {
			const OFFSET_TOP = 60,
				MIN_COMPUTER_VIEWPORT = 1025;
			const eventType = event.type,
				fixedOnLoad = !$('.above-nav').length ||
					$('.above-nav')[0].clientHeight === 0 ||
					($('.below-nav-rps').length && $('body').width() > MIN_COMPUTER_VIEWPORT),
				scrolledPassAbove = $(window).scrollTop() >= (fixedOnLoad ? OFFSET_TOP : $('.above-nav').outerHeight());
			const isBottom = window.innerHeight + window.pageYOffset >= document.body.offsetHeight,
				isTop = window.pageYOffset === 0,
				isMobile = $('body').width() < MIN_COMPUTER_VIEWPORT,
				isIndex = isExist($('.slideshow')) ? true : false;

			$('.navigation').toggleClass('is-bottom', isBottom);
			$('.navigation').toggleClass('is-top', isTop);
			$('.navigation').toggleClass('is-mobile', isMobile);
			$('.navigation').toggleClass('is-desktop', !isMobile);

			if (eventType !== 'scroll') {
				$('.navigation').toggleClass('is-index', isIndex);
				$('.navigation').toggleClass('not-index', !isIndex);
			}
			if (fixedOnLoad) {
				$('.navigation').addClass('is-fixed');
				$('.navigation').toggleClass('was-scrolled', scrolledPassAbove);
			} else {
				$('.navigation').toggleClass('is-fixed was-scrolled', scrolledPassAbove);
			}
			if (fixedOnLoad) {
				if (!$('.below-nav-rps').length || (($('.below-nav-rps').length || $('.above-nav')[0].clientHeight > 0) && isMobile)) {
					$('.below-nav').css({
						marginTop: navHeight,
					});
				} else {
					$('.below-nav').css({
						marginTop: 0,
					});
				}
			} else {
				$('.below-nav').css({
					marginTop: scrolledPassAbove ? navHeight : 0,
				});
			}
			if (isMobile && !$('.nav-menu ul').length) {
				$('.nav-menu').html($('.menu ul.ulmn').clone(true));
			} else if (!isMobile) {
				$('.nav-menu').empty();
			}
		}

		checkScrollDirection(function (direction) {
			$('.navigation')[direction === 'down' ? 'addClass' : 'removeClass']('scrolling-down')[direction === 'up' ? 'addClass' : 'removeClass']('scrolling-up');
		});
		$(window).bind('load resize', function (event) {
			detectStatusOfNavigation(event);
		}).scroll(function (event) {
			detectStatusOfNavigation(event);
		});

		if ($('.menu-mobile-btn').length) {
			$('body').on('click', 'span.btn-dropdown-menu', function () {
				var o = $(this);
				if (!o.hasClass('active')) {
					o.addClass('active');
					o.next('.sub-menu').stop().slideDown(300);
				} else {
					o.removeClass('active');
					o.next('.sub-menu').stop().slideUp(300);
				}
			});
			$('.menu-mobile-btn').click(function (e) {
				e.preventDefault();
				e.stopPropagation();
				$('.header-left-fixwidth').toggleClass('open-sidebar-menu');
				$('.opacity-menu').toggleClass('open-opacity');

				$('body').toggleClass('no-scroll', $('.opacity-menu').hasClass('open-opacity'));
			});
			$('.opacity-menu').click(function (e) {
				$('.header-left-fixwidth').removeClass('open-sidebar-menu');
				$('.opacity-menu').removeClass('open-opacity');

				$('body').removeClass('no-scroll');
			});
		}
	}
};

/* Tools */
NN_FRAMEWORK.Tools = function () {
	if (isExist($('.toolbar'))) {
		$('.footer').css({ marginBottom: $('.toolbar').innerHeight() });
	}
};

/* Popup */
NN_FRAMEWORK.Popup = function () {
	if (isExist($('#popup'))) {
		validateForm('validation-popup');
		$('#popup').modal('show');
	}
};

/* Wow */
NN_FRAMEWORK.Wows = function () {
	new WOW().init();
};

/* Search */
NN_FRAMEWORK.Search = function () {
	if (isExist($('.search-toggle'))) {
		var closeNavigationSearch = function () {
			$('.navigation.search-open').removeClass('search-open');
			$('body').removeClass('no-scroll');
			$('.search-toggle i').removeClass('bi-x-lg').addClass('bi-search');
			$('.navigation-search-panel .show-search').hide();
		};

		$('.search-toggle').click(function (e) {
			e.preventDefault();
			var nav = $(this).closest('.navigation');
			var icon = $(this).find('i');
			var isOpen = nav.hasClass('search-open');

			closeNavigationSearch();

			if (!isOpen) {
				nav.addClass('search-open');
				$('body').addClass('no-scroll');
				icon.removeClass('bi-search').addClass('bi-x-lg');
				$('#keyword-navigation').trigger('focus');
			}
		});

		$('.navigation-search-panel .search-panel-close').click(function () {
			closeNavigationSearch();
		});

		$(document).on('click', function (e) {
			if (!$(e.target).closest('.navigation-search-panel, .search-toggle').length) {
				closeNavigationSearch();
			}
		});

		$(document).on('keydown', function (e) {
			if (e.key === 'Escape') {
				closeNavigationSearch();
			}
		});
	}

	if (isExist($('.icon-search'))) {
		$('.icon-search').click(function () {
			if ($(this).hasClass('active')) {
				$(this).removeClass('active');
				$('.search-grid').stop(true, true).animate({ opacity: '0', width: '0px' }, 200);
			} else {
				$(this).addClass('active');
				$('.search-grid').stop(true, true).animate({ opacity: '1', width: '230px' }, 200);
			}
			document.getElementById($(this).next().find('input').attr('id')).focus();
			$('.icon-search i').toggleClass('bi bi-x-lg');
		});
	}

	if (isExist($('.search-auto'))) {
		var ajaxSearchRequest = null;
		var $navForm = $('.navigation-search-form');
		var trackUrl = $navForm.attr('data-track-url') || '/api/events/track';

		function resolveTrackingSessionId() {
			var key = 'egamen_tracking_session_id';
			var current = '';
			try {
				current = localStorage.getItem(key) || '';
			} catch (e) { }
			if (current) return current;

			current = 'sess_' + Date.now() + '_' + Math.random().toString(36).slice(2, 10);
			try {
				localStorage.setItem(key, current);
			} catch (e) { }
			return current;
		}

		function trackBehaviorEvent(eventType, metadata) {
			var sessionId = resolveTrackingSessionId();
			var userId = (typeof MEMBER_ID !== 'undefined' && parseInt(MEMBER_ID, 10) > 0) ? String(parseInt(MEMBER_ID, 10)) : '';
			var payload = {
				event_type: eventType,
				session_id: sessionId,
				source: 'web',
				metadata: metadata || {}
			};
			if (userId) {
				payload.user_id = userId;
			} else {
				payload.anonymous_id = sessionId;
			}
			if (typeof CSRF_TOKEN !== 'undefined' && CSRF_TOKEN) {
				payload.csrf_token = CSRF_TOKEN;
				payload._token = CSRF_TOKEN;
			}
			try {
				if (navigator.sendBeacon && trackUrl) {
					var blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
					navigator.sendBeacon(trackUrl, blob);
					return;
				}
			} catch (e) { }

			$.ajax({
				url: trackUrl,
				type: 'POST',
				dataType: 'json',
				contentType: 'application/json; charset=UTF-8',
				data: JSON.stringify(payload)
			});
		}

		$('.show-search').hide().empty();
		$('.search-auto').on('input', function () {
			var $this = $(this);
			var keyword = $.trim($this.val());
			var $resultBox = $this.closest('.navigation-search-panel__inner').find('.show-search');
			var quickSearchUrl = $this.closest('form').attr('data-quick-search-url') || '/tim-kiem-goi-y';

			if (keyword.length < 1) {
				$resultBox.hide().empty();
				if (ajaxSearchRequest) {
					ajaxSearchRequest.abort();
				}
				return;
			}

			if (ajaxSearchRequest) {
				ajaxSearchRequest.abort();
			}

			// Instant search without delays - results show as you type
			ajaxSearchRequest = $.ajax({
				url: quickSearchUrl,
				type: 'GET',
				data: { keyword: keyword },
				dataType: 'html',
				success: function (html) {
					var content = $.trim(html || '');
					if (content.length > 0) {
						$resultBox.html(content).show();
					} else {
						$resultBox.hide().empty();
					}
				},
				error: function () {
					$resultBox.hide().empty();
				}
			});
		});

		$navForm.off('submit.trackSearch').on('submit.trackSearch', function () {
			var keyword = $.trim($('#keyword-navigation').val() || '');
			if (!keyword) return;
			var resultCount = $('.show-search .nav-search-result-item').length;
			trackBehaviorEvent('search_query', {
				query: keyword,
				filters: {},
				sort: 'relevance',
				result_count: resultCount
			});
		});

		$(document).off('click.trackSearchResult').on('click.trackSearchResult', '.show-search .nav-search-result-item', function () {
			var $item = $(this);
			var keyword = $.trim($item.attr('data-query') || $('#keyword-navigation').val() || '');
			var productId = ($item.attr('data-product-id') || '').toString();
			var position = parseInt($item.attr('data-position') || '0', 10) || 0;
			if (!productId) return;
			trackBehaviorEvent('click_result', {
				query: keyword,
				product_id: productId,
				position: position,
				page: 1
			});
		});

		$(document).on('click', function (e) {
			if (!$(e.target).closest('.navigation-search-form, .show-search').length) {
				$('.show-search').hide();
			}
		});
	}

	if (isExist($('.icon-search-menu'))) {
		$('.icon-search-menu').click(function () {
			if ($(this).hasClass('active')) {
				$(this).removeClass('active');
				$('.search-grid').stop(true, true).animate({ opacity: '0', width: '0px' }, 200);
			} else {
				$(this).addClass('active');
				$('.search-grid').stop(true, true).animate({ opacity: '1', width: '230px' }, 200);
			}
			document.getElementById($(this).next().find('input').attr('id')).focus();
			$('.icon-search-menu i').toggleClass('fa-xmark');
		});
	}
};

/* Password toggle */
NN_FRAMEWORK.PasswordToggle = function () {
	if (isExist($('.js-toggle-password'))) {
		$('.js-toggle-password').off('click').on('click', function () {
			var target = $(this).attr('data-target');
			var input = $(target);
			if (!input.length) return;

			var isPassword = input.attr('type') === 'password';
			input.attr('type', isPassword ? 'text' : 'password');
			$(this).find('i').toggleClass('bi-eye bi-eye-slash');
		});
	}
};

/* Videos */
NN_FRAMEWORK.Videos = function () {
	Fancybox.bind('[data-fancybox]', {});
};

/* Dom Change */
NN_FRAMEWORK.DomChange = function () {
	/* Video Fotorama */
	if (isExist($('#fotorama-videos'))) {
		$('#fotorama-videos').fotorama();
	}
	/* Video Select */
	if (isExist($('.list-video'))) {
		$('.list-video').change(function () {
			var id = $(this).val();
			$.ajax({
				url: 'load-video',
				type: 'GET',
				dataType: 'html',
				data: {
					id: id
				},
				beforeSend: function () {
					holdonOpen();
				},
				success: function (result) {
					$('.video-main').html(result);
					holdonClose();
				}
			});
		});
	}

	/* Chat Facebook */
	$('#messages-facebook').one('DOMSubtreeModified', function () {
		$('.js-facebook-messenger-box').on('click', function () {
			$('.js-facebook-messenger-box, .js-facebook-messenger-container').toggleClass('open'),
				$('.js-facebook-messenger-tooltip').length && $('.js-facebook-messenger-tooltip').toggle();
		}),
			$('.js-facebook-messenger-box').hasClass('cfm') &&
			setTimeout(function () {
				$('.js-facebook-messenger-box').addClass('rubberBand animated');
			}, 3500),
			$('.js-facebook-messenger-tooltip').length &&
			($('.js-facebook-messenger-tooltip').hasClass('fixed')
				? $('.js-facebook-messenger-tooltip').show()
				: $('.js-facebook-messenger-box').on('hover', function () {
					$('.js-facebook-messenger-tooltip').show();
				}),
				$('.js-facebook-messenger-close-tooltip').on('click', function () {
					$('.js-facebook-messenger-tooltip').addClass('closed');
				}));
		$('.search_open').click(function () {
			$('.search_box_hide').toggleClass('opening');
		});
	});
};

NN_FRAMEWORK.SwiperData = function (obj) {
	if (!isExist(obj)) return false;
	var name = obj.attr('data-swiper-name') || 'swiper';
	var thumbs = obj.attr('data-swiper-thumbs');
	var more = obj.attr('data-swiper');

	if (more && more.search('|') >= 0) {
		more = more.split('|');
		var on = more.reduce((a, b) => {
			if (b.search('{') < 0) {
				var c = {
					[b.split(':')[0]]: useStrict(b.split(':')[1])
				};
			} else {
				const b1 = String(b.split(':', 1));
				const b2 = useStrict(b.slice(String(b.split(':', 1)).length + 1).trim());
				var c = {
					[b1]: b2
				};
			}
			return Object.assign({}, a, c);
		}, {});
	} else {
		on = '';
	}
	if (thumbs) {
		on.thumbs = { swiper: window[thumbs] };
	}

	window[name] = new Swiper(obj[0], on);

	if (window[name].passedParams.breakpoints) {
		if (window[name].params.direction == 'vertical') {
			const entries = Object.entries(window[name].passedParams.breakpoints);
			function setHeight() {
				var height = obj.find('.item').outerHeight();
				var countItem = obj.find('.item').length;
				entries.forEach((v) => {
					var Breakpoint = v[0] > 0 ? v[0] : 0;
					if (Breakpoint > 0 && Breakpoint == window[name].currentBreakpoint) {
						var items = v[1].slidesPerView != undefined ? v[1].slidesPerView : 0;
						var margin = v[1].spaceBetween != undefined ? v[1].spaceBetween : 0;
					} else if (window[name].currentBreakpoint == 'max') {
						var items = window[name].passedParams.slidesPerView;
						var margin = window[name].passedParams.spaceBetween;
					}
					if (window[name].params.direction == 'vertical') {
						if (countItem < items) {
							obj.css({
								height: height * countItem + (countItem - 1) * margin
							});
							obj.find('.swiper-slide').addClass('h-auto');
						} else {
							obj.css({
								height: height * items + (items - 1) * margin
							});
						}
						window[name].update();
					} else {
						obj.css({ height: '' });
						window[name].update();
					}
				});
			}
			setHeight();
			obj.find('img').each(function () {
				if (!this.complete) {
					$(this).one('load error', function () {
						setHeight();
					});
				}
			});
			$(window).on('resize', setHeight);
			window[name].on('imagesReady', setHeight);
			window[name].on('breakpoint', setHeight);
			setTimeout(setHeight, 120);
		}
	} else {
		if (window[name].params.direction == 'vertical') {
			function setHeight() {
				var height = obj.find('.item').outerHeight();
				var countItem = obj.find('.item').length;
				var items = window[name].passedParams.slidesPerView;
				var margin = window[name].passedParams.spaceBetween;
				if (countItem < items) {
					obj.css({
						height: height * countItem + (countItem - 1) * margin
					});
					obj.find('.swiper-slide').addClass('h-auto');
				} else {
					obj.css({ height: height * items + (items - 1) * margin });
				}
				window[name].update();
			}
			setHeight();
			obj.find('img').each(function () {
				if (!this.complete) {
					$(this).one('load error', function () {
						setHeight();
					});
				}
			});
			$(window).on('resize', setHeight);
			window[name].on('imagesReady', setHeight);
			window[name].on('breakpoint', setHeight);
			setTimeout(setHeight, 120);
		}
	}
	return window[name];
};

/* Swiper */
NN_FRAMEWORK.Swiper = function () {
	if (isExist($('.swiper-auto'))) {
		$('.swiper-auto[data-swiper-name]').each(function () {
			NN_FRAMEWORK.SwiperData($(this));
		});
		$('.swiper-auto:not([data-swiper-name])').each(function () {
			NN_FRAMEWORK.SwiperData($(this));
		});
	}
};

NN_FRAMEWORK.Api = function () {
	const observeNearViewport = function (element, callback, options = {}) {
		if (!element || typeof callback !== 'function') return;

		const rootMargin = options.rootMargin || '300px 0px';
		const once = options.once !== false;

		if (!('IntersectionObserver' in window)) {
			callback(element);
			return;
		}

		const observer = new IntersectionObserver(function (entries, currentObserver) {
			entries.forEach(function (entry) {
				if (!entry.isIntersecting) return;

				callback(entry.target);

				if (once) {
					currentObserver.unobserve(entry.target);
				}
			});
		}, {
			rootMargin: rootMargin
		});

		observer.observe(element);
	};

	if (isExist($('.click-product'))) {
		$('.click-product span').click(function (e) {
			var thisClass = $(this).closest('.other-product');
			thisClass.find('.click-product span').removeClass('active');
			$(this).addClass('active');
			var url = $(this).data('url');
			var type = $(this).data('type');
			var template = $(this).data('template');
			var id_list = $(this).data('list');
			var id_cat = $(this).data('cat');
			var id_item = $(this).data('item');
			var status = $(this).data('status');
			var paginate = $(this).data('paginate');
			var other = $(this).data('other');
			var section = $(this).data('section');
			var slug = $(this).data('slug');
			var eshow = $(this).data('eshow');
			$.ajax({
				url: url,
				type: 'GET',
				data: {
					type: type,
					status: status,
					id_list: id_list,
					id_cat: id_cat,
					id_item: id_item,
					template: template,
					other: other,
					section: section,
					slug: slug,
					paginate: paginate,
					eShow: eshow
				},
				success: function (result) {
					thisClass.find(eshow).html(result);
					NN_FRAMEWORK.Swiper();
					NN_FRAMEWORK.Img();
					NN_FRAMEWORK.ProductCard();
				}
			});
		});
		$('.click-product').each(function () {
			var triggerContainer = $(this);
			var observeTarget = triggerContainer.closest('.list-product').get(0) || triggerContainer.get(0);

			observeNearViewport(observeTarget, function () {
				if (triggerContainer.data('lazy-loaded')) return;
				triggerContainer.data('lazy-loaded', true);
				triggerContainer.find('span').first().trigger('click');
			});
		});
		/* loc san pham */
		$('.sort-select-main span').on('click', function () {
			var sort = $(this).data('sort');
			$('.sort-select-main span').removeClass('active');
			$(this).addClass('active');

			var activeProduct = $('.click-product span.active');
			var url = activeProduct.data('url');
			var id_list = activeProduct.data('list');
			var slug = activeProduct.data('slug');
			var paginate = activeProduct.data('paginate');
			var other = activeProduct.data('other');
			var section = activeProduct.data('section');
			var eshow = activeProduct.data('eshow');

			$.ajax({
				url: url,
				type: 'GET',
				data: {
					id_list: id_list,
					slug: slug,
					paginate: paginate,
					other: other,
					section: section,
					eShow: eshow,
					sort: sort
				},
				success: function (result) {
					$(eshow).html(result);
				},
				error: function () {
					alert('Đã xảy ra lỗi, vui lòng thử lại.');
				}
			});
		});
	}
	if (isExist($('.load-home'))) {
		$('.load-home').each(function () {
			var thisClass = $(this);
			observeNearViewport(thisClass.get(0), function () {
				if (thisClass.data('lazy-loaded')) return;
				thisClass.data('lazy-loaded', true);

				var url = thisClass.data('url');
				var type = thisClass.data('type');
				var paginate = thisClass.data('paginate');
				var template = thisClass.data('template');
				var other = thisClass.data('other');
				var slug = thisClass.data('slug');
				var status = thisClass.data('status');
				var id_list = thisClass.data('list');
				var id_cat = thisClass.data('cat');
				var id_item = thisClass.data('item');
				var section = thisClass.data('section');
				var eshow = thisClass.data('eshow');
				$.ajax({
					url: url,
					type: 'GET',
					data: {
						type: type,
						status: status,
						id_list: id_list,
						id_cat: id_cat,
						id_item: id_item,
						template: template,
						other: other,
						section: section,
						slug: slug,
						paginate: paginate,
						eShow: eshow
					},
					success: function (result) {
						thisClass.find(eshow).html(result);
						NN_FRAMEWORK.Swiper();
						NN_FRAMEWORK.Img();
						NN_FRAMEWORK.ProductCard();
					}
				});
			});
		});
	}
	$('body').on('click', '#load-more', function () {
		var page = $(this).data('page');
		var section = $(this).data('section');
		var button = $(this);
		var parentContainer;

		if (section === 'home') {
			parentContainer = button.closest('.load-product-home');
		} else if (section === 'list') {
			parentContainer = button.closest('.product-list').find('span.active');
		}
		else if (section === 'cat') {
			parentContainer = button.closest('.list-product').find('span.active');
		}

		if (parentContainer && parentContainer.length) {
			var url = parentContainer.data('url');
			var type = parentContainer.data('type');
			var paginate = parentContainer.data('paginate');
			var other = parentContainer.data('other');
			var id_list = parentContainer.data('list');
			var id_cat = parentContainer.data('cat');
			var template = parentContainer.data('template');
			var eshow = parentContainer.data('eshow');
			$.ajax({
				url: url,
				type: 'GET',
				data: { type, other, paginate, template, id_list, id_cat, page, eshow, section },
				success: function (response) {
					handleAjaxSuccess(response, button, page, section, eshow);
				}
			});
		}
	});

	function handleAjaxSuccess(response, button, page, section, eshow) {
		if (response.trim()) {
			var newProducts = $(response).find('.row').html();
			if (newProducts) {
				button.parent('.col-12.button').remove();
				$(eshow).find('#product-list-' + section + ' .row').append(newProducts);
				button.data('page', page + 1);
			}
		} else {
			button.remove();
		}
	}

	if (isExist($('.item-search'))) {
		$('.item-search input').click(function () {
			Filter();
		});
	}

	if (isExist($('.sort-select-main'))) {
		$('.sort-select-main p a').click(function () {
			$('.sort-select-main p a').removeClass('check');
			$(this).addClass('check');
			Filter();
		});
	}

	$('.filter').click(function (e) {
		$('.left-product').toggleClass('show');
	});
	TextSort();
};

NN_FRAMEWORK.HomeRecommend = function () {
	var section = document.getElementById('home-recommend-section');
	if (!section) return;

	var $section = $(section);
	var $loading = $section.find('.home-recommend-loading');
	var $empty = $section.find('.home-recommend-empty');
	var $list = $section.find('.home-recommend-list');
	var $pagination = $section.find('.home-recommend-pagination');
	var apiUrl = $section.attr('data-api-url') || '';
	var perPage = parseInt($section.attr('data-limit') || '10', 10);
	var fetchLimit = parseInt($section.attr('data-fetch-limit') || '60', 10);

	perPage = isNaN(perPage) ? 10 : Math.max(1, perPage);
	fetchLimit = isNaN(fetchLimit) ? 60 : Math.max(perPage, fetchLimit);

	function resolveTrackingSessionId() {
		var key = 'egamen_tracking_session_id';
		var current = '';
		try {
			current = localStorage.getItem(key) || '';
		} catch (e) { }
		if (current) return current;

		current = 'sess_' + Date.now() + '_' + Math.random().toString(36).slice(2, 10);
		try {
			localStorage.setItem(key, current);
		} catch (e) { }
		return current;
	}

	function renderRecommendPagination($items, currentPage) {
		var totalItems = $items.length;
		var totalPages = Math.ceil(totalItems / perPage);

		if (totalPages <= 1) {
			$items.removeClass('d-none');
			$pagination.addClass('d-none').empty();
			return;
		}

		var safePage = Math.max(1, Math.min(currentPage, totalPages));
		var startIndex = (safePage - 1) * perPage;
		var endIndex = startIndex + perPage;

		$items.addClass('d-none').slice(startIndex, endIndex).removeClass('d-none');

		var html = '';
		html += '<button type="button" class="home-recommend-page-btn" data-page="' + (safePage - 1) + '"' +
			(safePage <= 1 ? ' disabled' : '') + '>Truoc</button>';

		for (var page = 1; page <= totalPages; page++) {
			html += '<button type="button" class="home-recommend-page-btn' + (page === safePage ? ' is-active' : '') +
				'" data-page="' + page + '">' + page + '</button>';
		}

		html += '<button type="button" class="home-recommend-page-btn" data-page="' + (safePage + 1) + '"' +
			(safePage >= totalPages ? ' disabled' : '') + '>Sau</button>';

		$pagination.html(html).removeClass('d-none');
	}

	$pagination.off('click.homeRecommend').on('click.homeRecommend', '.home-recommend-page-btn', function () {
		var $btn = $(this);
		if ($btn.is(':disabled')) return;

		var targetPage = parseInt($btn.attr('data-page') || '1', 10);
		var $items = $list.find('.home-recommend-grid .col');
		if (!$items.length || isNaN(targetPage)) return;

		renderRecommendPagination($items, targetPage);
	});

	if (!apiUrl) {
		$loading.addClass('d-none');
		$empty.removeClass('d-none').text('Thieu cau hinh API goi y.');
		$pagination.addClass('d-none').empty();
		return;
	}

	var params = {
		limit: fetchLimit,
		format: 'html'
	};

	if (typeof MEMBER_ID !== 'undefined' && parseInt(MEMBER_ID, 10) > 0) {
		params.user_id = String(parseInt(MEMBER_ID, 10));
	} else {
		var sessionId = resolveTrackingSessionId();
		params.anonymous_id = sessionId;
		params.session_id = sessionId;
	}

	$.ajax({
		url: apiUrl,
		type: 'GET',
		dataType: 'html',
		data: params,
		success: function (html) {
			$loading.addClass('d-none');
			var content = $.trim(html || '');
			if (!content.length) {
				$empty.removeClass('d-none');
				$pagination.addClass('d-none').empty();
				return;
			}

			$list.html(content);

			var $items = $list.find('.home-recommend-grid .col');
			if ($items.length === 0) {
				$empty.removeClass('d-none');
				$pagination.addClass('d-none').empty();
				return;
			}

			renderRecommendPagination($items, 1);
			$empty.addClass('d-none');
		},
		error: function () {
			$loading.addClass('d-none');
			$pagination.addClass('d-none').empty();
			$empty.removeClass('d-none').text('Khong tai duoc goi y luc nay.');
		}
	});
};

NN_FRAMEWORK.Properties = function () {
	if (isExist($('.grid-properties'))) {
		$('.properties').click(function (e) {
			$(this).parents('.grid-properties').find('.properties').removeClass('active');
			// $('.properties').removeClass('outstock');
			$(this).addClass('active');
		});
	}
};

NN_FRAMEWORK.ProductCard = function () {
	if (isExist($('.product-colors'))) {
		$('body').on('click', '.product-color', function () {
			var button = $(this);
			var container = button.closest('.product');
			var mainImg = container.find('.pic-product img.product-main-img');
			var mainSrc = button.data('image');
			var cacheBuster = 'v=' + Date.now();
			var finalSrc = mainSrc ? (mainSrc + (mainSrc.indexOf('?') >= 0 ? '&' : '?') + cacheBuster) : '';

			if (mainImg.length && finalSrc) {
				mainImg.attr('src', finalSrc);
				if (mainImg.attr('srcset')) {
					mainImg.attr('srcset', finalSrc);
				}
				if (mainImg.attr('data-src')) {
					mainImg.attr('data-src', finalSrc);
				}
				var picture = mainImg.closest('picture');
				if (picture.length) {
					picture.find('source').attr('srcset', finalSrc);
				}
			}

			button.closest('.product-colors').find('.product-color').removeClass('active');
			button.addClass('active');
		});
	}
};

NN_FRAMEWORK.QuickView = function () {
	var modalId = 'quickview-product-modal';

	function getModalHtml() {
		return `
		<div class="quickview-modal" id="${modalId}" aria-hidden="true">
			<div class="quickview-dialog" role="dialog" aria-modal="true">
				<button type="button" class="quickview-close" aria-label="Close">&times;</button>
				<div class="quickview-body">
					<div class="quickview-loading">Đang tải...</div>
				</div>
			</div>
		</div>`;
	}

	function ensureModal() {
		var modal = document.getElementById(modalId);
		if (!modal) {
			document.body.insertAdjacentHTML('beforeend', getModalHtml());
			modal = document.getElementById(modalId);
		}
		return modal;
	}

	function closeModal() {
		var modal = document.getElementById(modalId);
		if (!modal) return;
		modal.classList.remove('is-open');
		document.body.classList.remove('quickview-open');
	}

	function openModal() {
		var modal = ensureModal();
		modal.classList.add('is-open');
		document.body.classList.add('quickview-open');
	}

	function setModalContent(html) {
		var modal = ensureModal();
		var body = modal.querySelector('.quickview-body');
		if (!body) return;
		body.innerHTML = html;
	}

	function setLoading() {
		setModalContent('<div class="quickview-loading">Đang tải...</div>');
	}

	function setError() {
		setModalContent('<div class="quickview-error">Không thể tải nội dung sản phẩm.</div>');
	}

	function simplifyQuickViewGrid(grid, detailUrl) {
		if (!grid) return '';

		// Keep thumbnails data for image switching, but hide the left thumb column in UI.
		var thumbs = grid.querySelector('.product-detail-thumbs');
		if (thumbs) thumbs.classList.add('quickview-thumbs-hidden');

		// Disable click-to-zoom in quick view.
		var mainZoomAnchor = grid.querySelector('#Zoom-1');
		if (mainZoomAnchor) {
			mainZoomAnchor.classList.remove('MagicZoom');
			mainZoomAnchor.classList.add('quickview-disable-zoom');
			mainZoomAnchor.setAttribute('href', 'javascript:void(0)');
			mainZoomAnchor.removeAttribute('data-options');
		}

		// Add detail page button on the right column.
		var rightCol = grid.querySelector('.right-pro-detail');
		if (rightCol && detailUrl && !rightCol.querySelector('.quickview-detail-link-wrap')) {
			var wrap = document.createElement('div');
			wrap.className = 'quickview-detail-link-wrap';
			wrap.innerHTML =
				'<a class="quickview-detail-link" href="' + detailUrl + '">' +
				'Xem chi tiết' +
				'</a>';
			rightCol.appendChild(wrap);
		}

		return '<div class="grid-pro-detail product-detail-v2 quickview-grid">' + grid.innerHTML + '</div>';
	}

	function extractGridDetail(htmlText, detailUrl) {
		var parser = new DOMParser();
		var doc = parser.parseFromString(htmlText, 'text/html');
		var grid = doc.querySelector('.grid-pro-detail');
		if (!grid) return '';
		return simplifyQuickViewGrid(grid, detailUrl);
	}

	$('body').on('click', '.js-quick-view', function (e) {
		e.preventDefault();
		var url = $(this).data('url') || $(this).attr('href');
		if (!url) return;

		openModal();
		setLoading();

		fetch(url, {
			method: 'GET',
			headers: { 'X-Requested-With': 'XMLHttpRequest' }
		})
			.then(function (response) {
				if (!response.ok) throw new Error('Failed');
				return response.text();
			})
			.then(function (htmlText) {
				var gridHtml = extractGridDetail(htmlText, url);
				if (!gridHtml) {
					setError();
					return;
				}
				setModalContent(gridHtml);
				NN_FRAMEWORK.Swiper();
				NN_FRAMEWORK.ProductCard();
				NN_FRAMEWORK.Properties();
			})
			.catch(function () {
				setError();
			});
	});

	$('body').on('click', '#' + modalId + ' .quickview-close', function () {
		closeModal();
	});

	$('body').on('click', '#' + modalId, function (e) {
		if (e.target.id === modalId) closeModal();
	});

	$(document).on('keydown', function (e) {
		if (e.key === 'Escape') closeModal();
	});
};

NN_FRAMEWORK.Main = function () {
	var imgElements = document.querySelectorAll('img');
	imgElements.forEach(function (img) {
		if (!img.hasAttribute('alt')) {
			img.alt = WEBSITE_NAME;
		}
	});
	var anchorElements = document.querySelectorAll('a');
	anchorElements.forEach(function (anchor) {
		if (!anchor.hasAttribute('aria-label')) {
			anchor.setAttribute('aria-label', WEBSITE_NAME);
		}
	});

	$('.tt-toc').click(function (e) {
		$('.box-readmore ul').slideToggle();
	});
	$('.top-banner .close').click(() => {
		$('.top-banner').slideToggle()
		sessionStorage.setItem("top-banner", true)
	})

};

NN_FRAMEWORK.Img = function () {
	const images = document.querySelectorAll('img');
	images.forEach((img) => {
		const handleImageLoad = () => {
			const width = img.clientWidth;
			const height = img.clientHeight;
			const hw = img.getAttribute('width');
			if (width > 0 && height > 0 && !hw) {
				img.setAttribute('width', width);
				img.setAttribute('height', height);
			}
		};
		img.addEventListener('load', handleImageLoad);
		if (img.complete) {
			handleImageLoad();
		}
	});
};

/* Lazy Background Loading */
NN_FRAMEWORK.LazyBackgroundLoading = function () {
	if (isExist($('.lazy-background'))) {
		if ('IntersectionObserver' in window) {
			function handleIntersection(entries) {
				entries.map((entry) => {
					const bgImage = entry.target.dataset.bgImage,
						bgOptions = entry.target.dataset.bgOptions ? entry.target.dataset.bgOptions : '';

					if (entry.isIntersecting) {
						entry.target.style[bgOptions ? 'background' : 'backgroundImage'] = "url('" + bgImage + "') " + bgOptions;
						observer.unobserve(entry.target);
					}
				});
			}

			const elements = document.querySelectorAll('.lazy-background');
			const observer = new IntersectionObserver(handleIntersection, {
				rootMargin: '100px',
			});
			elements.forEach((element) => observer.observe(element));
		} else {
			const elements = document.querySelectorAll('.lazy-background');
			elements.forEach((element) => {
				const bgImage = element.dataset.bgImage,
					bgOptions = element.dataset.bgOptions ? element.dataset.bgOptions : '';

				element.style[bgOptions ? 'background' : 'backgroundImage'] = "url('" + bgImage + "') " + bgOptions;
			});
		}
	}
};

NN_FRAMEWORK.VoucherHome = function () {
	function fallbackCopy(value) {
		var input = document.createElement('textarea');
		input.value = value;
		input.style.position = 'fixed';
		input.style.left = '-9999px';
		document.body.appendChild(input);
		input.focus();
		input.select();
		try {
			document.execCommand('copy');
		} catch (e) { }
		document.body.removeChild(input);
	}

	function copyCode(value) {
		if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
			return navigator.clipboard.writeText(value);
		}
		fallbackCopy(value);
		return Promise.resolve();
	}

	$(document).off('click.voucherHome').on('click.voucherHome', '.js-copy-voucher-home', function (event) {
		event.preventDefault();
		var code = ($(this).data('code') || '').toString().trim();
		if (!code) return;

		copyCode(code)
			.then(function () {
				if (typeof showNotify === 'function') {
					showNotify('Đã sao chép mã: ' + code, 'Thông báo', 'success');
				}
			})
			.catch(function () {
				if (typeof showNotify === 'function') {
					showNotify('Không thể sao chép mã. Vui lòng thử lại.', 'Thông báo', 'warning');
				}
			});
	});
};

NN_FRAMEWORK.NavSmartSearch = function () {
	const navSugContainer = document.querySelector('.navigation-ai-suggestions .nav-ai-suggestions, .navigation-ai-suggestions .ai-search-suggestions');
	if (!navSugContainer) return;

	function escapeHtml(value) {
		return String(value || '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#39;');
	}

	const wrapper = document.querySelector('.navigation-ai-suggestions');
	const navForm = document.querySelector('.navigation-search-form');
	let defaultSuggestions = [];
	try {
		defaultSuggestions = JSON.parse(wrapper.dataset.smartSuggestions || '[]');
	} catch (e) { }

	function renderNavSuggestions() {
		let recent = [];
		try {
			recent = JSON.parse(localStorage.getItem('ai_recent_searches') || '[]');
		} catch (e) {
			recent = [];
		}

		const merged = [...new Set([...recent, ...defaultSuggestions])].slice(0, 5);

		navSugContainer.innerHTML = merged.map(function (s) {
			const isRecent = recent.includes(s);
			const icon = isRecent ? '<i class="bi bi-clock-history"></i>' : '<i class="bi bi-search"></i>';
			return `<button type="button" class="nav-ai-suggestion" data-smart-query="${escapeHtml(s)}">
                ${icon}
                <span class="nav-ai-suggestion-text">${escapeHtml(s)}</span>
                <i class="bi bi-arrow-up-left nav-ai-suggestion-arrow"></i>
            </button>`;
		}).join('');

		const btns = navSugContainer.querySelectorAll('[data-smart-query]');
		btns.forEach(function (button) {
			button.addEventListener('click', function () {
				const value = button.dataset.smartQuery || '';
				const input = document.getElementById('keyword-navigation');
				if (input) {
					input.value = value;
					saveRecentNavSearch(value); // Save to history
					renderNavSuggestions(); // Update the UI immediately
					if (navForm) {
						if (typeof navForm.requestSubmit === 'function') {
							navForm.requestSubmit();
						} else {
							navForm.submit();
						}
					}
				}
			});
		});
	}

	renderNavSuggestions();

	// Save recent keyword while preserving default submit behavior.
	if (navForm) {
		navForm.addEventListener('submit', function () {
			const input = document.getElementById('keyword-navigation');
			if (input && input.value.trim().length > 1) {
				saveRecentNavSearch(input.value);
			}
		});
	}

	function saveRecentNavSearch(query) {
		const q = query.trim();
		if (!q) return;
		let recent = [];
		try {
			recent = JSON.parse(localStorage.getItem('ai_recent_searches') || '[]');
		} catch (e) {
			recent = [];
		}
		recent = recent.filter(item => item.toLowerCase() !== q.toLowerCase());
		recent.unshift(q);
		recent = recent.slice(0, 5);
		localStorage.setItem('ai_recent_searches', JSON.stringify(recent));
	}
};

NN_FRAMEWORK.SmartSearch = function () {
	const root = document.querySelector('[data-smart-search-root]');
	if (!root) return;

	const endpoint = root.dataset.smartEndpoint;
	const configuredPerPage = Number(root.dataset.smartPerPage || 36);
	const perPage = Number.isFinite(configuredPerPage) && configuredPerPage > 0 ? Math.round(configuredPerPage) : 36;
	const form = root.querySelector('[data-smart-search-form]');
	const input = root.querySelector('[data-smart-search-input]');
	const loading = root.querySelector('[data-smart-search-loading]');
	const status = root.querySelector('[data-smart-search-status]');
	const resultsWrap = document.querySelector('[data-smart-search-results-wrap]');
	const resultsGrid = document.querySelector('[data-smart-search-results]');
	const emptyState = document.querySelector('[data-smart-search-empty]');
	const smartPagination = document.querySelector('[data-smart-search-pagination]');
	const defaultGrid = document.querySelector('[data-smart-default-grid]');
	const defaultPagination = document.querySelector('[data-smart-default-pagination]');
	const summary = document.querySelector('[data-smart-search-summary]');
	const title = document.querySelector('[data-smart-search-title]');
	const resetButtons = document.querySelectorAll('[data-smart-search-reset]');
	const defaultGridHtml = defaultGrid ? defaultGrid.innerHTML : '';
	const divKqSearch = document.querySelector('.div_kq_search');
	const suggestionContainer = root.querySelector('.ai-search-suggestions');
	let defaultSuggestions = [];
	try {
		defaultSuggestions = JSON.parse(root.dataset.smartSuggestions || '[]');
	} catch (e) { }

	let activeQuery = '';
	let activePage = 1;
	let isLoading = false;

	function escapeHtml(value) {
		return String(value || '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#39;');
	}

	function formatPrice(price) {
		const value = Number(price || 0);
		return value > 0 ? value.toLocaleString('vi-VN') + 'đ' : 'Liên hệ';
	}

	function setLoading(nextLoading) {
		isLoading = !!nextLoading;
		if (loading) loading.hidden = !isLoading;
		if (form) form.classList.toggle('is-loading', isLoading);
	}

	function setStatus(message, type) {
		if (!status) return;
		status.textContent = message || '';
		status.dataset.state = type || '';
	}

	function setSmartPaginationHidden(hidden) {
		if (!smartPagination) return;
		smartPagination.hidden = !!hidden;
		if (hidden) {
			smartPagination.innerHTML = '';
		}
	}

	function showDefaultState() {
		if (defaultGrid) defaultGrid.hidden = false;
		if (defaultGrid) defaultGrid.innerHTML = defaultGridHtml;
		if (defaultPagination) defaultPagination.hidden = false;
		if (resultsWrap) resultsWrap.hidden = true;
		if (summary) summary.textContent = '';
		if (title) title.textContent = '';
		if (emptyState) emptyState.hidden = true;
		if (resultsGrid) resultsGrid.innerHTML = '';
		if (divKqSearch) divKqSearch.hidden = false;
		setSmartPaginationHidden(true);
		activeQuery = '';
		activePage = 1;
	}

	function showSmartState() {
		if (defaultGrid) defaultGrid.hidden = false;
		if (defaultPagination) defaultPagination.hidden = true;
		if (resultsWrap) resultsWrap.hidden = false;
		if (divKqSearch) divKqSearch.hidden = true;
	}

	function renderSuggestions() {
		if (!suggestionContainer) return;
		let recent = [];
		try {
			recent = JSON.parse(localStorage.getItem('ai_recent_searches') || '[]');
		} catch (e) { }

		const merged = [...new Set([...recent, ...defaultSuggestions])].slice(0, 5);

		suggestionContainer.innerHTML = merged.map(function (s) {
			return `<button type="button" class="ai-search-suggestion" data-smart-query="${escapeHtml(s)}">${escapeHtml(s)}</button>`;
		}).join('');

		const btns = suggestionContainer.querySelectorAll('[data-smart-query]');
		btns.forEach(function (button) {
			button.addEventListener('click', function () {
				const value = button.dataset.smartQuery || '';
				if (input) input.value = value;
				runSearch(value, 1);
			});
		});
	}

	function saveRecentSearch(query) {
		const q = query.trim();
		if (!q) return;
		let recent = [];
		try {
			recent = JSON.parse(localStorage.getItem('ai_recent_searches') || '[]');
		} catch (e) { }

		recent = [q, ...recent.filter(s => s.toLowerCase() !== q.toLowerCase())].slice(0, 5);
		localStorage.setItem('ai_recent_searches', JSON.stringify(recent));
		renderSuggestions();
	}

	function renderProductCard(product) {
		const tags = Array.isArray(product.tags) ? product.tags.filter(Boolean) : [];
		const tagHtml = tags.length ?
			`<div class="ai-product-card__tags">${tags.map(tag => `<span>${escapeHtml(tag)}</span>`).join('')}</div>` :
			'';

		const image = product.image ? escapeHtml(product.image) : '';
		const name = escapeHtml(product.name || 'Sản phẩm');
		const url = escapeHtml(product.url || '#');
		const category = product.category ? `<span>${escapeHtml(product.category)}</span>` : '';
		const color = product.color ? `<span>${escapeHtml(product.color)}</span>` : '';
		const size = product.size ? `<span>${escapeHtml(product.size)}</span>` : '';
		const priceText = escapeHtml(product.price_text || formatPrice(product.price));

		return `
            <article class="ai-product-card">
                <a class="ai-product-card__link" href="${url}">
                    <div class="ai-product-card__image">
                        ${image ? `<img src="${image}" alt="${name}" loading="lazy">` : '<div class="ai-product-card__placeholder">No image</div>'}
                    </div>
                    <div class="ai-product-card__body">
                        <h4 class="ai-product-card__title">${name}</h4>
                        <div class="ai-product-card__price">${priceText}</div>
                        <div class="ai-product-card__meta">
                            ${category}
                            ${color}
                            ${size}
                        </div>
                        ${tagHtml}
                    </div>
                </a>
            </article>
        `;
	}

	function buildPageTokens(totalPages, currentPage) {
		if (totalPages <= 7) {
			return Array.from({ length: totalPages }, function (_, index) {
				return index + 1;
			});
		}

		const pages = [1];
		const minMiddle = Math.max(2, currentPage - 1);
		const maxMiddle = Math.min(totalPages - 1, currentPage + 1);

		if (minMiddle > 2) {
			pages.push(null);
		}

		for (let page = minMiddle; page <= maxMiddle; page += 1) {
			pages.push(page);
		}

		if (maxMiddle < totalPages - 1) {
			pages.push(null);
		}

		pages.push(totalPages);
		return pages;
	}

	function renderSmartPagination(payload) {
		if (!smartPagination) return;

		const totalPages = Math.max(1, Number(payload.total_pages || 1));
		const currentPage = Math.max(1, Number(payload.page || 1));

		if (totalPages <= 1) {
			setSmartPaginationHidden(true);
			return;
		}

		const pageTokens = buildPageTokens(totalPages, currentPage);
		const nodes = [];

		nodes.push(
			`<button type="button" class="btn btn-sm btn-light" data-smart-page="${currentPage - 1}" ${currentPage <= 1 ? 'disabled' : ''}>Prev</button>`
		);

		pageTokens.forEach(function (token) {
			if (token === null) {
				nodes.push('<span class="px-1">...</span>');
				return;
			}

			const isActive = token === currentPage;
			nodes.push(
				`<button type="button" class="btn btn-sm ${isActive ? 'btn-dark' : 'btn-light'}" data-smart-page="${token}" ${isActive ? 'disabled' : ''}>${token}</button>`
			);
		});

		nodes.push(
			`<button type="button" class="btn btn-sm btn-light" data-smart-page="${currentPage + 1}" ${currentPage >= totalPages ? 'disabled' : ''}>Next</button>`
		);

		smartPagination.innerHTML = nodes.join('');
		smartPagination.hidden = false;
	}

	function renderResults(payload, query) {
		const products = Array.isArray(payload.products) ? payload.products : [];
		const count = Number(payload.count || products.length);
		const page = Math.max(1, Number(payload.page || 1));
		const totalPages = Math.max(1, Number(payload.total_pages || 1));
		const startIndex = count > 0 ? ((page - 1) * perPage) + 1 : 0;
		const endIndex = Math.min(count, page * perPage);
		activeQuery = query;
		activePage = page;

		showSmartState();
		setLoading(false);

		if (summary) {
			summary.textContent = query
				? `Kết quả cho "${query}": ${count} sản phẩm. Hiển thị ${startIndex}-${endIndex}`
				: `${count} sản phẩm`;
		}
		if (title) {
			title.textContent = query || 'Kết quả tìm kiếm';
		}

		if (!products.length) {
			if (defaultGrid) defaultGrid.innerHTML = '';
			if (emptyState) emptyState.hidden = false;
			setSmartPaginationHidden(true);
			setStatus(payload.message || 'Không có kết quả phù hợp.', 'warning');
			return;
		}

		if (defaultGrid) {
			defaultGrid.innerHTML = products.map(function (product) {
				return product && product.html ? product.html : renderProductCard(product);
			}).join('');
		}

		if (emptyState) emptyState.hidden = true;
		renderSmartPagination({
			page: page,
			total_pages: totalPages,
		});
		setStatus(payload.message || `Đã tìm thấy ${count} sản phẩm.`, 'success');
	}

	async function runSearch(query, page = 1) {
		const trimmed = String(query || '').trim();
		const requestedPage = Math.max(1, Number(page || 1));
		if (trimmed.length < 2) {
			setStatus('Vui lòng nhập từ khóa dài hơn 1 ký tự để tìm kiếm.', 'warning');
			return;
		}

		setStatus('', '');
		setLoading(true);
		showSmartState();

		try {
			const body = new URLSearchParams();
			body.append('query', trimmed);
			body.append('page', String(requestedPage));
			body.append('per_page', String(perPage));
			body.append('csrf_token', CSRF_TOKEN);
			body.append('_token', CSRF_TOKEN);

			const response = await fetch(endpoint, {
				method: 'POST',
				headers: {
					'X-Requested-With': 'XMLHttpRequest',
					'Accept': 'application/json',
					'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
					'X-CSRF-TOKEN': CSRF_TOKEN
				},
				credentials: 'same-origin',
				body: body.toString()
			});

			const responseText = await response.text();
			let payload = null;
			try {
				payload = responseText ? JSON.parse(responseText) : null;
			} catch (jsonError) {
				payload = null;
			}

			if (!response.ok || !payload) {
				if (response.status === 419) {
					throw new Error('CSRF token không hợp lệ. Vui lòng tải lại trang.');
				}
				if (response.status >= 500) {
					throw new Error('Máy chủ đang gặp lỗi khi xử lý AI search.');
				}
				throw new Error('Không thể xử lý yêu cầu lúc này.');
			}

			if (!payload.success) {
				if (resultsGrid) resultsGrid.innerHTML = '';
				if (emptyState) emptyState.hidden = false;
				setSmartPaginationHidden(true);
				setLoading(false);
				setStatus(payload.message || 'Tìm kiếm thất bại. Vui lòng thử lại.', 'error');
				return;
			}

			renderResults(payload, trimmed);
			saveRecentSearch(trimmed);
		} catch (error) {
			setLoading(false);
			setStatus(error && error.message ? error.message :
				'Không thể kết nối tới AI search. Vui lòng thử lại sau.', 'error');
			showDefaultState();
		}
	}

	if (form) {
		form.addEventListener('submit', function (event) {
			event.preventDefault();
			runSearch(input ? input.value : '', 1);
		});
	}

	if (smartPagination) {
		smartPagination.addEventListener('click', function (event) {
			const pageButton = event.target.closest('[data-smart-page]');
			if (!pageButton || isLoading) return;

			const nextPage = Number(pageButton.getAttribute('data-smart-page') || 0);
			if (!Number.isFinite(nextPage) || nextPage < 1 || nextPage === activePage || activeQuery === '') {
				return;
			}

			runSearch(activeQuery, nextPage);
		});
	}

	resetButtons.forEach(function (button) {
		button.addEventListener('click', function () {
			if (input) input.value = '';
			setStatus('', '');
			setLoading(false);
			showDefaultState();
			if (input) input.focus();
		});
	});

	showDefaultState();
	renderSuggestions();
};

NN_FRAMEWORK.ChatProducts = function () {
	const root = document.querySelector('[data-chat-products-root]');
	if (!root) return;

	const endpoint = root.dataset.chatEndpoint || '';
	const siteName = (root.dataset.chatSiteName || 'Website').trim() || 'Website';
	const launcher = root.querySelector('[data-chat-products-launcher]');
	const openTriggers = root.querySelectorAll('[data-chat-products-open]');
	const dismissBtn = root.querySelector('[data-chat-products-dismiss]');
	const teaser = root.querySelector('.chat-products-widget__teaser');
	const panel = root.querySelector('[data-chat-products-panel]');
	const minimizeBtn = root.querySelector('[data-chat-products-minimize]');
	const resetBtn = root.querySelector('[data-chat-products-reset]');
	const form = root.querySelector('[data-chat-products-form]');
	const input = root.querySelector('[data-chat-products-input]');
	const submit = root.querySelector('[data-chat-products-submit]');
	const messages = root.querySelector('[data-chat-products-messages]');
	const humanButton = root.querySelector('[data-chat-products-human]');

	if (!endpoint || !launcher || !panel || !form || !input || !submit || !messages) return;

	const escapeHtml = function (value) {
		return String(value || '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#39;');
	};

	const formatPrice = function (price) {
		const value = Number(price || 0);
		return value > 0 ? value.toLocaleString('vi-VN') + 'đ' : 'Liên hệ';
	};

	const defaultMessages = [
		`Xin chào Anh/Chị! Em là trợ lý AI của ${siteName}.`,
		'Em rất sẵn lòng hỗ trợ Anh/Chị.'
	];

	const addBubble = function (content, role = 'assistant', isHtml = false, extraClass = '') {
		const bubble = document.createElement('div');
		bubble.className = `chat-products-widget__bubble is-${role}${extraClass ? ` ${extraClass}` : ''}`;
		if (isHtml) {
			bubble.innerHTML = content;
		} else {
			bubble.textContent = content;
		}
		messages.appendChild(bubble);
		messages.scrollTop = messages.scrollHeight;
	};

	const setLoading = function (loading) {
		submit.disabled = !!loading;
		input.disabled = !!loading;
		submit.innerHTML = loading
			? '<span class="chat-products-widget__send-wait">...</span>'
			: '<i class="bi bi-send-fill" aria-hidden="true"></i>';
		submit.classList.toggle('is-loading', !!loading);
	};

	let panelCloseTimer = null;
	const setOpen = function (open, instant = false) {
		if (panelCloseTimer) {
			clearTimeout(panelCloseTimer);
			panelCloseTimer = null;
		}

		if (open) {
			launcher.hidden = true;
			panel.hidden = false;
			if (instant) {
				panel.classList.add('is-open');
			} else {
				requestAnimationFrame(function () {
					panel.classList.add('is-open');
				});
			}
			setTimeout(function () {
				input.focus();
				messages.scrollTop = messages.scrollHeight;
			}, 120);
			return;
		}

		panel.classList.remove('is-open');
		if (instant) {
			panel.hidden = true;
			launcher.hidden = false;
			return;
		}

		panelCloseTimer = setTimeout(function () {
			panel.hidden = true;
			launcher.hidden = false;
		}, 220);
	};

	const resetConversation = function () {
		messages.innerHTML = '';
		defaultMessages.forEach(function (text) {
			addBubble(text, 'assistant');
		});
		if (humanButton) humanButton.hidden = true;
	};

	const setTeaserVisible = function (visible) {
		if (!teaser) return;
		teaser.hidden = !visible;
		launcher.classList.toggle('is-teaser-hidden', !visible);
	};

	let typingBubble = null;
	const showTyping = function () {
		if (typingBubble) return;
		typingBubble = document.createElement('div');
		typingBubble.className = 'chat-products-widget__bubble is-assistant is-typing';
		typingBubble.innerHTML = '<em>Đang nhập tin nhắn</em><span class="chat-products-widget__typing-dots"><span></span><span></span><span></span></span>';
		messages.appendChild(typingBubble);
		messages.scrollTop = messages.scrollHeight;
	};

	const hideTyping = function () {
		if (!typingBubble) return;
		typingBubble.remove();
		typingBubble = null;
	};

	const renderProducts = function (items) {
		if (!Array.isArray(items) || !items.length) return;

		const htmlCards = items
			.map(function (item) {
				return String(item.product_html || '').trim();
			})
			.filter(function (html) {
				return html !== '';
			});

		if (htmlCards.length) {
			addBubble(`<div class="chat-products-widget__product-grid">${htmlCards.join('')}</div>`, 'assistant', true, 'is-products');
			return;
		}

		const htmlItems = items.map(function (item) {
			const name = escapeHtml(item.name || 'Sản phẩm');
			const url = escapeHtml(item.product_url || '#');
			const price = escapeHtml(formatPrice(item.price));
			return `<li><a href="${url}" target="_blank" rel="noopener noreferrer">${name}</a> - ${price}</li>`;
		});

		addBubble(`<ul class="chat-products-widget__product-list">${htmlItems.join('')}</ul>`, 'assistant', true);
	};

	openTriggers.forEach(function (trigger) {
		trigger.addEventListener('click', function () {
			setOpen(true);
		});
	});

	if (minimizeBtn) {
		minimizeBtn.addEventListener('click', function () {
			setOpen(false);
		});
	}

	if (resetBtn) {
		resetBtn.addEventListener('click', function () {
			resetConversation();
		});
	}

	if (dismissBtn) {
		dismissBtn.addEventListener('click', function (event) {
			event.preventDefault();
			event.stopPropagation();
			setTeaserVisible(false);
		});
	}

	form.addEventListener('submit', async function (event) {
		event.preventDefault();
		const query = String(input.value || '').trim();
		if (query.length < 2) return;

		addBubble(query, 'user');
		input.value = '';
		setLoading(true);
		showTyping();
		if (humanButton) humanButton.hidden = true;

		try {
			const response = await fetch(`${endpoint}?message=${encodeURIComponent(query)}`, {
				method: 'GET',
				headers: {
					'Accept': 'application/json',
					'X-Requested-With': 'XMLHttpRequest'
				},
				credentials: 'same-origin'
			});

			const payload = await response.json();
			const status = String(payload.status || '');
			const message = String(payload.message || '');
			hideTyping();

			if (status === 'success') {
				addBubble(message || 'Đây là các sản phẩm phù hợp.', 'assistant');
				renderProducts(payload.data || []);
			} else if (status === 'no_products') {
				addBubble(message || 'Chưa tìm thấy sản phẩm phù hợp. Vui lòng liên hệ tư vấn viên.', 'assistant');
				if (humanButton) humanButton.hidden = false;
			} else if (status === 'out_of_scope') {
				addBubble(message || 'Em chỉ hỗ trợ câu hỏi mua sắm sản phẩm trên website.', 'assistant');
			} else {
				addBubble(message || 'Không thể xử lý yêu cầu lúc này.', 'assistant');
			}
		} catch (error) {
			addBubble('Không thể kết nối lúc này. Vui lòng thử lại.', 'assistant');
		} finally {
			hideTyping();
			setLoading(false);
		}
	});

	if (humanButton) {
		humanButton.addEventListener('click', function () {
			const base = (typeof BASE === 'string' && BASE.length) ? BASE : '/';
			window.location.href = base + 'lien-he';
		});
	}

	resetConversation();
	setTeaserVisible(true);
	setOpen(false, true);
};
/* Ready */
$(document).ready(function () {
	NN_FRAMEWORK.Api();
	NN_FRAMEWORK.Popup();
	NN_FRAMEWORK.Swiper();
	NN_FRAMEWORK.GoTop();
	NN_FRAMEWORK.LazyBackgroundLoading();
	NN_FRAMEWORK.Menu();
	NN_FRAMEWORK.Videos();
	NN_FRAMEWORK.Search();
	NN_FRAMEWORK.PasswordToggle();
	NN_FRAMEWORK.DomChange();
	NN_FRAMEWORK.loadNameInputFile();
	NN_FRAMEWORK.HomeRecommend();
	NN_FRAMEWORK.Properties();
	NN_FRAMEWORK.ProductCard();
	NN_FRAMEWORK.QuickView();
	NN_FRAMEWORK.VoucherHome();
	NN_FRAMEWORK.Main();
	NN_FRAMEWORK.SmartSearch();
	NN_FRAMEWORK.ChatProducts();
	NN_FRAMEWORK.NavSmartSearch();
	if (isExist($('.comment-page'))) {
		new Comments('.comment-page', BASE);
	}
	new Cart(BASE);
});

window.addEventListener('load', () => {
	NN_FRAMEWORK.Img();
});


