class Cart {
	constructor(options) {
		this.wrapper = document.querySelector('body');
		this.base_url = this.resolveBaseUrl(options);
		this.CountCart = document.querySelector('.count-cart');
		this.variantStockMap = {};
		this.variantCombos = [];
		this.variantComboSet = new Set();
		this.cartStockByRow = {};
		this.cartHasOutOfStock = false;
		this.cartStockPollTimer = null;
		this.cartStockSyncing = false;
		this.cartStockPollMs = 8000;
		this.cartPricing = {
			baseShip: 0,
			subtotal: 0,
			discount: 0,
			total: 0,
			voucher: null,
			voucherList: []
		};
		this.init();
	}

	resolveBaseUrl(options) {
		const addCartUrl = (typeof CART_URL === 'object' && CART_URL && typeof CART_URL.ADD_CART === 'string')
			? String(CART_URL.ADD_CART).trim()
			: '';
		if (addCartUrl) {
			const marker = '/cart/add-to-cart';
			const markerIndex = addCartUrl.indexOf(marker);
			if (markerIndex >= 0) {
				return addCartUrl.slice(0, markerIndex + 1);
			}
		}

		const candidate = (typeof options === 'string') ? options : (options?.url || '');
		return this.normalizeBasePath(candidate);
	}

	normalizeBasePath(basePath) {
		const raw = String(basePath || '').trim();
		if (!raw) return '';

		try {
			const parsed = new URL(raw, window.location.origin);
			let pathname = parsed.pathname || '/';
			if (!pathname.endsWith('/')) pathname += '/';
			return pathname;
		} catch (error) {
			const normalized = raw.replace(/\/+$/, '');
			return normalized ? (normalized + '/') : '';
		}
	}

	init() {
		window.addEventListener('load', () => {
			this.loadVariantStockData();
			this.refreshPropertyAvailability();
			this.syncInitialThumb();
			this.updateSelectedPropertyLabels();
			this.syncQuantityLimit();
			this.setupZoomSync();
			this.initCartStockState();
			this.initCartPricing();
			this.initCheckoutPrefill();
			this.startCartStockPolling();
		});

		this.wrapper.addEventListener('click', (e) => {
			if (e.target.closest('.addcart')) {
				this.addCart(e.target.closest('.addcart'));
			} else if (e.target.matches('.quantity-pro-detail span')) {
				this.numberCart(e.target);
			} else if (e.target.matches('.quantity-counter-procart span')) {
				this.numberForm(e.target);
			} else if (e.target.closest('.del-procart')) {
				this.deleteCart(e.target.closest('.del-procart'));
			} else if (e.target.closest('.payments-label')) {
				this.ShowPayments(e.target.closest('.payments-label'));
			} else if (e.target.closest('.js-voucher-apply')) {
				this.applyVoucherFromInput();
			} else if (e.target.closest('.js-voucher-card')) {
				this.selectVoucherCard(e.target.closest('.js-voucher-card'));
			} else if (e.target.closest('.properties')) {
				const changed = this.handlePropertyClick(e.target.closest('.properties'));
				if (changed) this.showPrice();
			} else if (e.target.closest('.thumbs-next')) {
				e.preventDefault();
				this.stepThumb(1);
			} else if (e.target.closest('.thumbs-prev')) {
				e.preventDefault();
				this.stepThumb(-1);
			} else if (e.target.closest('.thumb-pro-detail')) {
				this.handleThumbClick(e.target.closest('.thumb-pro-detail'));
			} else if (e.target.closest('.mz-button-next, .mz-button-prev')) {
				setTimeout(() => this.syncThumbByMain(), 40);
			}
		});

		this.wrapper.addEventListener("change", (e) => {
			if (e.target.closest(".select-city-cart")) {
				this.getWards(e.target.closest(".select-city-cart"));
			} else if (e.target.closest(".select-ward-cart")) {
				this.getShip(e.target.closest(".select-ward-cart"));
			}
		});

		this.wrapper.addEventListener('keydown', (e) => {
			if (e.target.closest('.js-voucher-input') && e.key === 'Enter') {
				e.preventDefault();
				this.applyVoucherFromInput();
			}
		});


		// this.wrapper.addEventListener('submit', (e) => {
		// 	if (e.target.matches('.form-cart')) {
		// 		this.submitCart(e);
		// 	}
		// });
	}

	showPrice() {
		const activeProperties = Array.from(this.wrapper.querySelectorAll('.grid-properties .properties.active'));
		if (!activeProperties.length) return;

		const id_product = activeProperties[0].getAttribute('data-product');
		const properties = activeProperties.map((el) => el.getAttribute('data-id'));
		const activeColor = this.wrapper.querySelector('.grid-properties .properties.active[data-is-color="1"]');
		const colorId = activeColor ? activeColor.getAttribute('data-id') : '';
		var form_data = new FormData();
		form_data.append('id_product', id_product);
		form_data.append('properties', JSON.stringify(properties));
		if (colorId) form_data.append('color_id', colorId);

		fetch(this.base_url + 'cart/show-price', {
			method: 'POST',
			body: form_data
		})
			.then((response) => response.json())
			.then((result) => {
				const newPrice = this.wrapper.querySelector('.price-new-pro-detail');
				const oldPrice = this.wrapper.querySelector('.price-old-pro-detail');
				if (newPrice && result?.priceNew != null) newPrice.innerHTML = result.priceNew;
				if (oldPrice && result?.priceOld != null) oldPrice.innerHTML = result.priceOld;
				this.updatePriceMeta();
				this.syncVariantVisual({ code: result?.code || '' });
			});

		this.showPhoto();
	}

	showPhoto() {
		const activeProperties = Array.from(this.wrapper.querySelectorAll('.grid-properties .properties.active'));
		if (!activeProperties.length) return;
		const id_product = activeProperties[0].getAttribute('data-product');
		const properties = activeProperties.map((el) => el.getAttribute('data-id'));
		const activeColor = this.wrapper.querySelector('.grid-properties .properties.active[data-is-color="1"]');
		const colorId = activeColor ? activeColor.getAttribute('data-id') : '';
		var form_data = new FormData();
		form_data.append('id_product', id_product);
		form_data.append('properties', JSON.stringify(properties));
		if (colorId) form_data.append('color_id', colorId);

		fetch(this.base_url + 'cart/show-photo', {
			method: 'POST',
			body: form_data
		})
			.then((response) => response.json())
			.then((result) => {
				let photo = result?.photo || '';
				if (!photo && activeColor) {
					photo = activeColor.getAttribute('data-photo') || '';
				}
				if (photo) {
					this.syncVariantVisual({
						photo: photo,
						photo_full: result?.photoFull || '',
						photo_thumb: result?.photoThumb || ''
					});
				}
			});
	}

