/* Validation form */
function validateForm(ele) {
	window.addEventListener(
		'load',
		function () {
			var forms = document.getElementsByClassName(ele);
			var validation = Array.prototype.filter.call(forms, function (form) {
				form.addEventListener(
					'submit',
					function (event) {
						if (form.checkValidity() === false) {
							event.preventDefault();
							event.stopPropagation();
						}
						form.classList.add('was-validated');
					},
					false
				);
			});
			$('.' + ele)
				.find('input[type=submit],button[type=submit]')
				.removeAttr('disabled');
		},
		false
	);
}

/* Validation form chung */
validateForm('validation-form');

function isExist(ele) {
	return ele.length;
}

function getLen(str) {
	return /^\s*$/.test(str) ? 0 : str.length;
}
const cache_input_value = (selector, target_child_selector, child_key) => {
	window['cache_input_value'] = {};
	const ele = document.querySelector(selector),
		mo = new MutationObserver((entries) => {
			for (const entry of entries) {
				// console.log(entry);
				if (entry.type == 'childList') {
					const target_children = ele.querySelectorAll(target_child_selector);
					if (target_children) {
						if (window['cache_input_value']) {
							for (const k in window['cache_input_value']) {
								const v = window['cache_input_value'][k];
								const founded = document.querySelector('[' + child_key + '=' + k + ']');
								if (founded) founded.value = v;
							}
						}
						for (const ele of target_children) {
							ele.addEventListener('change', (e) => {
								const key = e.target.getAttribute(child_key);
								window['cache_input_value'][key.replace(/-/g, '_')] = e.target.value;
							});
						}
					}
				}
			}
		});
	if (ele) {
		mo.observe(ele, {
			childList: true,
			subtree: true
		});
	}
};
cache_input_value('.group-properties', 'input', 'id');
/* onChange Category */
function filterCategory(url) {
	if ($('.filter-category').length > 0 && url != '') {
		var id = '';
		var value = 0;

		if (url && url != 0) url = url + '?search=' + COM;
		$('.filter-category').each(function (index) {
			id = $(this).attr('id');

			if (id) {
				value = parseInt($('#' + id).val());
				if (value) {
					url += '&' + id + '=' + value;
				}
			}
		});
	} else {
		url = url + '?search=' + COM;
	}

	return url;
}

function onchangeCategory(obj) {
	var name = '';
	var keyword = $('#keyword').val();
	var url = LINK_FILTER;

	obj.parents('.form-group')
		.nextAll()
		.each(function () {
			name = $(this).find('.filter-category').attr('name');
			if ($(this) != obj) {
				$(this).find('.filter-category').val(0);
			}
		});

	url = filterCategory(url);

	if (keyword) {
		url += '&keyword=' + encodeURI(keyword);
	}

	return (window.location = url);
}

/* Search */
function doEnter(evt, obj, url) {
	if (url == '') {
		notifyDialog('ưng dẫn không hợp lệ');
		return false;
	}

	if (evt.keyCode == 13 || evt.which == 13) {
		onSearch(obj, url);
	}
}
function onSearch(obj, url) {
	if (url == '') {
		notifyDialog('ưng dẫn không hợp lệ');
		return false;
	} else {
		var keyword = $('#' + obj).val();
		url = filterCategory(url);

		if (keyword) {
			url += '&keyword=' + encodeURI(keyword);
		}
		window.location = url;
	}
}

function readMail(check = 0, id = 0) {
	$.ajax({
		type: 'POST',
		url: 'readmail',
		data: {
			check: check,
			id: id,
			csrf_token: CSRF_TOKEN
		},
		success: function (data) {
			$('.count-inbox').html(data);
		}
	});
}

/* Action order (Search - Export excel - Export word) */
function actionOrder(url) {
	var listid = '';
	var order_status = parseInt($('#order_status').val());
	var order_payment = parseInt($('#order_payment').val());
	var order_date = $('#flatpickr-range').val();
	var price_from = $('.price_from').val();
	var price_to = $('.price_to').val();
	var city = parseInt($('#id_city').val());
	var district = parseInt($('#id_district').val());
	var ward = parseInt($('#id_ward').val());
	var keyword = $('#keyword').val();

	$('input.select-checkbox').each(function () {
		if (this.checked) listid = listid + ',' + this.value;
	});

	listid = listid.substr(1);
	url += '?search=' + COM;
	if (listid) url += '&listid=' + listid;
	if (order_status) url += '&order_status=' + order_status;
	if (order_payment) url += '&order_payment=' + order_payment;
	if (order_date) url += '&order_date=' + order_date;
	if (price_from) url += '&price_from=' + price_from;
	if (price_to) url += '&price_to=' + price_to;
	if (city) url += '&id_city=' + city;
	if (district) url += '&id_district=' + district;
	if (ward) url += '&id_ward=' + ward;
	if (keyword) url += '&keyword=' + encodeURI(keyword);

	window.location = url;
}

function actionLog(url) {
	var log_date = $('#flatpickr-range').val();
	var keyword = $('#keyword').val();
	url += '?search=' + COM;
	if (log_date) url += '&log_date=' + log_date;
	if (keyword) url += '&keyword=' + encodeURI(keyword);
	window.location = url;
}

function cardProperties() {
	var propertiesCache = collectPropertiesCache();
	var formData = new FormData(form);
	$.ajax({
		type: 'POST',
		url: 'properties',
		data: formData,
		cache: false,
		contentType: false,
		processData: false,
		success: function (data) {
			$('.group-properties').html(data);
			$('.format-price').priceFormat({
				limit: 13,
				prefix: '',
				centsLimit: 0
			});
			initPropertiesPhotoSelect($('.group-properties'));
			initPropertiesStatusSelect($('.group-properties'));
			applyPropertiesCache($('.group-properties'), propertiesCache);
			updatePropertiesTotalQuantity();

			var $group = $('.group-properties');
			var mainRegular = $('#regular_price').val();
			var mainSale = $('#sale_price').val();
			var mainCode = $('#code').val();

			if ($group.length) {
				var $rows = $group.find('.flex-propertiescard');
				var $regularInputs = $rows.find('.regular_price');
				var $saleInputs = $rows.find('.sale_price');
				var $codeInputs = $rows.find('.properties_code');

				if (!$regularInputs.length) {
					$regularInputs = $rows.find("input[name^='propertiescard[regular_price]']");
				}
				if (!$saleInputs.length) {
					$saleInputs = $rows.find("input[name^='propertiescard[sale_price]']");
				}
				if (!$codeInputs.length) {
					$codeInputs = $rows.find("input[name^='propertiescard[code]']");
				}

				updatePropertiesPrices($rows, mainRegular, mainSale);

				if (mainCode) {
					updatePropertiesCodes($rows, mainCode);
					$('#code').data('codeBase', mainCode);
				}

				$rows.each(function () {
					updatePropertiesDiscount($(this));
				});
				updatePropertiesTotalQuantity();
			}
		}
	});
}