	syncVariantVisual(result = {}) {
		const code = result?.code || '';
		const codeElement = this.wrapper.querySelector('.js-product-code');
		if (codeElement && code) {
			codeElement.textContent = code;
		}

		const photo = result?.photo || '';
		if (!photo) return;

		const thumbTarget = this.wrapper.querySelector('.thumb-pro-detail[data-product-photo="' + photo + '"]');
		if (thumbTarget) {
			this.syncThumbActive(thumbTarget);
		}

		this.updateMainPhoto(photo, result?.photo_full || '', result?.photo_thumb || '');
	}

	updateMainPhoto(photo, photoFull = '', photoThumb = '') {
		if (!photo) return;
		const mainAnchor = this.wrapper.querySelector('#Zoom-1');
		if (!mainAnchor) return;
		const mainImage = mainAnchor.querySelector('img.product-main-image') || mainAnchor.querySelector('img');
		const mainHref = mainAnchor.getAttribute('href') || '';
		const currentThumbSrc = mainImage ? (mainImage.getAttribute('src') || '') : '';
		let finalFull = photoFull || '';
		let finalThumb = photoThumb || '';

		if (!finalFull) {
			const currentFull = this.getPathSegment(mainHref);
			if (currentFull) finalFull = mainHref.replace(currentFull, photo);
		}

		if (finalThumb) {
			mainAnchor.querySelectorAll('img').forEach((img) => {
				img.setAttribute('src', finalThumb);
			});
		} else {
			const currentThumb = this.getPathSegment(currentThumbSrc);
			if (mainImage && currentThumb) {
				finalThumb = currentThumbSrc.replace(currentThumb, photo);
				mainImage.setAttribute('src', finalThumb);
			}
		}

		if (finalFull) mainAnchor.setAttribute('href', finalFull);

		// MagicZoom needs explicit update to sync main frame and zoom frame.
		if (window.MagicZoom && typeof window.MagicZoom.update === 'function') {
			const zoomId = mainAnchor.getAttribute('id') || mainAnchor;
			window.MagicZoom.update(zoomId, finalFull || mainAnchor.getAttribute('href'), finalThumb || (mainImage ? mainImage.getAttribute('src') : ''));
			return;
		}
		if (window.MagicZoom && typeof window.MagicZoom.refresh === 'function') {
			window.MagicZoom.refresh();
		}
	}

	handlePropertyClick(target) {
		if (!target || target.classList.contains('outstock') || target.classList.contains('disabled')) return false;
		const group = target.closest('.grid-properties');
		if (!group) return false;
		group.querySelectorAll('.properties').forEach((el) => el.classList.remove('active'));
		target.classList.add('active');
		this.refreshPropertyAvailability();
		this.updateSelectedPropertyLabels();
		this.syncQuantityLimit();

		const isColor = target.getAttribute('data-is-color') === '1';
		const photo = target.getAttribute('data-photo') || '';
		if (isColor && photo) {
			this.syncVariantVisual({ photo: photo });
		}
		if (isColor) this.showPhoto();
		return true;
	}

	syncThumbActive(target) {
		if (!target) return;
		this.wrapper.querySelectorAll('.thumb-pro-detail').forEach((el) => el.classList.remove('mz-thumb-selected'));
		target.classList.add('mz-thumb-selected');
	}

	handleThumbClick(target) {
		if (!target) return;
		this.syncThumbActive(target);
		const full = target.getAttribute('href') || '';
		const thumb = target.getAttribute('data-image') || '';
		const photo = target.getAttribute('data-product-photo') || this.getPathSegment(full);
		if (photo || full || thumb) {
			this.updateMainPhoto(photo, full, thumb);
		}
	}

	stepThumb(step = 1) {
		const thumbs = Array.from(this.wrapper.querySelectorAll('.product-detail-thumbs .thumb-pro-detail'));
		if (!thumbs.length) return;
		let index = thumbs.findIndex((el) => el.classList.contains('mz-thumb-selected'));
		if (index < 0) index = 0;
		let nextIndex = index + step;
		if (nextIndex < 0) nextIndex = 0;
		if (nextIndex > thumbs.length - 1) nextIndex = thumbs.length - 1;
		if (nextIndex === index) return;

		const target = thumbs[nextIndex];
		this.handleThumbClick(target);
		if (window.productThumbsSwiper && typeof window.productThumbsSwiper.slideTo === 'function') {
			window.productThumbsSwiper.slideTo(nextIndex);
		}
	}

	syncInitialThumb() {
		const selected = this.wrapper.querySelector('.thumb-pro-detail.mz-thumb-selected') || this.wrapper.querySelector('.thumb-pro-detail');
		if (selected) this.handleThumbClick(selected);
	}

	setupZoomSync() {
		const mainAnchor = this.wrapper.querySelector('#Zoom-1');
		if (!mainAnchor || typeof MutationObserver === 'undefined') return;
		this.syncThumbByMain();
		const observer = new MutationObserver(() => this.syncThumbByMain());
		observer.observe(mainAnchor, { attributes: true, attributeFilter: ['href'] });
	}

	syncThumbByMain() {
		const mainAnchor = this.wrapper.querySelector('#Zoom-1');
		if (!mainAnchor) return;
		const href = mainAnchor.getAttribute('href') || '';
		if (!href) return;
		let thumb = this.wrapper.querySelector('.thumb-pro-detail[href="' + href + '"]');
		if (!thumb) {
			const photo = this.getPathSegment(href);
			thumb = this.wrapper.querySelector('.thumb-pro-detail[data-product-photo="' + photo + '"]');
		}
		if (!thumb) return;
		this.syncThumbActive(thumb);
		const index = Array.from(this.wrapper.querySelectorAll('.product-detail-thumbs .thumb-pro-detail')).indexOf(thumb);
		if (index >= 0 && window.productThumbsSwiper && typeof window.productThumbsSwiper.slideTo === 'function') {
			window.productThumbsSwiper.slideTo(index);
		}
	}

	updateSelectedPropertyLabels() {
		this.wrapper.querySelectorAll('.js-selected-prop[data-list]').forEach((labelEl) => {
			const listId = labelEl.getAttribute('data-list');
			if (!listId) return;
			const active = this.wrapper.querySelector('.properties.active[data-list="' + listId + '"]');
			if (!active) return;
			const name = active.getAttribute('data-name') || active.textContent || '';
			labelEl.textContent = (name || '').trim();
		});
	}

	loadVariantStockData() {
		const root = this.wrapper.querySelector('.product-detail-v2');
		const raw = root ? (root.getAttribute('data-variant-stock') || '{}') : '{}';
		let parsed = {};
		try {
			parsed = JSON.parse(raw);
		} catch (e) {
			parsed = {};
		}

		this.variantStockMap = {};
		this.variantCombos = [];
		this.variantComboSet = new Set();

		Object.keys(parsed || {}).forEach((key) => {
			const row = parsed[key] || {};
			const ids = key.split(',').map((v) => parseInt(v, 10)).filter((v) => Number.isInteger(v) && v > 0).sort((a, b) => a - b);
			if (!ids.length) return;
			const variantKey = ids.join(',');
			const quantity = parseInt(row.quantity || 0, 10) || 0;
			const inStock = !!row.in_stock && quantity > 0;
			this.variantStockMap[variantKey] = { quantity: quantity, in_stock: inStock };
			if (inStock) {
				this.variantCombos.push(ids);
				this.variantComboSet.add(variantKey);
			}
		});
	}

	getActiveIdsByGroup() {
		const selected = {};
		this.wrapper.querySelectorAll('.grid-properties .properties.active').forEach((el) => {
			const listId = String(el.getAttribute('data-list') || '').trim();
			const id = parseInt(el.getAttribute('data-id') || 0, 10);
			if (!listId || !id) return;
			selected[listId] = id;
		});
		return selected;
	}

	getCandidateIdsForOption(option) {
		const selected = this.getActiveIdsByGroup();
		const listId = String(option.getAttribute('data-list') || '').trim();
		const id = parseInt(option.getAttribute('data-id') || 0, 10);
		if (!listId || !id) return [];
		selected[listId] = id;
		return Object.values(selected).map((v) => parseInt(v, 10)).filter((v) => Number.isInteger(v) && v > 0).sort((a, b) => a - b);
	}

	isCandidateAvailable(candidateIds = []) {
		if (!this.variantCombos.length) return true;
		if (!candidateIds.length) return true;
		return this.variantCombos.some((combo) => candidateIds.every((id) => combo.includes(id)));
	}

	refreshPropertyAvailability() {
		if (!this.variantCombos.length) return;
		this.wrapper.querySelectorAll('.grid-properties .properties').forEach((option) => {
			const available = this.isCandidateAvailable(this.getCandidateIdsForOption(option));
			option.classList.toggle('outstock', !available);
			option.classList.toggle('disabled', !available);
			option.setAttribute('data-in-stock', available ? '1' : '0');
			option.setAttribute('aria-disabled', available ? 'false' : 'true');
		});

		this.wrapper.querySelectorAll('.grid-properties').forEach((group) => {
			const active = group.querySelector('.properties.active');
			if (active && (active.classList.contains('outstock') || active.classList.contains('disabled'))) {
				active.classList.remove('active');
			}
			if (!group.querySelector('.properties.active')) {
				const fallback = group.querySelector('.properties:not(.outstock):not(.disabled)');
				if (fallback) fallback.classList.add('active');
			}
		});

		this.updateSelectedPropertyLabels();
	}

	getSelectedVariantInfo() {
		const ids = Array.from(this.wrapper.querySelectorAll('.grid-properties .properties.active'))
			.map((el) => parseInt(el.getAttribute('data-id') || 0, 10))
			.filter((id) => Number.isInteger(id) && id > 0)
			.sort((a, b) => a - b);
		const key = ids.join(',');
		const stock = this.variantStockMap[key] || { quantity: 0, in_stock: false };
		return {
			key: key,
			quantity: parseInt(stock.quantity || 0, 10) || 0,
			in_stock: !!stock.in_stock,
			valid: this.variantComboSet.has(key)
		};
	}

	syncQuantityLimit() {
		const qtyInput = this.wrapper.querySelector('.qty-pro');
		if (!qtyInput) return;
		const variant = this.getSelectedVariantInfo();
		if (!variant.valid || !variant.in_stock) {
			qtyInput.value = 1;
			qtyInput.removeAttribute('max');
			return;
		}
		qtyInput.setAttribute('max', String(variant.quantity));
		const current = parseInt(qtyInput.value || 1, 10) || 1;
		qtyInput.value = Math.min(Math.max(1, current), variant.quantity);
	}

	getPathSegment(path) {
		if (!path) return '';
		const purePath = path.split('?')[0];
		const parts = purePath.split('/');
		return parts.length ? parts[parts.length - 1] : '';
	}

	updatePriceMeta() {
		const newPriceEl = this.wrapper.querySelector('.price-new-pro-detail');
		const oldPriceEl = this.wrapper.querySelector('.price-old-pro-detail');
		const percentEl = this.wrapper.querySelector('.js-price-percent');
		const savingEl = this.wrapper.querySelector('.js-price-saving');
		if (!newPriceEl || !percentEl || !savingEl) return;

		const newVal = this.toNumber(newPriceEl.textContent || '');
		const oldVal = oldPriceEl ? this.toNumber(oldPriceEl.textContent || '') : 0;
		const hasDiscount = oldVal > newVal && newVal > 0;

		if (!hasDiscount) {
			percentEl.classList.add('hidden');
			savingEl.classList.add('hidden');
			return;
		}

		const save = oldVal - newVal;
		const percent = Math.round((save * 100) / oldVal);
		percentEl.textContent = '-' + percent + '%';
		percentEl.classList.remove('hidden');
		savingEl.querySelector('span').textContent = this.formatMoneyVn(save);
		savingEl.classList.remove('hidden');
	}

	toNumber(text = '') {
		const normalized = (text || '').replace(/[^\d]/g, '');
		return normalized ? parseInt(normalized, 10) : 0;
	}