function collectPropertiesCache() {
	var cache = {};
	$('.group-properties .flex-propertiescard').each(function () {
		var $wrap = $(this);
		var idProps = $wrap.find("input[name^='propertiescard[id_properties]']").val();
		if (!idProps) return;

		cache[idProps] = {
			code: $wrap.find("input[name^='propertiescard[code]']").val() || '',
			regular_price: $wrap.find("input[name^='propertiescard[regular_price]'], .regular_price").val() || '',
			sale_price: $wrap.find("input[name^='propertiescard[sale_price]'], .sale_price").val() || '',
			discount: $wrap.find("input[name^='propertiescard[discount]'], .discount").val() || '',
			quantity: $wrap.find("input[name^='propertiescard[quantity]']").val() || '',
			number: $wrap.find("input[name^='propertiescard[number]']").val() || '',
			status: $wrap.find("select[name^='propertiescard[status]'], .properties_status").val() || '',
			id_photo: $wrap.find("select[name^='propertiescard[id_photo]'], .properties_id_photo").val() || '0'
		};
	});
	return cache;
}

function applyPropertiesCache($group, cache) {
	if (!$group || !$group.length) return;
	$group.find('.flex-propertiescard').each(function () {
		var $wrap = $(this);
		var idProps = $wrap.find("input[name^='propertiescard[id_properties]']").val();
		if (!idProps || !cache[idProps]) return;

		var data = cache[idProps];
		var $code = $wrap.find("input[name^='propertiescard[code]']");
		if ($code.length) $code.val(data.code);

		var $regular = $wrap.find("input[name^='propertiescard[regular_price]'], .regular_price");
		var $sale = $wrap.find("input[name^='propertiescard[sale_price]'], .sale_price");
		if ($regular.length) $regular.val(data.regular_price);
		if ($sale.length) $sale.val(data.sale_price);

		var $discount = $wrap.find("input[name^='propertiescard[discount]'], .discount");
		if ($discount.length) $discount.val(data.discount);

		var $quantity = $wrap.find("input[name^='propertiescard[quantity]']");
		if ($quantity.length) $quantity.val(data.quantity);
		var $number = $wrap.find("input[name^='propertiescard[number]']");
		if ($number.length) $number.val(data.number);

		var $status = $wrap.find("select[name^='propertiescard[status]'], .properties_status");
		if ($status.length) $status.val(data.status);

		var $photo = $wrap.find("select[name^='propertiescard[id_photo]'], .properties_id_photo");
		if ($photo.length && data.id_photo) {
			$photo.val(data.id_photo).trigger('change');
		}

		ensurePropertiesStatus($wrap);
	});
	ensurePropertiesNumberSequence();
}

function updatePropertiesCodes($rows, mainCode) {
	if (!$rows || !$rows.length || !mainCode) return;
	$rows.each(function (index) {
		var $wrap = $(this);
		var $input = $wrap.find('.properties_code');
		if (!$input.length) $input = $wrap.find("input[name^='propertiescard[code]']");
		if (!$input.length) return;

		var num = String(index + 1).padStart(2, '0');
		$input.val(mainCode + '-' + num);
	});
}

function updatePropertiesPrices($rows, mainRegular, mainSale) {
	if (!$rows || !$rows.length) return;
	$rows.each(function () {
		var $wrap = $(this);
		var $regular = $wrap.find('.regular_price');
		var $sale = $wrap.find('.sale_price');

		if (!$regular.length) $regular = $wrap.find("input[name^='propertiescard[regular_price]']");
		if (!$sale.length) $sale = $wrap.find("input[name^='propertiescard[sale_price]']");

		if (typeof mainRegular !== 'undefined' && mainRegular !== null && mainRegular !== '') {
			$regular.val(mainRegular);
		}
		if (typeof mainSale !== 'undefined' && mainSale !== null && mainSale !== '') {
			$sale.val(mainSale);
		}

		updatePropertiesDiscount($wrap);
	});
}

function ensurePropertiesStatus($wrap, showNotify) {
	if (!$wrap || !$wrap.length) return;
	var $qty = $wrap.find(".properties_quantity");
	var $status = $wrap.find(".properties_status");
	if (!$status.length) return;
	if (typeof showNotify === 'undefined') showNotify = false;

	var qty = 0;
	if ($qty.length) {
		var raw = ($qty.val() || '').toString().replace(/,/g, '');
		qty = parseInt(raw, 10);
		if (isNaN(qty)) qty = 0;
	}

	if (qty <= 0 && $status.val() === 'active') {
		$status.val('inactive');
		if (showNotify && typeof notifyDialog === 'function') {
			notifyDialog('Số lượng phải lớn hơn 0 để bật Hoạt động');
		}
	}

	togglePropertiesInactiveState($wrap, $status.val() === 'inactive');
}

function togglePropertiesInactiveState($wrap, inactive) {
	if (!$wrap || !$wrap.length) return;
	if (inactive) {
		$wrap.addClass('properties-inactive');
	} else {
		$wrap.removeClass('properties-inactive');
	}
}

function updatePropertiesTotalQuantity() {
	var total = 0;
	$('.group-properties .properties_quantity').each(function () {
		var raw = ($(this).val() || '').toString().replace(/,/g, '');
		var num = parseInt(raw, 10);
		if (!isNaN(num)) total += num;
	});
	var $target = $('#properties_total_quantity');
	if ($target.length) $target.val(total);
}

function ensurePropertiesNumberSequence() {
	$('.group-properties .flex-propertiescard').each(function (index) {
		var $number = $(this).find("input[name^='propertiescard[number]']");
		if (!$number.length) return;
		var current = ($number.val() || '').toString().trim();
		if (!current) {
			$number.val(index + 1);
		}
	});
}

function updatePropertiesDiscount($wrap) {
	var $regular = $wrap.find('.regular_price');
	var $sale = $wrap.find('.sale_price');
	var $discount = $wrap.find('.discount');

	if (!$regular.length) $regular = $wrap.find("input[name^='propertiescard[regular_price]']");
	if (!$sale.length) $sale = $wrap.find("input[name^='propertiescard[sale_price]']");
	if (!$discount.length) $discount = $wrap.find("input[name^='propertiescard[discount]']");

	var regular_price = $regular.val();
	var sale_price = $sale.val();
	var discount = 0;

	if (regular_price && sale_price) {
		regular_price = regular_price.replace(/,/g, '');
		sale_price = sale_price.replace(/,/g, '');
		regular_price = parseFloat(regular_price);
		sale_price = parseFloat(sale_price);

		if (regular_price > 0 && sale_price > 0 && sale_price < regular_price) {
			discount = 100 - (sale_price * 100) / regular_price;
			discount = roundNumber(discount, 0);
		}
	}

	if ($discount.length) {
		$discount.val(discount);
	}
}

function initPropertiesPhotoSelect($scope) {
	if (!$scope || !$scope.length) return;
	if (!$.fn.select2) {
		setTimeout(function () { initPropertiesPhotoSelect($scope); }, 100);
		return;
	}
	var $selects = $scope.find('.properties_id_photo');
	$selects.each(function () {
		var $select = $(this);
		if ($select.data('select2')) {
			$select.select2('destroy');
			var $parent = $select.parent();
			if ($parent.hasClass('position-relative') && $parent.hasClass('w-100')) {
				$select.unwrap();
			}
		}

		if (!$select.parent().hasClass('position-relative')) {
			$select.wrap('<div class="position-relative w-100"></div>');
		}
		$select.select2({
			placeholder: $select.data('placeholder') || 'chọn hình',
			width: '100%',
			templateResult: formatPropertiesPhotoOption,
			templateSelection: formatPropertiesPhotoOption,
			escapeMarkup: function (markup) { return markup; }
		});
	});
}

function initPropertiesStatusSelect($scope) {
	if (!$scope || !$scope.length) return;
	if (!$.fn.select2) {
		setTimeout(function () { initPropertiesStatusSelect($scope); }, 100);
		return;
	}
	var $selects = $scope.find('.properties_status');
	$selects.each(function () {
		var $select = $(this);
		if ($select.data('select2')) {
			$select.select2('destroy');
			if ($select.parent().hasClass('position-relative')) $select.unwrap();
		}
		if (!$select.parent().hasClass('position-relative')) {
			$select.wrap('<div class="position-relative w-100"></div>');
		}
		$select.select2({
			placeholder: $select.data('placeholder') || 'Trạng thái',
			width: '100%',
			minimumResultsForSearch: Infinity
		});
	});
}

function formatPropertiesPhotoOption(state) {
	if (!state.id) return state.text;
	var $opt = $(state.element);
	var img = $opt.data('image') || $opt.data('image-full');
	if (!img) return state.text;
	return '<span class="properties-photo-option"><img src="' + img + '" class="properties-photo-thumb" alt=""><span class="properties-photo-label">' + (state.text || '') + '</span></span>';
}

function updatePropertiesPhotoPreview($select) {
	if (!$select || !$select.length) return;
	var $wrap = $select.closest('.properties-photo');
	var img = $select.find('option:selected').data('image') || $select.find('option:selected').data('image-full') || '';
	var $preview = $wrap.find('.properties-photo-preview');

	if (!img) {
		$preview.empty();
		renderPropertiesPhotoSelection($select, '');
		return;
	}

	$preview.html('<img src="' + img + '" class="img-thumbnail properties-photo-img" alt="photo">');
	renderPropertiesPhotoSelection($select, img);
}

function renderPropertiesPhotoSelection($select, img) {
	if (!$select || !$select.length) return;
	var $container = $select.next('.select2');
	if (!$container.length) return;
	var $rendered = $container.find('.select2-selection__rendered');
	if (!$rendered.length) return;
	if (!img) {
		$rendered.text('');
		return;
	}
	$rendered.html('<span class="properties-photo-option"><img src="' + img + '" class="properties-photo-thumb" alt=""></span>');
}

function emailStarred(starred = 0, id = 0) {
	holdonOpen();
	document.location = 'newsletters/starred/' + TYPE + '?starred=' + starred + '&id=' + id;
}

/* Delete item */
function deleteItem(url) {
	holdonOpen();
	document.location = url;
}

/* Delete all */
function deleteAll(url) {
	var listid = '';
	const parts = url.split('?');
	var url = parts[0];
	const params = parts.length > 1 ? parts[1] : null;

	$('table tbody input.form-check-input').each(function () {
		if (this.checked) {
			listid = listid + ',' + parseInt(this.value);
		}
	});
	$('ul.list-unstyled input.form-check-input').each(function () {
		if (this.checked) {
			listid = listid + ',' + parseInt(this.value);
		}
	});
	listid = listid.substr(1);
	if (listid == '') {
		notifyDialog('Bạn phải chọn ít nhất 1 mục để xóa');
		return false;
	}
	holdonOpen();
	document.location = url + '?listid=' + listid + '&' + params;
}

/* Push OneSignal */
function pushOneSignal(url) {
	document.location = url;
}

/* HoldOn */
function holdonOpen(
	theme = 'sk-circle',
	text = 'Loading...',
	backgroundColor = 'rgba(0,0,0,0.8)',
	textColor = 'white'
) {
	var options = {
		theme: theme,
		message: text,
		backgroundColor: backgroundColor,
		textColor: textColor
	};
	HoldOn.open(options);
}
function holdonClose() {
	HoldOn.close();
}

/* Go to element */
function goToByScroll(id, minusTop) {
	minusTop = parseInt(minusTop) ? parseInt(minusTop) : 0;
	id = id.replace('#', '');
	$('html,body').animate(
		{
			scrollTop: $('#' + id).offset().top - minusTop
		},
		'slow'
	);
}

/* Show notify */
function showNotify(text = 'Notify text', title = 'Thông báo', status = 'success') {
	new Notify({
		status: status, // success, warning, error
		title: title,
		text: text,
		effect: 'fade',
		speed: 400,
		customClass: null,
		customIcon: null,
		showIcon: true,
		showCloseButton: true,
		autoclose: true,
		autotimeout: 3000,
		gap: 10,
		distance: 10,
		type: 3,
		position: 'right top'
	});
}