	formatMoneyVn(value = 0) {
		if (!value) return '0đ';
		return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',') + 'đ';
	}

	getCartPricingRoot() {
		return this.wrapper.querySelector('.js-cart-pricing');
	}

	getVoucherRoot() {
		return this.wrapper.querySelector('.js-voucher-cart');
	}

	getVoucherListFromDom() {
		const voucherRoot = this.getVoucherRoot();
		if (!voucherRoot) return [];

		const voucherCards = Array.from(this.wrapper.querySelectorAll('.js-voucher-card[data-code]'));
		if (voucherCards.length) {
			return voucherCards.map((card) => {
				return {
					id: parseInt(card.getAttribute('data-id') || 0, 10) || 0,
					code: String(card.getAttribute('data-code') || '').trim().toUpperCase(),
					discount_type: String(card.getAttribute('data-discount-type') || '').trim().toUpperCase(),
					discount_value: parseFloat(card.getAttribute('data-discount-value') || 0) || 0,
					max_discount: parseFloat(card.getAttribute('data-max-discount') || 0) || 0,
					min_order_value: parseFloat(card.getAttribute('data-min-order-value') || 0) || 0
				};
			}).filter((voucher) => voucher.code);
		}

		const raw = voucherRoot.getAttribute('data-vouchers') || '[]';
		try {
			const parsed = JSON.parse(raw);
			if (!Array.isArray(parsed)) return [];
			return parsed.map((voucher) => {
				return {
					id: parseInt(voucher.id || 0, 10) || 0,
					code: String(voucher.code || '').trim().toUpperCase(),
					discount_type: String(voucher.discount_type || '').toUpperCase(),
					discount_value: parseFloat(voucher.discount_value || 0) || 0,
					max_discount: parseFloat(voucher.max_discount || 0) || 0,
					min_order_value: parseFloat(voucher.min_order_value || 0) || 0
				};
			}).filter((voucher) => voucher.code);
		} catch (e) {
			return [];
		}
	}

	setVoucherCardActive(code = '') {
		const normalizedCode = String(code || '').trim().toUpperCase();
		this.wrapper.querySelectorAll('.js-voucher-card').forEach((card) => {
			const cardCode = String(card.getAttribute('data-code') || '').trim().toUpperCase();
			card.classList.toggle('active', !!normalizedCode && cardCode === normalizedCode);
		});
	}

	findVoucherByCode(code = '') {
		const normalizedCode = String(code || '').trim().toUpperCase();
		if (!normalizedCode) return null;
		if (!Array.isArray(this.cartPricing.voucherList) || !this.cartPricing.voucherList.length) {
			this.cartPricing.voucherList = this.getVoucherListFromDom();
		}
		return this.cartPricing.voucherList.find((voucher) => voucher.code === normalizedCode) || null;
	}

	validateVoucher(voucher = null, subtotal = 0, shouldNotify = false) {
		if (!voucher) return false;
		const minOrderValue = parseInt(voucher.min_order_value || 0, 10) || 0;
		if (minOrderValue > 0 && subtotal < minOrderValue) {
			if (shouldNotify) {
				showNotify('Đơn hàng chưa đạt giá trị tối thiểu để dùng mã này.', 'Thông báo', 'warning');
			}
			return false;
		}
		return true;
	}

	calculateVoucherDiscount(voucher = null, subtotal = 0, shipPrice = 0) {
		let discount = 0;
		let finalShipPrice = Math.max(0, parseInt(shipPrice || 0, 10) || 0);
		if (!voucher) return { discount: 0, shipPrice: finalShipPrice };

		const discountType = String(voucher.discount_type || '').toUpperCase();
		const discountValue = parseFloat(voucher.discount_value || 0) || 0;
		const maxDiscount = parseFloat(voucher.max_discount || 0) || 0;

		if (discountType === 'PERCENT') {
			discount = Math.round((Math.max(0, subtotal) * discountValue) / 100);
			if (maxDiscount > 0) {
				discount = Math.min(discount, Math.round(maxDiscount));
			}
			discount = Math.min(discount, Math.max(0, subtotal));
		} else if (discountType === 'FREE_SHIP') {
			const maxShipDiscount = Math.round(discountValue);
			const shipDiscount = maxShipDiscount > 0 ? Math.min(finalShipPrice, maxShipDiscount) : finalShipPrice;
			finalShipPrice = Math.max(0, finalShipPrice - shipDiscount);
			discount = 0;
		} else {
			discount = Math.round(discountValue);
			discount = Math.min(discount, Math.max(0, subtotal));
		}

		return { discount: Math.max(0, discount), shipPrice: finalShipPrice };
	}

	clearVoucher(shouldNotify = false) {
		this.cartPricing.voucher = null;
		this.setVoucherCardActive('');
		const voucherCodeInput = this.wrapper.querySelector('.js-voucher-code-input');
		if (voucherCodeInput) voucherCodeInput.value = '';
		const voucherDiscountInput = this.wrapper.querySelector('.js-voucher-discount-input');
		if (voucherDiscountInput) voucherDiscountInput.value = '0';
		const input = this.wrapper.querySelector('.js-voucher-input');
		if (input) input.value = '';
		this.recalculateCartPricing();
		if (shouldNotify) showNotify('Đã bỏ voucher.', 'Thông báo', 'success');
	}

	applyVoucherFromInput(shouldNotify = true) {
		const voucherRoot = this.getVoucherRoot();
		if (!voucherRoot) return;
		const input = voucherRoot.querySelector('.js-voucher-input');
		if (!input) return;

		const code = String(input.value || '').trim().toUpperCase();
		if (!code) {
			this.clearVoucher(shouldNotify);
			return;
		}

		const voucher = this.findVoucherByCode(code);
		if (!voucher) {
			showNotify('Mã voucher không hợp lệ.', 'Thông báo', 'warning');
			return;
		}

		if (!this.validateVoucher(voucher, this.cartPricing.subtotal, true)) {
			return;
		}

		this.cartPricing.voucher = voucher;
		input.value = voucher.code;
		this.setVoucherCardActive(voucher.code);
		this.recalculateCartPricing();
		if (shouldNotify) showNotify('Áp dụng voucher thành công.', 'Thông báo', 'success');
	}

	selectVoucherCard(card) {
		if (!card) return;
		const code = String(card.getAttribute('data-code') || '').trim().toUpperCase();
		if (!code) return;
		const input = this.wrapper.querySelector('.js-voucher-input');
		if (input) input.value = code;
		this.applyVoucherFromInput(false);
	}

	initCartPricing() {
		const pricingRoot = this.getCartPricingRoot();
		if (!pricingRoot) return;
		const shipInput = this.wrapper.querySelector('.js-ship-price-input');
		const shipFromInput = shipInput ? (parseInt(shipInput.value || 0, 10) || 0) : 0;
		const shipFromData = parseInt(pricingRoot.getAttribute('data-base-ship') || 0, 10) || 0;
		this.cartPricing.baseShip = Math.max(0, shipFromInput || shipFromData || 0);
		this.cartPricing.voucherList = this.getVoucherListFromDom();
		this.recalculateCartPricing();
	}

	recalculateCartPricing() {
		const pricingRoot = this.getCartPricingRoot();
		if (!pricingRoot) return;

		const subtotalElement = pricingRoot.querySelector('.load-price-subtotal') || pricingRoot.querySelector('.load-price-total');
		const subtotal = this.toNumber(subtotalElement ? subtotalElement.textContent : '0');
		let shipPrice = Math.max(0, parseInt(this.cartPricing.baseShip || 0, 10) || 0);
		let discount = 0;

		if (this.cartPricing.voucher) {
			if (!this.validateVoucher(this.cartPricing.voucher, subtotal, false)) {
				this.cartPricing.voucher = null;
				this.setVoucherCardActive('');
				const input = this.wrapper.querySelector('.js-voucher-input');
				if (input) input.value = '';
			} else {
				const result = this.calculateVoucherDiscount(this.cartPricing.voucher, subtotal, shipPrice);
				discount = result.discount;
				shipPrice = result.shipPrice;
			}
		}

		const total = Math.max(0, subtotal + shipPrice - discount);
		this.cartPricing.subtotal = subtotal;
		this.cartPricing.discount = discount;
		this.cartPricing.total = total;

		const discountElement = pricingRoot.querySelector('.load-price-discount');
		if (discountElement) discountElement.textContent = discount > 0 ? '-' + this.formatMoneyVn(discount) : this.formatMoneyVn(0);
		const shipElement = pricingRoot.querySelector('.load-price-ship');
		if (shipElement) shipElement.textContent = shipPrice > 0 ? this.formatMoneyVn(shipPrice) : 'Miễn phí';
		const totalElement = pricingRoot.querySelector('.load-price-total');
		if (totalElement) totalElement.textContent = this.formatMoneyVn(total);

		const voucherCodeInput = this.wrapper.querySelector('.js-voucher-code-input');
		if (voucherCodeInput) voucherCodeInput.value = this.cartPricing.voucher?.code || '';
		const voucherDiscountInput = this.wrapper.querySelector('.js-voucher-discount-input');
		if (voucherDiscountInput) voucherDiscountInput.value = String(discount);
		const shipInput = this.wrapper.querySelector('.js-ship-price-input');
		if (shipInput) shipInput.value = String(shipPrice);
		const totalInput = this.wrapper.querySelector('.js-total-price-input');
		if (totalInput) totalInput.value = String(total);
	}

	syncCartSubtotal(text = '') {
		const pricingRoot = this.getCartPricingRoot();
		if (!pricingRoot) return false;
		const subtotalElement = pricingRoot.querySelector('.load-price-subtotal');
		if (!subtotalElement) return false;
		if (text) subtotalElement.innerHTML = text;
		this.recalculateCartPricing();
		return true;
	}

	// submitCart(e) {
	// 	e.preventDefault();
	// 	const form = e.target;
	// 	const formData = new FormData(form);
	// 	holdonOpen();
	// 	fetch(this.base_url + 'cart/send-to-cart', {
	// 		method: 'POST',
	// 		body: formData
	// 	})
	// 		.then((response) => response.json())
	// 		.then((result) => {});
	// }

	// getDistrict(target) {
	// 	const selectCity = document.querySelector('.select-city-cart');
	// 	const selectDistrict = document.querySelector('.select-district-cart');
	// 	const id = selectCity.value;
	// 	holdonOpen();
	// 	var form_data = new FormData();
	// 	form_data.append('id', id);
	// 	fetch(this.base_url + 'cart/get-district', {
	// 		method: 'POST',
	// 		body: form_data
	// 	})
	// 		.then((response) => response.json())
	// 		.then((result) => {
	// 			if (result.districts) {
	// 				this.populateSelect(result.districts, selectDistrict);
	// 			}
	// 			holdonClose();
	// 		});
	// }

  getWards(target, selectedWard = '') {
    const selectCity = document.querySelector(".select-city-cart");
    const selectWard = document.querySelector(".select-ward-cart");
    const id = selectCity ? selectCity.value : '';
    if (!id) {
      if (selectWard) {
        selectWard.innerHTML = '<option value="">Phường/Xã</option>';
      }
      this.getShip(selectWard);
      return;
    }

    holdonOpen();
    var form_data = new FormData();
    form_data.append("id", id);
    form_data.append("csrf_token", CSRF_TOKEN);
    fetch(this.base_url + "cart/get-ward", {
      method: "POST",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
      body: form_data,
    })
      .then((response) => response.json())
      .then((result) => {
        if (result.wards) {
          const selectedWardValue = String(selectedWard || (selectWard ? (selectWard.getAttribute('data-selected') || '') : '')).trim();
          const selectedWardName = String(selectWard ? (selectWard.getAttribute('data-selected-name') || '') : '').trim();
          this.populateSelect(result.wards, selectWard, {
            placeholder: 'Phường/Xã',
            selectedValue: selectedWardValue,
            selectedText: selectedWardName || selectedWardValue
          });
          if (selectWard) {
            selectWard.removeAttribute('data-selected');
            selectWard.removeAttribute('data-selected-name');
          }
        }
        this.getShip(selectWard);
        holdonClose();
      })
      .catch((error) => {
        console.error("API Error:", error);
        holdonClose();
      });
  }
	getShip(target) {
		const selectCity = document.querySelector('.select-city-cart');
		const selectWard = document.querySelector('.select-ward-cart');
		if (!selectCity) return;

		const cityId = parseInt(selectCity.value || 0, 10) || 0;
		const wardId = selectWard ? (parseInt(selectWard.value || 0, 10) || 0) : 0;
		var form_data = new FormData();
		form_data.append('city_id', cityId);
		form_data.append('ward_id', wardId);
		form_data.append('csrf_token', CSRF_TOKEN);

		fetch(this.base_url + 'cart/get-ship', {
			method: 'POST',
			headers: {
				'X-Requested-With': 'XMLHttpRequest'
			},
			body: form_data
		})
			.then((response) => response.json())
			.then((result) => {
				const shipPrice = parseInt(result?.shipPrice || 0, 10) || 0;
				this.cartPricing.baseShip = Math.max(0, shipPrice);
				const pricingRoot = this.getCartPricingRoot();
				if (pricingRoot) pricingRoot.setAttribute('data-base-ship', String(this.cartPricing.baseShip));
				this.recalculateCartPricing();
			})
			.catch((error) => {
				console.error('Shipping API Error:', error);
			});
	}
	initCheckoutPrefill() {
		const savedAddressSelect = document.querySelector('.js-saved-address');
		if (savedAddressSelect) {
			savedAddressSelect.addEventListener('change', () => {
				this.applySavedAddress(savedAddressSelect);
			});

			if (savedAddressSelect.value) {
				this.applySavedAddress(savedAddressSelect);
				return;
			}
		}

		const selectCity = document.querySelector('.select-city-cart');
		const selectWard = document.querySelector('.select-ward-cart');
		if (!selectCity || !selectCity.value) return;

		const selectedWardValue = selectWard ? String(selectWard.getAttribute('data-selected') || '').trim() : '';
		const selectedWardName = selectWard ? String(selectWard.getAttribute('data-selected-name') || '').trim() : '';
		this.getWards(selectCity, selectedWardValue || selectedWardName);
	}
	getCartForm() {
		return this.wrapper.querySelector('.form-cart');
	}
	initCartStockState() {
		const form = this.getCartForm();
		if (!form) return;

		let stockByRow = {};
		try {
			stockByRow = JSON.parse(form.getAttribute('data-cart-stock') || '{}') || {};
		} catch (e) {
			stockByRow = {};
		}

		const hasOutFromDom = String(form.getAttribute('data-cart-has-out-of-stock') || '0') === '1';
		this.applyCartStockStates(stockByRow, { notify: false });
		if (hasOutFromDom && !this.cartHasOutOfStock) {
			this.updateCheckoutAvailability(true);
		}

		if (!form.dataset.stockSubmitBound) {
			form.addEventListener('submit', (e) => {
				if (!this.cartHasOutOfStock) return;
				e.preventDefault();
				showNotify('Giỏ hàng có sản phẩm hết hàng. Vui lòng cập nhật trước khi thanh toán.', 'Thông báo', 'warning');
			});
			form.dataset.stockSubmitBound = '1';
		}
	}
	startCartStockPolling() {
		const form = this.getCartForm();
		if (!form) return;
		if (!form.querySelector('.quantity-procat[data-rowId]')) return;

		this.syncCartStockRealtime(false);
		if (this.cartStockPollTimer) {
			clearInterval(this.cartStockPollTimer);
		}
		this.cartStockPollTimer = setInterval(() => {
			this.syncCartStockRealtime(true);
		}, this.cartStockPollMs);

		if (!form.dataset.stockVisibilityBound) {
			document.addEventListener('visibilitychange', () => {
				if (document.visibilityState === 'visible') {
					this.syncCartStockRealtime(true);
				}
			});
			form.dataset.stockVisibilityBound = '1';
		}
	}
	syncCartStockRealtime(notify = false) {
		if (this.cartStockSyncing) return;
		const form = this.getCartForm();
		if (!form) return;

		this.cartStockSyncing = true;
		const form_data = new FormData();
		if (typeof CSRF_TOKEN !== 'undefined') {
			form_data.append('csrf_token', CSRF_TOKEN);
		}

		fetch(this.base_url + 'cart/sync-cart-stock', {
			method: 'POST',
			body: form_data
		})
			.then((response) => response.json())
			.then((result) => {
				if (!result || result.success === false) return;
				this.applyCartStockStates(result.cartStockByRow || {}, { notify: notify });
			})
			.catch(() => { })
			.finally(() => {
				this.cartStockSyncing = false;
			});
	}
	resolveCartStockMessage(stockState = {}) {
		const state = stockState || {};
		const message = String(state.message || '').trim();
		if (message) return message;
		const availableQty = parseInt(state.available_qty, 10);
		if (Number.isInteger(availableQty) && availableQty >= 0) {
			if (availableQty === 0) return 'Sản phẩm đã hết hàng.';
			return 'Số lượng vượt quá tồn kho. Còn lại: ' + availableQty;
		}
		return 'Sản phẩm đã hết hàng.';
	}
	updateRowQuantityControls(rowId, stockState = {}, inputElement = null) {
		const input = inputElement || this.wrapper.querySelector('.quantity-procat[data-rowId="' + rowId + '"]');
		if (!input) return;
		const counter = input.closest('.quantity-counter-procart');
		if (!counter) return;

		const plus = counter.querySelector('.counter-procart-plus');
		const minus = counter.querySelector('.counter-procart-minus');
		const currentQty = Math.max(1, parseInt(input.value || 1, 10) || 1);
		const availableQty = parseInt(stockState?.available_qty, 10);
		const hasAvailable = Number.isInteger(availableQty) && availableQty >= 0;
		const outOfStock = !!(stockState && stockState.in_stock === false);
		const canIncrease = !outOfStock && (!hasAvailable || currentQty < availableQty);
		const canDecrease = currentQty > 1;

		if (plus) plus.classList.toggle('is-disabled', !canIncrease);
		if (minus) minus.classList.toggle('is-disabled', !canDecrease);
		input.setAttribute('data-stock-blocked', outOfStock ? '1' : '0');
		if (hasAvailable) {
			input.setAttribute('data-available-qty', String(availableQty));
		} else {
			input.removeAttribute('data-available-qty');
		}
	}
	updateRowStockNotice(rowId, stockState = {}) {
		const rowSelector = '.procart-' + rowId;
		const rowElement = this.wrapper.querySelector(rowSelector);
		if (!rowElement) return;

		const infoBlock = rowElement.querySelector('.info-procart') || rowElement;
		let notice = rowElement.querySelector('.js-cart-stock-state[data-row-id="' + rowId + '"]');
		if (!notice) {
			notice = document.createElement('p');
			notice.className = 'cart-stock-state js-cart-stock-state';
			notice.setAttribute('data-row-id', rowId);
			infoBlock.appendChild(notice);
		}

		const outOfStock = !!(stockState && stockState.in_stock === false);
		notice.textContent = this.resolveCartStockMessage(stockState);
		notice.classList.toggle('is-error', outOfStock);
		notice.hidden = !outOfStock;
	}
	updateCheckoutAvailability(hasOutOfStock = false) {
		this.cartHasOutOfStock = !!hasOutOfStock;
		const checkoutBtn = this.wrapper.querySelector('.js-btn-checkout');
		if (checkoutBtn) {
			checkoutBtn.disabled = this.cartHasOutOfStock;
			checkoutBtn.classList.toggle('is-disabled', this.cartHasOutOfStock);
		}

		const alertBox = this.wrapper.querySelector('.js-cart-checkout-alert');
		if (alertBox) {
			alertBox.classList.toggle('hidden', !this.cartHasOutOfStock);
		}
	}
	applyCartStockStates(stockByRow = {}, options = {}) {
		const normalizedStock = (stockByRow && typeof stockByRow === 'object') ? stockByRow : {};
		const previousOutRows = new Set(
			Object.keys(this.cartStockByRow || {}).filter((rowId) => {
				return this.cartStockByRow[rowId] && this.cartStockByRow[rowId].in_stock === false;
			})
		);

		this.cartStockByRow = normalizedStock;
		let hasOutOfStock = false;
		let firstNewErrorMessage = '';

		this.wrapper.querySelectorAll('.quantity-procat[data-rowId]').forEach((input) => {
			const rowId = String(input.getAttribute('data-rowId') || '').trim();
			if (!rowId) return;

			const rowState = normalizedStock[rowId] || { in_stock: true, available_qty: null, message: '' };
			const rowOut = rowState && rowState.in_stock === false;
			if (rowOut) {
				hasOutOfStock = true;
				if (!previousOutRows.has(rowId) && !firstNewErrorMessage) {
					firstNewErrorMessage = this.resolveCartStockMessage(rowState);
				}
			}

			this.updateRowQuantityControls(rowId, rowState, input);
			this.updateRowStockNotice(rowId, rowState);
		});

		this.updateCheckoutAvailability(hasOutOfStock);
		const shouldNotify = !!options.notify;
		if (shouldNotify && firstNewErrorMessage) {
			showNotify(firstNewErrorMessage, 'Thông báo', 'warning');
		}
	}

	applySavedAddress(selectElement) {
		if (!selectElement) return;
		const option = selectElement.options[selectElement.selectedIndex];
		if (!option) return;

		const fullnameInput = document.getElementById('fullname');
		const phoneInput = document.getElementById('phone');
		const addressInput = document.getElementById('address');
		const citySelect = document.querySelector('.select-city-cart');
		const wardSelect = document.querySelector('.select-ward-cart');

		if (fullnameInput) fullnameInput.value = option.getAttribute('data-fullname') || '';
		if (phoneInput) phoneInput.value = option.getAttribute('data-phone') || '';
		if (addressInput) addressInput.value = option.getAttribute('data-address-line') || '';

		if (!citySelect) return;
		let cityId = parseInt(option.getAttribute('data-city-id') || 0, 10) || 0;
		const cityName = String(option.getAttribute('data-city-name') || '').trim();
		const wardId = String(option.getAttribute('data-ward-id') || '').trim();
		const wardName = String(option.getAttribute('data-ward-name') || '').trim();

		if (cityId <= 0 && cityName) {
			cityId = this.findCityIdByName(cityName);
		}

		if (cityId > 0) {
			citySelect.value = String(cityId);
			if (wardSelect) {
				wardSelect.setAttribute('data-selected', wardId);
				wardSelect.setAttribute('data-selected-name', wardName);
			}
			this.getWards(citySelect, wardId || wardName);
			return;
		}

		citySelect.value = '';
		if (wardSelect) {
			wardSelect.innerHTML = '<option value="">Phường/Xã</option>';
			wardSelect.removeAttribute('data-selected');
			wardSelect.removeAttribute('data-selected-name');
		}
		this.getShip(wardSelect);
	}

	findCityIdByName(cityName = '') {
		const selectCity = document.querySelector('.select-city-cart');
		if (!selectCity || !cityName) return 0;
		const keyword = this.normalizeTextValue(cityName);
		if (!keyword) return 0;

		for (let i = 0; i < selectCity.options.length; i++) {
			const option = selectCity.options[i];
			const optionName = option.getAttribute('data-city-name') || option.textContent || '';
			if (this.normalizeTextValue(optionName) === keyword) {
				return parseInt(option.value || 0, 10) || 0;
			}
		}

		return 0;
	}

	normalizeTextValue(text = '') {
		let value = String(text || '').trim().toLowerCase();
		if (!value) return '';
		if (typeof value.normalize === 'function') {
			value = value.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
		}
		value = value.replace(/đ/g, 'd');
		return value;
	}
	addCart(target) {
		const id = parseInt(target.getAttribute('data-id'), 10); // Chuyển đổi id sang số nguyên
		const properties = [];
		this.wrapper.querySelectorAll('.properties.active').forEach((v, k) => {
			properties.push(v.getAttribute('data-id'));
		});

		const action = target.getAttribute('data-action');
		const quantity = parseInt(this.wrapper.querySelector('.qty-pro').value, 10);
		if (this.variantCombos.length) {
			const variant = this.getSelectedVariantInfo();
			if (!variant.valid || !variant.in_stock) {
				showNotify('Phiên bản đã chọn đang hết hàng.', 'Thông báo', 'warning');
				return;
			}
			if (quantity > variant.quantity) {
				showNotify('Số lượng vượt quá tồn kho. Còn lại: ' + variant.quantity, 'Thông báo', 'warning');
				return;
			}
		}
		holdonOpen();
		var form_data = new FormData();
		form_data.append('id', id);
		form_data.append('quantity', quantity);
		form_data.append('properties', JSON.stringify(properties));
		form_data.append('csrf_token', CSRF_TOKEN);

		fetch(this.base_url + 'cart/add-to-cart', {
			method: 'POST',
			body: form_data
		})
			.then((response) => response.json())
			.then((result) => {
				if (result && result.error) {
					showNotify(result.message || 'Không thể thêm vào giỏ.', 'Thông báo', 'warning');
					holdonClose();
					return;
				}
				if (action == 'addnow') {
					this.CountCart.innerHTML = result.max;
					showNotify('Thêm giỏ hàng thành công.', 'Thông báo', 'success');
				} else {
					window.location.href = CART_URL['PAGE_CART'];
				}
				holdonClose();
			});
	}

	deleteCart(target) {
		const parentElement = document.querySelector('.form-cart');
		const rowId = target.getAttribute('data-rowId');
		var form_data = new FormData();
		form_data.append('rowId', rowId);
		holdonOpen();
		fetch(this.base_url + 'cart/delete-to-cart', {
			method: 'POST',
			body: form_data
		})
			.then((response) => response.json())
			.then((result) => {
				this.CountCart.innerHTML = result.max;
				const synced = this.syncCartSubtotal(result.tempText || result.totalText || '');
				if (!synced) this.wrapper.querySelector('.load-price-total').innerHTML = result.totalText;
				parentElement.querySelector('.procart-' + rowId)?.remove();
				if (this.cartStockByRow && Object.prototype.hasOwnProperty.call(this.cartStockByRow, rowId)) {
					delete this.cartStockByRow[rowId];
				}
				this.applyCartStockStates(this.cartStockByRow || {}, { notify: false });
				showNotify('Xóa sản phẩm thành công.', 'Thông báo', 'success');
				holdonClose();
			});
	}

	ShowPayments(target) {
		//const element = target.parentNode;
		const element = target.closest('.payments-cart');
		document.querySelectorAll('.payments-info').forEach((el) => el.classList.remove('active'));
		element.querySelector('.payments-info').classList.add('active');
	}

	numberCart(target) {
		const text = target.innerHTML;
		const inputElement = document.querySelector('.qty-pro');
		const num = inputElement.value;
		const max = parseInt(inputElement.getAttribute('max') || 0, 10) || 0;
		if (text == '-') {
			if (num > 1) {
				inputElement.value = parseInt(num, 10) - 1;
			} else {
				inputElement.value = 1;
			}
		} else {
			const next = parseInt(num, 10) + 1;
			inputElement.value = max > 0 ? Math.min(next, max) : next;
		}
	}
	numberForm(target) {
		const inputElement = target.parentNode.querySelector('.quantity-counter-procart .quantity-procat');
		if (!inputElement) return;

		const text = target.innerHTML;
		const rowId = inputElement.getAttribute('data-rowId');
		const currentQty = Math.max(1, parseInt(inputElement.value || 1, 10) || 1);
		const availableQty = parseInt(inputElement.getAttribute('data-available-qty') || 0, 10) || 0;
		const isPlus = text !== '-';
		const isBlocked = String(inputElement.getAttribute('data-stock-blocked') || '0') === '1';

		if (isPlus && (isBlocked || target.classList.contains('is-disabled'))) {
			const blockedState = this.cartStockByRow?.[rowId] || { in_stock: false, available_qty: availableQty };
			showNotify(this.resolveCartStockMessage(blockedState), 'Thông báo', 'warning');
			return;
		}

		let nextQty = currentQty;
		if (text == '-') {
			nextQty = currentQty > 1 ? currentQty - 1 : 1;
		} else {
			nextQty = currentQty + 1;
			if (availableQty > 0) {
				nextQty = Math.min(nextQty, availableQty);
			}
		}
		if (nextQty === currentQty) return;

		inputElement.value = String(nextQty);
		holdonOpen();
		var form_data = new FormData();
		form_data.append('quantity', String(nextQty));
		form_data.append('rowId', rowId);

		fetch(this.base_url + 'cart/update-to-number', {
			method: 'POST',
			body: form_data
		})
			.then((response) => response.json())
			.then((result) => {
				if (result && !result.error && result.max) {
					this.CountCart.innerHTML = result.max;
					this.wrapper.querySelectorAll('.load-price-new-' + rowId)?.forEach((v) => {
						v.innerHTML = result.salePrice;
					});
					this.wrapper.querySelectorAll('.load-price-' + rowId).forEach((v) => {
						v.innerHTML = result.regularPrice;
					});
					const finalQty = Math.max(1, parseInt(result.quantity || nextQty, 10) || nextQty);
					inputElement.value = String(finalQty);
					const synced = this.syncCartSubtotal(result.tempText || result.totalText || '');
					if (!synced) this.wrapper.querySelector('.load-price-total').innerHTML = result.totalText;
					this.applyCartStockStates(result.cartStockByRow || this.cartStockByRow || {}, { notify: false });
					showNotify('Cập nhật thành công.', 'Thông báo', 'success');
					return;
				}

				const rollbackQty = Math.max(1, parseInt(result?.currentQty || currentQty, 10) || currentQty);
				inputElement.value = String(rollbackQty);
				if (result?.cartStockByRow && typeof result.cartStockByRow === 'object') {
					this.applyCartStockStates(result.cartStockByRow, { notify: false });
				} else if (rowId) {
					const fallbackState = {};
					fallbackState[rowId] = {
						in_stock: false,
						available_qty: (typeof result?.availableQty === 'number') ? result.availableQty : null,
						message: String(result?.message || '').trim()
					};
					this.applyCartStockStates(Object.assign({}, this.cartStockByRow || {}, fallbackState), { notify: false });
				}

				showNotify(result?.message || 'Cập nhật thất bại.', 'Thông báo', 'warning');
			})
			.catch(() => {
				inputElement.value = String(currentQty);
				showNotify('Không thể cập nhật giỏ hàng. Vui lòng thử lại.', 'Thông báo', 'error');
			})
			.finally(() => {
				holdonClose();
			});
	}

	populateSelect(optionsArray = [], selectElement = '', config = {}) {
		if (!selectElement) return;

		const placeholder = String(config.placeholder || 'Chọn').trim();
		const selectedValue = String(config.selectedValue || '').trim();
		const selectedText = String(config.selectedText || '').trim();
		const normalizedSelectedText = this.normalizeTextValue(selectedText);

		selectElement.innerHTML = '';
		const defaultOption = document.createElement('option');
		defaultOption.value = '';
		defaultOption.textContent = placeholder;
		selectElement.appendChild(defaultOption);

		let hasSelected = false;
		optionsArray.forEach((optionText) => {
			const optionElement = document.createElement('option');
			const optionValue = String(optionText.id || '').trim();
			const optionLabel = String(optionText.namevi || '').trim();
			optionElement.textContent = optionLabel;
			optionElement.value = optionValue;

			const isSelectedById = !!selectedValue && optionValue === selectedValue;
			const isSelectedByName = !isSelectedById && !!normalizedSelectedText &&
				this.normalizeTextValue(optionLabel) === normalizedSelectedText;
			if (isSelectedById || isSelectedByName) {
				optionElement.selected = true;
				hasSelected = true;
			}

			selectElement.appendChild(optionElement);
		});

		if (!hasSelected) {
			selectElement.value = '';
		}
	}
}