/* Notify */
function notifyDialog(content = '', title = 'Thông báo', icon = 'fas fa-exclamation-triangle', type = 'red') {
	$.alert({
		title: title,
		icon: icon, // font awesome
		type: type, // red, green, orange, blue, purple, dark
		content: content, // html, text
		backgroundDismiss: true,
		animationSpeed: 600,
		animation: 'zoom',
		closeAnimation: 'scale',
		typeAnimated: true,
		animateFromElement: false,
		autoClose: 'accept|3000',
		escapeKey: 'accept',
		buttons: {
			accept: {
				text: '<i class="fas fa-check align-middle mr-2"></i>' + 'Đồng ý',
				btnClass: 'btn-blue btn-sm bg-gradient-primary'
			}
		}
	});
}

/* Confirm */
function confirmDialog(action, text, value, title = 'Thông báo', icon = 'fas fa-exclamation-triangle', type = 'red') {
	$.confirm({
		title: title,
		icon: icon, // font awesome
		type: type, // red, green, orange, blue, purple, dark
		content: text, // html, text
		backgroundDismiss: true,
		animationSpeed: 600,
		animation: 'zoom',
		closeAnimation: 'scale',
		typeAnimated: true,
		animateFromElement: false,
		autoClose: 'cancel|3000',
		escapeKey: 'cancel',
		buttons: {
			success: {
				text: '<i class="fas fa-check align-middle mr-2"></i>' + 'Đồng ý',
				btnClass: 'btn-blue btn-sm bg-gradient-primary',
				action: function () {
					if (action == 'create-seo') seoCreate();
					if (action == 'push-onesignal') pushOneSignal(value);
					if (action == 'send-email') sendEmail();
					if (action == 'delete-filer') deleteFiler(value);
					if (action == 'delete-all-filer') deleteAllFiler(value);
					if (action == 'delete-item') deleteItem(value);
					if (action == 'delete-all') deleteAll(value);
					if (action == 'delete-photo') deletePhoto(value);
				}
			},
			cancel: {
				text: '<i class="fas fa-times align-middle mr-2"></i>' + 'Hủy bỏ',
				btnClass: 'btn-red btn-sm bg-gradient-danger'
			}
		}
	});
}
function deletePhoto(_root) {
	let id = _root.data('id');
	let upload = _root.data('upload');
	let key = _root.data('key');
	let table = _root.data('table');
	$.ajax({
		url: 'deletephoto',
		type: 'POST',
		data: { id: id, upload: upload, key: key, table: table, csrf_token: CSRF_TOKEN },
		success: function (data) {
			_root.parents('.photoUpload-detail').html(data);
		}
	});
}
/* Rounde number */
function roundNumber(rnum, rlength) {
	return Math.round(rnum * Math.pow(10, rlength)) / Math.pow(10, rlength);
}

/* Max Datetime Picker */
function maxDate(element) {
	if (MAX_DATE) {
		$(element).datetimepicker({
			timepicker: false,
			format: 'd/m/Y',
			formatDate: 'd/m/Y',
			// minDate: '1950/01/01',
			maxDate: MAX_DATE
		});
	}
}

/* Min Datetime Picker */
function minDate(element) {
	if (MAX_DATE) {
		$(element).datetimepicker({
			timepicker: false,
			format: 'd/m/Y',
			formatDate: 'd/m/Y',
			minDate: MAX_DATE
			// maxDate: MAX_DATE
		});
	}
}

/* Youtube preview */
function youtubePreview(url, element) {
	var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/;
	var match = url.match(regExp);
	url = match && match[7].length == 11 ? match[7] : false;

	$(element)
		.attr('src', '//www.youtube.com/embed/' + url)
		.css({ width: '250', height: '150' });
}

/* Slug */
function slugConvert(slug, focus = false) {
	slug = (slug || '').toLowerCase();
	if (slug.normalize) {
		slug = slug.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
	}
	slug = slug.replace(/\u0111/g, 'd');
	slug = slug.replace(/[^a-z0-9\s-]/g, '');
	slug = slug.replace(/\s+/g, '-');
	slug = slug.replace(/-+/g, '-');
	if (!focus) {
		slug = slug.replace(/^\-+|\-+$/g, '');
	}
	return slug;
}



function slugPreview(title, lang, focus = false) {
	var slug = slugConvert(title, focus);

	$('#slug' + lang).val(slug);
	$('#slugurlpreview' + lang + ' strong').html(slug);
	$('#seourlpreview' + lang + ' strong').html(slug);
}
function slugPreviewTitleSeo(title, lang) {
	if ($('#title' + lang).length) {
		var titleSeo = $('#title' + lang).val();
		if (!titleSeo) {
			if (title) $('#title-seo-preview' + lang).html(title);
			else $('#title-seo-preview' + lang).html('Title');
		}
	}
}

function slugAlert(result, lang) {
	if (result == 1) {
		$('#alert-slug-danger' + lang).addClass('d-none');
		$('#alert-slug-success' + lang).removeClass('d-none');
	} else if (result == 0) {
		$('#alert-slug-danger' + lang).removeClass('d-none');
		$('#alert-slug-success' + lang).addClass('d-none');
	} else if (result == 2) {
		$('#alert-slug-danger' + lang).addClass('d-none');
		$('#alert-slug-success' + lang).addClass('d-none');
	}
}
function slugCheck() {
	var sluglang = 'vi,en';
	var slugInput = $('.slug-input');
	var id = $('.slug-id').val();
	var copy = $('.slug-copy').val();

	slugInput.each(function (index) {
		var slugId = $(this).attr('id');
		var slug = $(this).val();
		var lang = slugId.substr(slugId.length - 2);
		if (sluglang.indexOf(lang) >= 0) {
			if (slug) {
				$.ajax({
					url: 'slug',
					type: 'get',
					dataType: 'html',
					async: false,
					data: {
						slug: slug,
						id: id,
						copy: copy
					},
					success: function (result) {
						slugAlert(result, lang);
					}
				});
			}
		}
	});
}

/* Random password */
function randomPassword() {
	var str = '';

	for (i = 0; i < 9; i++) {
		str += '!@#$%^&*()?abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'.charAt(
			Math.floor(Math.random() * 62)
		);
	}

	$('#new-password').val(str);
	$('#renew-password').val(str);
	$('#show-password').html(str);
}

/* Check permissions */
function loadPermissions() {
	if (
		$('.card-permission').find('input[type=checkbox]:checked').length ==
		$('.card-permission').find('input[type=checkbox]').length
	) {
		$('input#selectall-checkbox').prop('checked', true);
	} else {
		$('input#selectall-checkbox').prop('checked', false);
	}
}
$(document).on('change', '.file_upload_video', function (evt) {
	var $source = $('#video_here');
	$source[0].src = URL.createObjectURL(this.files[0]);
	$source.parent()[0].load();
});
$(document).ready(function () {
	var url = location.href;

	// Regular expression to extract the value of the 'com' parameter
	var com = /[?&]com=([^&]+)/;
	// Use the match method to get an array of matches
	var match = url.match(com);
	// Check if there is a match and extract the value
	var comValue = match ? match[1] : null;
	if (comValue) {
		var layoutMenuActive = document.getElementsByClassName('menu-sub');
		layoutMenuActive.forEach(function (element, index) {
			let comIn = element.href.match(com);
			let comValue1 = comIn ? comIn[1] : null;
			if (comValue1 == comValue) {
				element.closest('.menu-item-main').classList.add('open');
			}
		});
	}

	$('body').on('click', '.delete-photo', function (event) {
		confirmDialog('delete-photo', 'Bạn muốn xóa hình ảnh này', $(this));
	});
	/* Loader */
	if ($('.loader-wrapper').length) {
		setTimeout(function () {
			$('.loader-wrapper').fadeOut('medium');
		}, 300);
	}

	/* Login */
	if (LOGIN_PAGE) {
		$('#username, #password').keypress(function (event) {
			if (event.keyCode == 13 || event.which == 13) $('#loginadmin').trigger('submit');
		});
	}

	/* Import excell */
	if (IMPORT_IMAGE_EXCELL && $('.copy-excel').length) {
		$('.copy-excel').click(function () {
			var text = $(this).data('text');
			var dummy = document.createElement('input');

			dummy.select();
			dummy.setSelectionRange(0, 99999);
			navigator.clipboard.writeText(text);

			if (!$(this).hasClass('active')) {
				$(this).addClass('active');
				$(this).html('Copied');
			}
		});
	}

	/* Product */
	if ($('#regular_price').length && $('#sale_price').length) {
		$('#regular_price, #sale_price').on('input keyup change', function () {
			var regular_price = $('#regular_price').val();
			var sale_price = $('#sale_price').val();
			var discount = 0;

			if (regular_price == '' || regular_price == '0' || sale_price == '' || sale_price == '0') {
				discount = 0;
			} else {
				regular_price = regular_price.replace(/,/g, '');
				sale_price = sale_price ? sale_price.replace(/,/g, '') : 0;
				regular_price = parseInt(regular_price);
				sale_price = parseInt(sale_price);

				if (sale_price < regular_price) {
					discount = 100 - (sale_price * 100) / regular_price;
					discount = roundNumber(discount, 0);
				} else {
					$('#regular_price, #sale_price').val(0);
					if ($('#discount').length) {
						discount = 0;
						$('#sale_price').val(0);
					}
				}
			}

			if ($('#discount').length) {
				$('#discount').val(discount);
			}
		});
	}

	/* Product properties */
	if ($('.group-properties').length) {
		$(document).on('input keyup change', ".group-properties input[name^='propertiescard[regular_price]'], .group-properties input[name^='propertiescard[sale_price]'], .group-properties .regular_price, .group-properties .sale_price", function () {
			updatePropertiesDiscount($(this).closest('.flex-propertiescard'));
		});

		$(document).on('input keyup change', ".group-properties .properties_quantity", function () {
			updatePropertiesTotalQuantity();
		});

		$(document).on('change', ".group-properties .properties_status", function () {
			ensurePropertiesStatus($(this).closest('.flex-propertiescard'), true);
		});

		$(document).on('input keyup change', ".group-properties .properties_quantity", function () {
			ensurePropertiesStatus($(this).closest('.flex-propertiescard'));
		});

		$(document).on('change', '.group-properties .properties_id_photo', function () {
			updatePropertiesPhotoPreview($(this));
		});

		$(document).on('click', '.refresh-properties-gallery', function () {
			var $btn = $(this);
			var target = $btn.data('target');
			var $select = target ? $(target) : $btn.closest('.properties-photo').find('.properties_id_photo');
			var idProduct = $('input[name="id"]').val();
			var type = (typeof TYPE !== 'undefined') ? TYPE : '';

			if (!idProduct) return;

			$.ajax({
				url: 'productGallery',
				type: 'POST',
				dataType: 'json',
				data: {
					id: idProduct,
					type: type,
					csrf_token: CSRF_TOKEN
				},
				success: function (items) {
					if (!$select.length) return;
					var current = $select.val();
					$select.empty().append('<option value="0">Chọn hình</option>');
					if (items && items.length) {
						items.forEach(function (item) {
							var text = item.photo ? item.photo : String(item.id);
							var opt = $('<option>')
								.val(item.id)
								.attr('data-image', item.thumb || '')
								.attr('data-image-full', item.full || '')
								.text(text);
							$select.append(opt);
						});
					}
					if (current) $select.val(current);
					$select.trigger('change');
				}
			});
		});

		$(document).on('input keyup change', '#code', function () {
			var $code = $(this);
			var mainCode = $code.val();
			if (!mainCode) return;
			var $rows = $('.group-properties .flex-propertiescard');
			updatePropertiesCodes($rows, mainCode);
			$code.data('codeBase', mainCode);
		});

		$(document).on('input keyup change', '#regular_price, #sale_price', function () {
			var $rows = $('.group-properties .flex-propertiescard');
			updatePropertiesPrices($rows, $('#regular_price').val(), $('#sale_price').val());
		});
	}

	if ($('.group-properties').length) {
		updatePropertiesPrices(
			$('.group-properties .flex-propertiescard'),
			($('#regular_price').val() || '').toString().trim(),
			($('#sale_price').val() || '').toString().trim()
		);

		var mainCode = ($('#code').val() || '').toString().trim();
		if (mainCode) {
			updatePropertiesCodes($('.group-properties .flex-propertiescard'), mainCode);
			$('#code').data('codeBase', mainCode);
		}

		initPropertiesPhotoSelect($('.group-properties'));
		$('.group-properties .properties_id_photo').each(function () {
			updatePropertiesPhotoPreview($(this));
		});
		initPropertiesStatusSelect($('.group-properties'));
		ensurePropertiesNumberSequence();
		updatePropertiesTotalQuantity();
	}

	if ($('.group-properties').length) {
		$('.group-properties .flex-propertiescard').each(function () {
			ensurePropertiesStatus($(this));
		});
	}

	/* Setting */
	if ($('.mailertype').length) {
		$('.mailertype').click(function () {
			var value = parseInt($(this).val());

			if (value == 1) {
				$('.host-email').removeClass('d-none');
				$('.host-email').addClass('d-block');
				$('.gmail-email').removeClass('d-block');
				$('.gmail-email').addClass('d-none');
			}
			if (value == 2) {
				$('.gmail-email').removeClass('d-none');
				$('.gmail-email').addClass('d-block');
				$('.host-email').removeClass('d-block');
				$('.host-email').addClass('d-none');
			}
		});
	}

	/* Status */
	if ($('.status-ex').length) {
		$('.status-ex').click(function () {
			var value = parseInt($(this).val());

			if (value == 1) {
				$('.cb-status').removeClass('d-none');
				$('.cb-status').addClass('d-block');
				$('.nc-status').removeClass('d-block');
				$('.nc-status').addClass('d-none');
			}
			if (value == 2) {
				$('.nc-status').removeClass('d-none');
				$('.nc-status').addClass('d-block');
				$('.cb-status').removeClass('d-block');
				$('.cb-status').addClass('d-none');
			}
		});
	}

	/* Max Datetime Picker */
	if ($('.max-date').length) {
		maxDate('.max-date');
	}

	/* Min Datetime Picker */
	if ($('.min-date').length) {
		minDate('.min-date');
	}
	/* Select 2 */
	const select2 = $('.select2');
	if (select2.length && !$('#emailComposeSidebarLabel').length) {
		select2.each(function () {
			var $this = $(this);
			if ($this.hasClass('properties_id_photo')) {
				if ($this.data('select2')) {
					$this.select2('destroy');
					if ($this.parent().hasClass('position-relative')) $this.unwrap();
				}
				$this.wrap('<div class="position-relative"></div>').select2({
					placeholder: $this.data('placeholder') || 'chọn hình',
					dropdownParent: $this.parent(),
					templateResult: formatPropertiesPhotoOption,
					templateSelection: formatPropertiesPhotoOption,
					escapeMarkup: function (markup) { return markup; }
				});
			} else {
				$this.wrap('<div class="position-relative"></div>').select2({
					placeholder: 'chọn danh mục',
					dropdownParent: $this.parent()
				});
			}
		});
	}

	if ($('#selectGroup').length && !$('#emailComposeSidebarLabel').length) {
		$('#selectGroup').select2();
		$(function () {
			$.fn.select2.amd.require(['optgroup-data', 'optgroup-results'], function (OptgroupData, OptgroupResults) {
				$('#selectGroup').select2({
					dataAdapter: OptgroupData,
					resultsAdapter: OptgroupResults,
					closeOnSelect: false
				});
			});
		});
	}

	/* Format price */
	if ($('.format-price').length) {
		$('.format-price').priceFormat({
			limit: 13,
			prefix: '',
			centsLimit: 0
		});
	}

	if ($('.check-link')) {
		$('body').on('click', '.check-link', function () {
			holdonOpen();
			$.ajax({
				url: 'check-link',
				type: 'POST',
				data: {
					csrf_token: CSRF_TOKEN
				},
				success: function (result) {
					holdonClose();
					window.location.reload();
				}
			});
		});
	}

	if ($('.reset-link').length) {
		$('body').on('click', '.reset-link', function () {
			var link = $(this).data('link');
			holdonOpen();
			$.ajax({
				url: 'reset-link',
				type: 'POST',
				dataType: 'html',
				data: {
					link: link,
					csrf_token: CSRF_TOKEN
				},
				success: function (result) {
					holdonClose();
					$('.result-link').html(result);
				}
			});
		});
	}

	/* Ajax category */
	if ($('.select-category')) {
		$('body').on('change', '.select-category', function () {
			var id = $(this).val();
			var child = $(this).data('child');
			var level = parseInt($(this).data('level'));
			var table = $(this).data('table');
			var type = $(this).data('type');
			var url = $(this).data('url');

			if ($('#' + child).length) {
				$.ajax({
					url: url,
					type: 'POST',
					data: {
						csrf_token: CSRF_TOKEN,
						level: level,
						id: id,
						table: table,
						type: type
					},
					success: function (result) {
						var op = "<option value='0'>" + 'chọn danh mục' + '</option>';

						if (level == 0) {
							$('#id_cat').html(op);
							$('#id_item').html(op);
							$('#id_sub').html(op);
						} else if (level == 1) {
							$('#id_item').html(op);
							$('#id_sub').html(op);
						} else if (level == 2) {
							$('#id_sub').html(op);
						}
						$('#' + child).html(result);
					}
				});
			}

			if (ACTIVE_PROPERTIES_CATEGORIES) {
				if (level == 0 && table == 'product_list') {
					$.ajax({
						url: 'propertiesList',
						type: 'POST',
						data: {
							id: id,
							csrf_token: CSRF_TOKEN
						},
						success: function (result) {
							$('.list-properties').html(result);
							$('.select2').select2({
								placeholder: 'chọn danh mục'
							});
						}
					});
				}
			}
			return false;
		});
	}

	/* Ajax place */
	if ($('.select-place').length) {
		$('body').on('change', '.select-place', function () {
			var id = $(this).val();
			var child = $(this).data('child');
			var level = parseInt($(this).data('level'));
			var table = $(this).data('table');

			if ($('#' + child).length) {
				$.ajax({
					url: 'place',
					type: 'POST',
					data: {
						csrf_token: CSRF_TOKEN,
						level: level,
						id: id,
						table: table
					},
					success: function (result) {
						var op = "<option value='0'>" + 'chọn danh mục' + '</option>';

						if (level == 0) {
							$('#id_district').html(op);
							$('#id_ward').html(op);
						} else if (level == 1) {
							$('#id_ward').html(op);
						}
						$('#' + child).html(result);
					}
				});
			}

			return false;
		});
	}
	/* Check required form */
	if ($('.submit-check').length) {
		$('.submit-check').click(function () {
			var formCheck = $(this).parents('form.validation-form');
			/* Holdon */
			holdonOpen();
			/* Check slug */
			slugCheck();

			/* Elements */
			var flag = true;
			var slugs = '';
			var slugOffset = $('.card-slug');
			var slugsInValid = $('.card-slug :required:invalid');
			var slugsError = $('.card-slug .text-danger').not('.d-none');
			var cardOffset = 0;
			var elementsInValid = $('.card :required:invalid');

			/* Check if has slug vs name */
			if (slugsInValid.length || slugsError.length) {
				flag = false;
				slugs = slugsError.length ? slugsError : slugsInValid;

				/* Check elements empty */
				slugs.each(function () {
					$this = $(this);
					var tabPane = $this.parents('.tab-pane');
					var tabPaneID = tabPane.attr('id');
					$('.nav-tabs a[href="#' + tabPaneID + '"]').tab('show');
					var triggerFirstTabEl = document.querySelector('a[aria-controls="' + tabPaneID + '"]');
					if (triggerFirstTabEl) {
						var tab = new bootstrap.Tab(triggerFirstTabEl);
						tab.show();
					}
					Toastify({
						text: 'Vui lòng nhập đầy đủ nội dung',
						duration: 1500,
						gravity: 'bottom',
						position: 'center',
						className: 'bg-danger',
						style: {
							background: 'var(--bs-danger)'
						}
					}).showToast();

					return false;
				});

				/* Scroll to error */
				setTimeout(function () {
					$('html,body').animate({ scrollTop: slugOffset.offset().top - 40 }, 'medium');
				}, 500);
			} else if (elementsInValid.length) {
				flag = false;

				/* Check elements empty */
				elementsInValid.each(function () {
					$this = $(this);
					cardOffset = $this.parents('.card-body');
					var cardCollapsed = $this.parents('.card.collapsed-card');

					if (cardCollapsed.length) {
						cardCollapsed.find('.card-body').show();
						cardCollapsed.find('.btn-tool i').toggleClass('fas fa-plus fas fa-minus');
						cardCollapsed.removeClass('collapsed-card');
					}

					var tabPane = $this.parents('.tab-pane');
					var tabPaneID = tabPane.attr('id');
					$('.nav-tabs a[href="#' + tabPaneID + '"]').tab('show');
					var triggerFirstTabEl = document.querySelector('a[aria-controls="' + tabPaneID + '"]');
					if (triggerFirstTabEl) {
						var tab = new bootstrap.Tab(triggerFirstTabEl);
						tab.show();
					}

					Toastify({
						text: 'Vui lòng nhập đầy đủ nội dung',
						duration: 1500,
						gravity: 'bottom',
						position: 'center',
						className: 'bg-danger',
						style: {
							background: 'var(--bs-danger)'
						}
					}).showToast();
					return false;
				});

				/* Scroll to error */
				if (cardOffset) {
					setTimeout(function () {
						$('html,body').animate({ scrollTop: cardOffset.offset().top - 100 }, 'medium');
					}, 500);
				}
			}

			/* Holdon close */
			holdonClose();

			/* Check form validated */
			if (!flag) {
				formCheck.addClass('was-validated');
			} else {
				formCheck.removeClass('was-validated');
			}

			return flag;
		});
	}

	/* Push oneSignal */
	if ($('#push-onesignal').length) {
		$('body').on('click', '#push-onesignal', function () {
			var url = $(this).data('url');
			confirmDialog('push-onesignal', 'Bạn muốn đẩy tin này', url);
		});
	}

	/* Check item */
	if ($('.select-checkbox').length) {
		var lastChecked = null;
		var $checkboxItem = $('.select-checkbox');

		$checkboxItem.click(function (e) {
			if (!lastChecked) {
				lastChecked = this;
				return;
			}

			if (e.shiftKey) {
				var start = $checkboxItem.index(this);
				var end = $checkboxItem.index(lastChecked);
				$checkboxItem.slice(Math.min(start, end), Math.max(start, end) + 1).prop('checked', true);
			}

			lastChecked = this;
		});
	}

	/* Check all */
	if ($('#selectall-checkbox').length) {
		$('body').on('click', '#selectall-checkbox', function () {
			var parentTable = $(this).parents('table');
			var input = parentTable.find('.custom-control.custom-checkbox.my-checkbox').find('input.form-check-input');

			if ($(this).is(':checked')) {
				input.each(function () {
					$(this).prop('checked', true);
				});
			} else {
				input.each(function () {
					$(this).prop('checked', false);
				});
			}
		});
	}

	/* Delete item */
	if ($('#delete-item').length) {
		$('body').on('click', '#delete-item', function () {
			var url = $(this).data('url');
			confirmDialog('delete-item', 'Bạn muốn xóa mục này', url);
		});
	}

	/* Delete all */
	if ($('#delete-all').length) {
		$('body').on('click', '#delete-all', function () {
			var url = $(this).data('url');
			confirmDialog('delete-all', 'Bạn có muốn xóa những mục này', url);
		});
	}

	/* Load name input file */
	if ($('.custom-file input[type=file]').length) {
		$('body').on('change', '.custom-file input[type=file]', function () {
			var fileName = $(this).val();
			fileName = fileName.substr(fileName.lastIndexOf('\\') + 1, fileName.length);
			$(this).siblings('label').html(fileName);
		});
	}

	/* Change status */
	if ($('.show-checkbox').length) {
		$('body').on('click', '.show-checkbox', function () {
			var id = $(this).attr('data-id');
			var table = $(this).attr('data-table');
			var attr = $(this).attr('data-attr');
			var url = $(this).attr('data-url');
			var $this = $(this);

			$.ajax({
				url: url,
				type: 'GET',
				data: {
					id: id,
					table: table,
					attr: attr
				},
				success: function () {
					if ($this.is(':checked')) $this.prop('checked', false);
					else $this.prop('checked', true);
				}
			});

			return false;
		});
	}

	/* Change numb */
	if ($('input.update-numb').length) {
		$('body').on('change', 'input.update-numb', function () {
			var id = $(this).attr('data-id');
			var table = $(this).attr('data-table');
			var value = $(this).val();

			$.ajax({
				url: 'numb',
				type: 'get',
				dataType: 'html',
				data: {
					id: id,
					table: table,
					value: value
				}
			});

			return false;
		});
	}

	/* Copy */
	if ($('#dropdownCopy').length) {
		$('body').on('click', '#dropdownCopy', function () {
			var id = $(this).attr('data-id');
			var table = $(this).attr('data-table');
			var com = $(this).attr('data-com');
			var type = $(this).attr('data-type');
			$.ajax({
				url: 'copy',
				type: 'get',
				dataType: 'html',
				data: {
					id: id,
					com: com,
					type: type,
					table: table
				},
				success: function () {
					holdonClose();
					window.location.reload();
				}
			});
		});
	}

	/* Send Newsletter */
	if ($('#emailComposeSidebarLabel').length) {
		$('body').on('click', '#emailComposeSidebarLabel', function () {
			var listid = '';
			$('input.email-list-item-input').each(function () {
				if (this.checked) listid = listid + ',' + this.value;
			});
			listid = listid.substr(1);
			if (listid == '') {
				notifyDialog('Bạn phải chọn ít nhất 1 mục để gửi mail');
				return false;
			} else {
				const myModal = new bootstrap.Modal(document.getElementById('emailComposeSidebar'), {});
				myModal.show();
				const selectedValues = listid.split(',');
				$('#emailContacts').val(selectedValues).trigger('change');
			}
		});
	}

	/* Comment */

	function showMoreReplies(target) {
		const replies = target.parentNode.querySelectorAll('.comment-replies .comment-replies-item');
		replies.forEach((reply) => {
			reply.style.display = 'block';
		});
		target.style.display = 'none';
	}

	document.body.addEventListener('click', function (event) {
		if (event.target.matches('.view-more-replies:not(.hide-comment)')) {
			showMoreReplies(event.target);
		}
	});

	document.body.addEventListener('click', function (event) {
		if (event.target.classList.contains('btn-reply-comment')) {
			document.querySelectorAll('.response-reply-admin').forEach((element) => {
				element.style.display = 'block';
			});
		}
	});
});

/* Permissions */
document.addEventListener('DOMContentLoaded', function () {
	// Lấy tất cả các phần tử có class "menu-header"
	const menuHeaders = document.querySelectorAll('.menu-header');
	menuHeaders.forEach(function (menuHeader) {
		// Tìm phần tử ul.menu-body bên trong menu-header
		const menuBody = menuHeader.querySelector('.menu-body');
		const menuSub = menuHeader.querySelector('.menu-sub');
		// const menuItem = menuHeader.querySelector('.menu-item');
		// Kiểm tra nếu ul.menu-body không có thẻ li con nào
		if (
			(menuBody && menuBody.querySelectorAll('li').length === 0) ||
			(menuSub && menuSub.querySelectorAll('li').length === 0)
		) {
			// Ẩn phần tử menu-header
			menuHeader.style.display = 'none';
		} else {
			menuHeader.style.display = 'block';
		}
		// Kiểm tra xem menuItem có class 'active'
		// if (menuItem.classList.contains('active')) {
		// 	// ặt tiêu điểm (focus) cho menuItem
		// 	menuItem.focus();
		// }
	});
});

$(document).ready(function () {
	if (isExist($('.scrolling-ul'))) {
		var $activeItem = $('.scrolling-ul .active');
		if ($activeItem.length) {
			$('.scrolling-ul').animate(
				{
					scrollTop:
						$activeItem.position().top +
						$('.scrolling-ul').scrollTop() -
						$('.scrolling-ul').height() / 2 +
						$activeItem.outerHeight() / 2
				},
				'slow'
			);
		}
	}
});

async function sendMessage() {
	const messageInput = document.getElementById('userMessage');
	const message = messageInput.value.trim();
	if (message === '') return;

	// Hiển thị tin nhắn ngưi dùng
	const chatBox = document.getElementById('chat-box');
	const userMessageDiv = document.createElement('div');
	userMessageDiv.className = 'message user';
	userMessageDiv.textContent = 'Bạn: ' + message;
	chatBox.appendChild(userMessageDiv);
	chatBox.scrollTop = chatBox.scrollHeight;

	// Xóa ô nhập
	messageInput.value = '';

	try {
		const response = await fetch('chat', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify({ message, csrf_token: CSRF_TOKEN })
		});

		const data = await response.json();

		if (response.ok) {
			const botReply = data.reply;
			const botMessageDiv = document.createElement('div');
			botMessageDiv.className = 'message bot';
			botMessageDiv.textContent = 'GPT: ' + botReply;
			chatBox.appendChild(botMessageDiv);
			chatBox.scrollTop = chatBox.scrollHeight;
		} else {
			const errorDiv = document.createElement('div');
			errorDiv.className = 'message bot';
			errorDiv.textContent = 'Error: ' + data.error;
			chatBox.appendChild(errorDiv);
			chatBox.scrollTop = chatBox.scrollHeight;
		}
	} catch (error) {
		const errorDiv = document.createElement('div');
		errorDiv.className = 'message bot';
		errorDiv.textContent = 'Error: Không thể kết nối đến server.';
		chatBox.appendChild(errorDiv);
		chatBox.scrollTop = chatBox.scrollHeight;
	}
}

// Gửi tin nhắn khi nhấn Enter
// document.getElementById('userMessage').addEventListener('keypress', function (e) {
// 	if (e.key === 'Enter') {
// 		sendMessage();
// 	}
// });
document.addEventListener('DOMContentLoaded', function () {
	const menuHeaders = document.querySelectorAll('.menu-header');
	menuHeaders.forEach(menuHeader => {
		// Lấy tất cả menu-body và menu-sub bên trong menu-header
		const menuBodies = menuHeader.querySelectorAll('.menu-body');
		const menuSubs = menuHeader.querySelectorAll('.menu-sub');

		// Kiểm tra xem có ít nhất một menu-body hoặc menu-sub chứa <li> không
		const hasMenuItems =
			[...menuBodies].some(menu => menu.querySelector('li')) ||
			[...menuSubs].some(menu => menu.querySelector('li'));

		menuHeader.style.display = hasMenuItems ? 'block' : 'none';
	});
});


