class WishlistManager {
	constructor(options) {
		this.wrapper = document.body;
		this.urls = options?.urls || {};
		this.cartUrl = options?.cartUrl || {};
		this.isMember = !!options?.isMember;
		this.memberId = parseInt(options?.memberId || 0, 10) || 0;
		this.storageKey = 'wishlist';
		this.localKeys = new Set();
		this.serverKeys = new Set();
		this.publicListEl = null;
		this.loadingPublicList = false;
		this.variantModal = null;
		this.variantState = null;
		this.init();
	}

	init() {
		this.publicListEl = document.querySelector('.js-wishlist-public-list');
		this.wrapper.addEventListener('click', (e) => {
			const toggleButton = e.target.closest('.js-wishlist-toggle');
			if (toggleButton) {
				e.preventDefault();
				this.toggle(toggleButton);
				return;
			}

			const removeButton = e.target.closest('.js-wishlist-remove');
			if (removeButton) {
				e.preventDefault();
				this.removeFromAccount(removeButton);
				return;
			}

			const addCartButton = e.target.closest('.js-wishlist-add-cart');
			if (addCartButton) {
				e.preventDefault();
				this.addToCart(addCartButton);
				return;
			}

			if (e.target.closest('.js-wishlist-variant-close') || e.target.classList.contains('wishlist-variant-modal')) {
				e.preventDefault();
				this.closeVariantModal();
				return;
			}

			const variantOption = e.target.closest('.js-wishlist-variant-option');
			if (variantOption) {
				e.preventDefault();
				this.selectVariantOption(variantOption);
				return;
			}

			const variantSubmit = e.target.closest('.js-wishlist-variant-submit');
			if (variantSubmit) {
				e.preventDefault();
				this.submitVariantModal();
				return;
			}

			const qtyMinus = e.target.closest('.js-wishlist-qty-minus');
			if (qtyMinus) {
				e.preventDefault();
				this.adjustVariantQty(-1);
				return;
			}

			const qtyPlus = e.target.closest('.js-wishlist-qty-plus');
			if (qtyPlus) {
				e.preventDefault();
				this.adjustVariantQty(1);
				return;
			}
		});

		this.wrapper.addEventListener('input', (e) => {
			if (e.target.closest('.js-wishlist-qty-input')) {
				this.syncVariantQtyFromInput(e.target.closest('.js-wishlist-qty-input'));
			}
		});

		this.wrapper.addEventListener('click', (e) => {
			if (e.target.closest('.properties')) {
				setTimeout(() => this.applyButtonsState(), 20);
			}
		});

		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape') {
				this.closeVariantModal();
			}
		});

		if (this.isMember) {
			this.bootstrapMember();
			return;
		}

		this.localKeys = this.buildKeysFromLocal();
		this.renderState();
	}

	bootstrapMember() {
		const localItems = this.getLocalItems();
		if (localItems.length > 0) {
			this.post(this.urls.MERGE, {
				items: JSON.stringify(localItems),
				csrf_token: CSRF_TOKEN
			}).then((payload) => {
				if (payload?.success) {
					this.clearLocalItems();
					this.serverKeys = new Set(payload.ids || []);
					this.renderState();
					return;
				}
				this.loadServerState();
			}).catch(() => this.loadServerState());
			return;
		}

		this.loadServerState();
	}

	loadServerState() {
		this.post(this.urls.STATE, { csrf_token: CSRF_TOKEN }).then((payload) => {
			if (!payload?.success) return;
			this.serverKeys = new Set(payload.ids || []);
			this.renderState();
		});
	}

	toggle(button) {
		const productId = parseInt(button.getAttribute('data-product-id') || '0', 10);
		const variantId = this.resolveVariantId(button);
		if (productId <= 0) return;

		if (!this.isMember) {
			const active = this.toggleLocal(productId, variantId);
			this.showNotice('Đã cập nhật danh sách yêu thích.');
			if (active && !sessionStorage.getItem('wishlist_login_hint')) {
				this.showNotice('Đăng nhập để lưu wishlist vĩnh viễn trên mọi thiết bị.');
				sessionStorage.setItem('wishlist_login_hint', '1');
			}
			return;
		}

		this.post(this.urls.TOGGLE, {
			product_id: productId,
			variant_id: variantId,
			csrf_token: CSRF_TOKEN
		}).then((payload) => {
			if (!payload?.success) {
				this.showNotice(payload?.message || 'Không thể cập nhật yêu thích. Vui lòng thử lại.', 'error');
				return;
			}
			this.serverKeys = new Set(payload.ids || []);
			this.renderState();
		}).catch(() => {
			this.showNotice('Không thể cập nhật yêu thích. Vui lòng thử lại.', 'error');
		});
	}

	toggleLocal(productId, variantId) {
		const key = this.makeKey(productId, variantId);
		const items = this.getLocalItems();
		const index = items.findIndex((item) => this.makeKey(item.productId, this.normalizeVariantId(item.variantId || '')) === key);
		let active = true;
		if (index >= 0) {
			items.splice(index, 1);
			active = false;
		} else {
			items.unshift({
				productId: String(productId),
				variantId: variantId,
				addedAt: new Date().toISOString()
			});
		}

		this.setLocalItems(items);
		this.localKeys = this.buildKeysFromLocal();
		this.renderState();
		return active;
	}

	removeFromAccount(button) {
		const card = button.closest('.account-wishlist-card');
		const productId = parseInt(button.getAttribute('data-product-id') || '0', 10);
		const variantId = this.normalizeVariantId(button.getAttribute('data-variant-id') || '');
		if (!this.isMember) {
			if (productId <= 0) return;
			this.removeLocal(productId, variantId);
			if (card) card.remove();
			this.ensureEmptyWishlistPanel(this.publicListEl || card?.parentElement || null);
			return;
		}

		const wishlistId = parseInt(button.getAttribute('data-wishlist-id') || '0', 10);

		this.post(this.urls.REMOVE, {
			wishlist_id: wishlistId,
			product_id: productId,
			variant_id: variantId,
			csrf_token: CSRF_TOKEN
		}).then((payload) => {
			if (!payload?.success) {
				this.showNotice(payload?.message || 'Không thể xóa yêu thích. Vui lòng thử lại.', 'error');
				return;
			}
			this.serverKeys = new Set(payload.ids || []);
			if (card) card.remove();
			this.renderState();
			this.ensureEmptyWishlistPanel(this.publicListEl || card?.parentElement || null);
		}).catch(() => {
			this.showNotice('Không thể xóa yêu thích. Vui lòng thử lại.', 'error');
		});
	}

	addToCart(button) {
		const productId = parseInt(button.getAttribute('data-product-id') || '0', 10);
		if (productId <= 0 || !this.cartUrl?.ADD_CART) return;
		const productName = button.getAttribute('data-product-name') || '';
		const productPhoto = button.getAttribute('data-product-photo') || '';
		const sourceCard = button.closest('.account-wishlist-card');

		const variantId = this.normalizeVariantId(button.getAttribute('data-variant-id') || '');
		const properties = this.variantToProperties(variantId);
		if (properties.length > 0) {
			this.openVariantModal({
				productId,
				productName,
				productPhoto,
				groups: [],
				combinations: [],
				preselectedProperties: properties,
				requireSelection: false,
				sourceCard
			});
			return;
		}

		this.post(this.urls.VARIANT_OPTIONS, {
			product_id: productId,
			csrf_token: CSRF_TOKEN
		}).then((payload) => {
			if (!payload?.success) {
				this.showNotice(payload?.message || 'Không thể tải phân loại sản phẩm.', 'error');
				if (this.isStockFailureMessage(payload?.message || '')) {
					this.markWishlistCardOutOfStock(sourceCard);
				}
				return;
			}

			const directVariantId = this.normalizeVariantId(payload.suggested_variant_id || '');
			const directProperties = this.variantToProperties(directVariantId);
			if (payload.can_direct_add || !payload.has_variants || ((payload.combinations || []).length <= 1 && directProperties.length > 0)) {
				this.openVariantModal({
					productId,
					productName: payload.product_name || productName,
					productPhoto,
					groups: [],
					combinations: [],
					preselectedProperties: directProperties,
					requireSelection: false,
					sourceCard
				});
				return;
			}

			this.openVariantModal({
				productId,
				productName: payload.product_name || '',
				productPhoto,
				groups: Array.isArray(payload.groups) ? payload.groups : [],
				combinations: Array.isArray(payload.combinations) ? payload.combinations : [],
				preselectedProperties: [],
				requireSelection: true,
				sourceCard
			});
		}).catch(() => {
			this.showNotice('Không thể thêm vào giỏ hàng. Vui lòng thử lại.', 'error');
		});
	}

	submitAddToCart(productId, properties = [], quantity = 1) {
		const payload = {
			id: productId,
			quantity: Math.max(1, parseInt(quantity || 1, 10)),
			properties: JSON.stringify(Array.isArray(properties) ? properties : []),
			csrf_token: CSRF_TOKEN
		};

		this.post(this.cartUrl.ADD_CART, payload).then((result) => {
			if (!result) {
				this.showNotice('Không thể thêm vào giỏ hàng. Vui lòng thử lại.', 'error');
				return;
			}
			if (result.error || result.success === false) {
				this.showNotice(result.message || 'Không thể thêm vào giỏ hàng. Vui lòng thử lại.', 'warning');
				if (this.isStockFailureMessage(result.message || '')) {
					this.markWishlistCardOutOfStock(this.variantState?.sourceCard || null);
				}
				return;
			}
			const countCart = document.querySelector('.count-cart');
			if (countCart && typeof result.max !== 'undefined') {
				countCart.textContent = result.max;
			}
			this.showNotice('Đã thêm vào giỏ hàng.');
			this.closeVariantModal();
		}).catch(() => {
			this.showNotice('Không thể thêm vào giỏ hàng. Vui lòng thử lại.', 'error');
		});
	}

	openVariantModal(data) {
		const groups = Array.isArray(data?.groups) ? data.groups : [];
		const combinations = (Array.isArray(data?.combinations) ? data.combinations : [])
			.map((combo) => this.normalizeVariantId(combo))
			.filter((combo) => combo !== '');

		this.ensureVariantModal();
		const requireSelection = !!data?.requireSelection && groups.length > 0 && combinations.length > 0;
		this.variantState = {
			productId: parseInt(data.productId || 0, 10) || 0,
			productName: data.productName || '',
			productPhoto: data.productPhoto || '',
			groups,
			combinations,
			selectedByGroup: {},
			requireSelection,
			preselectedProperties: Array.isArray(data?.preselectedProperties) ? data.preselectedProperties : [],
			quantity: 1,
			sourceCard: data?.sourceCard || null
		};

		if (requireSelection) {
			const firstCombo = combinations[0] || '';
			const firstIds = this.variantToProperties(firstCombo);
			groups.forEach((group) => {
				const options = Array.isArray(group.options) ? group.options : [];
				const optionByCombo = options.find((opt) => firstIds.includes(parseInt(opt.id || 0, 10)));
				const fallbackOption = options[0];
				const selected = optionByCombo || fallbackOption || null;
				if (selected) {
					this.variantState.selectedByGroup[String(group.list_id)] = parseInt(selected.id, 10);
				}
			});
		}

		const titleNode = this.variantModal.querySelector('.js-wishlist-variant-title');
		const bodyNode = this.variantModal.querySelector('.js-wishlist-variant-body');
		if (titleNode) {
			titleNode.textContent = 'Chọn phân loại sản phẩm';
		}
		if (bodyNode) {
			const summaryHtml = this.renderVariantSummary(this.variantState.productPhoto, this.variantState.productName);
			const groupsHtml = requireSelection ? this.renderVariantGroups(groups) : '';
			const qtyHtml = this.renderVariantQuantity(this.variantState.quantity);
			bodyNode.innerHTML = summaryHtml + groupsHtml + qtyHtml;
		}

		if (requireSelection) {
			this.refreshVariantOptionsState();
		}
		this.variantModal.classList.add('is-open');
		document.body.classList.add('wishlist-modal-open');
	}

	closeVariantModal() {
		if (!this.variantModal) return;
		this.variantModal.classList.remove('is-open');
		document.body.classList.remove('wishlist-modal-open');
	}

	ensureVariantModal() {
		if (this.variantModal) return;
		const root = document.createElement('div');
		root.className = 'wishlist-variant-modal';
		root.innerHTML = [
			'<div class="wishlist-variant-modal__dialog" role="dialog" aria-modal="true">',
			'<button type="button" class="wishlist-variant-modal__close js-wishlist-variant-close" aria-label="Đóng">×</button>',
			'<h3 class="wishlist-variant-modal__title js-wishlist-variant-title">Chọn phân loại sản phẩm</h3>',
			'<div class="wishlist-variant-modal__body js-wishlist-variant-body"></div>',
			'<div class="wishlist-variant-modal__foot">',
			'<button type="button" class="account-btn account-btn--outline js-wishlist-variant-close">Hủy</button>',
			'<button type="button" class="account-btn js-wishlist-variant-submit">Thêm vào giỏ</button>',
			'</div>',
			'</div>'
		].join('');
		document.body.appendChild(root);
		this.variantModal = root;
	}

	renderVariantGroups(groups = []) {
		return groups.map((group) => {
			const listId = String(group.list_id || '');
			const listName = group.list_name || 'Phân loại';
			const options = Array.isArray(group.options) ? group.options : [];
			const optionHtml = options.map((option) => {
				const id = parseInt(option.id || 0, 10) || 0;
				return '<button type="button" class="wishlist-variant-option js-wishlist-variant-option" data-list-id="' + listId + '" data-option-id="' + id + '">' + this.escapeHtml(option.name || '') + '</button>';
			}).join('');
			return [
				'<div class="wishlist-variant-group">',
				'<p class="wishlist-variant-group__name">' + this.escapeHtml(listName) + '</p>',
				'<div class="wishlist-variant-group__options">' + optionHtml + '</div>',
				'</div>'
			].join('');
		}).join('');
	}

	renderVariantSummary(photoUrl = '', productName = '') {
		const hasPhoto = !!String(photoUrl || '').trim();
		const safeName = this.escapeHtml(productName || 'Sản phẩm');
		return [
			'<div class="wishlist-variant-summary">',
			hasPhoto
				? '<div class="wishlist-variant-summary__thumb"><img src="' + this.escapeHtml(photoUrl) + '" alt="' + safeName + '"></div>'
				: '<div class="wishlist-variant-summary__thumb is-empty">N/A</div>',
			'<p class="wishlist-variant-summary__name">' + safeName + '</p>',
			'</div>'
		].join('');
	}

	renderVariantQuantity(quantity = 1) {
		const qty = Math.max(1, parseInt(quantity || 1, 10));
		return [
			'<div class="wishlist-variant-qty">',
			'<p class="wishlist-variant-qty__name">Số lượng</p>',
			'<div class="wishlist-variant-qty__control">',
			'<button type="button" class="wishlist-variant-qty__btn js-wishlist-qty-minus" aria-label="Giảm số lượng">-</button>',
			'<input type="number" min="1" step="1" class="wishlist-variant-qty__input js-wishlist-qty-input" value="' + qty + '">',
			'<button type="button" class="wishlist-variant-qty__btn js-wishlist-qty-plus" aria-label="Tăng số lượng">+</button>',
			'</div>',
			'</div>'
		].join('');
	}

	adjustVariantQty(delta = 0) {
		if (!this.variantModal || !this.variantState) return;
		const input = this.variantModal.querySelector('.js-wishlist-qty-input');
		if (!input) return;
		const current = Math.max(1, parseInt(input.value || '1', 10) || 1);
		const next = Math.max(1, current + parseInt(delta || 0, 10));
		input.value = String(next);
		this.variantState.quantity = next;
	}

	syncVariantQtyFromInput(input) {
		if (!this.variantState || !input) return;
		const next = Math.max(1, parseInt(input.value || '1', 10) || 1);
		input.value = String(next);
		this.variantState.quantity = next;
	}

	selectVariantOption(button) {
		if (!this.variantState) return;
		const listId = String(button.getAttribute('data-list-id') || '');
		const optionId = parseInt(button.getAttribute('data-option-id') || '0', 10) || 0;
		if (!listId || optionId <= 0 || button.disabled) return;

		this.variantState.selectedByGroup[listId] = optionId;
		this.refreshVariantOptionsState();
	}

	refreshVariantOptionsState() {
		if (!this.variantState || !this.variantModal) return;
		const buttons = this.variantModal.querySelectorAll('.js-wishlist-variant-option');
		const selectedByGroup = this.variantState.selectedByGroup || {};
		const combinationSet = new Set(this.variantState.combinations || []);

		buttons.forEach((button) => {
			const listId = String(button.getAttribute('data-list-id') || '');
			const optionId = parseInt(button.getAttribute('data-option-id') || '0', 10) || 0;
			const isSelected = selectedByGroup[listId] === optionId;

			const draft = { ...selectedByGroup, [listId]: optionId };
			const isAllowed = this.canBuildCombination(draft, combinationSet);
			button.classList.toggle('is-active', isSelected);
			button.disabled = !isAllowed;
		});
	}

	canBuildCombination(selectedByGroup = {}, combinationSet = new Set()) {
		const selectedIds = Object.values(selectedByGroup)
			.map((id) => parseInt(id || 0, 10))
			.filter((id) => id > 0);
		if (!selectedIds.length) return true;

		return Array.from(combinationSet).some((combo) => {
			const comboIds = this.variantToProperties(combo);
			return selectedIds.every((id) => comboIds.includes(id));
		});
	}

	submitVariantModal() {
		if (!this.variantState) return;
		const quantity = Math.max(1, parseInt(this.variantState.quantity || 1, 10));
		if (!this.variantState.requireSelection) {
			this.submitAddToCart(this.variantState.productId, this.variantState.preselectedProperties || [], quantity);
			return;
		}

		const selectedIds = Object.values(this.variantState.selectedByGroup || {})
			.map((id) => parseInt(id || 0, 10))
			.filter((id) => id > 0);
		if (!selectedIds.length) {
			this.showNotice('Vui lòng chọn phân loại trước khi thêm giỏ hàng.', 'error');
			return;
		}

		const variantId = this.normalizeVariantId(selectedIds.join(','));
		const comboSet = new Set(this.variantState.combinations || []);
		if (!comboSet.has(variantId)) {
			this.showNotice('Biến thể bạn chọn hiện không khả dụng.', 'error');
			return;
		}

		this.submitAddToCart(this.variantState.productId, this.variantToProperties(variantId), quantity);
	}

	ensureEmptyWishlistPanel(panel = null) {
		const target = panel || document.querySelector('.account-wishlist-list');
		if (!target) return;
		const listSelector = '.account-wishlist-card';
		if (target.querySelector(listSelector)) return;
		if (target.querySelector('.alert')) return;

		const message = document.createElement('div');
		message.className = 'alert alert-info';
		message.textContent = 'Bạn chưa có sản phẩm yêu thích nào.';
		target.appendChild(message);
	}

	renderPublicList() {
		if (!this.publicListEl || this.loadingPublicList) return;
		this.loadingPublicList = true;

		if (this.isMember) {
			this.post(this.urls.LIST, { csrf_token: CSRF_TOKEN }).then((payload) => {
				this.loadingPublicList = false;
				if (!payload?.success) {
					this.publicListEl.innerHTML = '<div class="alert alert-danger">Không thể tải danh sách yêu thích.</div>';
					return;
				}
				this.renderPublicItems(payload.items || []);
			}).catch(() => {
				this.loadingPublicList = false;
				this.publicListEl.innerHTML = '<div class="alert alert-danger">Không thể tải danh sách yêu thích.</div>';
			});
			return;
		}

		const localItems = this.getLocalItems();
		if (!localItems.length) {
			this.loadingPublicList = false;
			this.publicListEl.innerHTML = '<div class="alert alert-info">Bạn chưa có sản phẩm yêu thích nào.</div>';
			return;
		}

		this.post(this.urls.GUEST_LIST, {
			items: JSON.stringify(localItems),
			csrf_token: CSRF_TOKEN
		}).then((payload) => {
			this.loadingPublicList = false;
			if (!payload?.success) {
				this.publicListEl.innerHTML = '<div class="alert alert-danger">Không thể tải danh sách yêu thích.</div>';
				return;
			}
			this.renderPublicItems(payload.items || []);
		}).catch(() => {
			this.loadingPublicList = false;
			this.publicListEl.innerHTML = '<div class="alert alert-danger">Không thể tải danh sách yêu thích.</div>';
		});
	}

	renderPublicItems(items = []) {
		if (!this.publicListEl) return;
		if (!Array.isArray(items) || !items.length) {
			this.publicListEl.innerHTML = '<div class="alert alert-info">Bạn chưa có sản phẩm yêu thích nào.</div>';
			return;
		}

		this.publicListEl.innerHTML = items.map((item) => {
			const exists = !!item.exists;
			const title = this.escapeHtml(item.name || 'Sản phẩm');
			const url = item.url || '';
			const variantName = this.escapeHtml(item.variant_name || '');
			const photoHtml = item.photo_url
				? (url
					? '<a href="' + this.escapeHtml(url) + '" title="' + title + '"><img src="' + this.escapeHtml(item.photo_url) + '" alt="' + title + '"></a>'
					: '<img src="' + this.escapeHtml(item.photo_url) + '" alt="' + title + '">')
				: '<span class="account-wishlist-thumb__empty">N/A</span>';
			const titleHtml = url
				? '<a href="' + this.escapeHtml(url) + '">' + title + '</a>'
				: title;
			const variantHtml = variantName !== '' ? '<p class="account-wishlist-meta">Phân loại: ' + variantName + '</p>' : '';
			const statusHtml = !exists
				? '<p class="account-wishlist-state account-wishlist-state--missing">Sản phẩm không còn tồn tại</p>'
				: '<div class="account-wishlist-price"><strong>' + this.escapeHtml(item.price_current_text || 'Liên hệ') + '</strong>' + (((item.price_sale || 0) > 0 && (item.price_regular || 0) > (item.price_sale || 0)) ? '<span>' + this.escapeHtml(item.price_regular_text || '') + '</span>' : '') + '</div>';
			const outOfStockNotice = (exists && !item.can_add_to_cart)
				? '<p class="account-wishlist-state account-wishlist-state--missing js-wishlist-out-of-stock">Sản phẩm đã hết hàng</p>'
				: '';
			const addBtn = item.can_add_to_cart
				? '<button type="button" class="btn account-btn account-btn--outline js-wishlist-add-cart" data-product-id="' + parseInt(item.product_id || 0, 10) + '" data-variant-id="' + this.escapeHtml(item.variant_id || '') + '" data-product-name="' + title + '" data-product-photo="' + this.escapeHtml(item.photo_url || '') + '">Thêm vào giỏ</button>'
				: '';
			return '' +
				'<article class="account-wishlist-card ' + (exists ? '' : 'is-missing') + '">' +
				'<div class="account-wishlist-thumb">' + photoHtml + '</div>' +
				'<div class="account-wishlist-content">' +
				'<p class="account-wishlist-title">' + titleHtml + '</p>' +
				variantHtml +
				statusHtml +
				'<div class="account-wishlist-actions">' +
				addBtn +
				'<button type="button" class="btn account-btn account-btn--outline js-wishlist-remove" data-wishlist-id="' + parseInt(item.wishlist_id || 0, 10) + '" data-product-id="' + parseInt(item.product_id || 0, 10) + '" data-variant-id="' + this.escapeHtml(item.variant_id || '') + '">Xóa</button>' +
				'</div>' +
				outOfStockNotice +
				'</div>' +
				'</article>';
		}).join('');
	}

	removeLocal(productId, variantId) {
		const key = this.makeKey(productId, variantId);
		const items = this.getLocalItems().filter((item) => {
			const itemKey = this.makeKey(item.productId || item.product_id || 0, item.variantId || item.variant_id || '');
			return itemKey !== key;
		});
		this.setLocalItems(items);
		this.localKeys = this.buildKeysFromLocal();
		this.renderState();
	}

	renderState() {
		this.applyButtonsState();
		this.updateCount();
		this.renderPublicList();
	}

	applyButtonsState() {
		const buttons = this.wrapper.querySelectorAll('.js-wishlist-toggle');
		buttons.forEach((button) => {
			const productId = parseInt(button.getAttribute('data-product-id') || '0', 10);
			if (productId <= 0) return;
			const variantId = this.resolveVariantId(button);
			const key = this.makeKey(productId, variantId);
			const active = this.isMember ? this.serverKeys.has(key) : this.localKeys.has(key);
			button.classList.toggle('is-active', !!active);
			this.swapHeartIcon(button, !!active);
		});
	}

	swapHeartIcon(button, active) {
		const icon = button.querySelector('i');
		if (!icon) return;

		if (icon.classList.contains('bi-heart') || icon.classList.contains('bi-heart-fill')) {
			icon.classList.toggle('bi-heart-fill', active);
			icon.classList.toggle('bi-heart', !active);
		}
	}

	updateCount() {
		const count = this.isMember ? this.serverKeys.size : this.localKeys.size;
		this.wrapper.querySelectorAll('.js-wishlist-count').forEach((el) => {
			el.textContent = String(count);
		});
	}

	resolveVariantId(button) {
		const source = (button.getAttribute('data-variant-source') || '').trim();
		const fixedVariant = this.normalizeVariantId(button.getAttribute('data-variant-id') || '');
		if (source !== 'detail') return fixedVariant;

		const activeProps = Array.from(document.querySelectorAll('.grid-properties .properties.active'))
			.map((el) => parseInt(el.getAttribute('data-id') || '0', 10))
			.filter((v) => v > 0);
		if (!activeProps.length) return fixedVariant;
		return this.normalizeVariantId(activeProps.join(','));
	}

	makeKey(productId, variantId = '') {
		return String(parseInt(productId || 0, 10)) + ':' + this.normalizeVariantId(variantId);
	}

	normalizeVariantId(raw) {
		const value = String(raw || '').trim();
		if (!value) return '';
		if (/^\d+(,\d+)*$/.test(value)) {
			const ids = Array.from(new Set(value.split(',').map((v) => parseInt(v, 10)).filter((v) => v > 0))).sort((a, b) => a - b);
			return ids.join(',');
		}
		return value.replace(/[^a-zA-Z0-9,_-]/g, '').slice(0, 191);
	}

	variantToProperties(variantId) {
		const value = this.normalizeVariantId(variantId);
		if (!value || !/^\d+(,\d+)*$/.test(value)) return [];
		return value.split(',').map((v) => parseInt(v, 10)).filter((v) => v > 0);
	}

	getLocalItems() {
		try {
			const raw = localStorage.getItem(this.storageKey);
			const parsed = raw ? JSON.parse(raw) : [];
			return Array.isArray(parsed) ? parsed : [];
		} catch (e) {
			return [];
		}
	}

	setLocalItems(items) {
		localStorage.setItem(this.storageKey, JSON.stringify(Array.isArray(items) ? items : []));
	}

	clearLocalItems() {
		localStorage.removeItem(this.storageKey);
		this.localKeys = new Set();
	}

	buildKeysFromLocal() {
		const keys = new Set();
		this.getLocalItems().forEach((item) => {
			const productId = parseInt(item.productId || item.product_id || 0, 10);
			const variantId = this.normalizeVariantId(item.variantId || item.variant_id || '');
			if (productId > 0) {
				keys.add(this.makeKey(productId, variantId));
			}
		});
		return keys;
	}

	post(url, payload = {}) {
		if (!url) return Promise.resolve(null);
		const formData = new FormData();
		Object.keys(payload).forEach((key) => {
			formData.append(key, payload[key]);
		});

		return fetch(url, {
			method: 'POST',
			headers: { 'X-Requested-With': 'XMLHttpRequest' },
			body: formData,
			credentials: 'same-origin'
		}).then((response) => {
			return response.json().catch(() => null);
		});
	}

	escapeHtml(text) {
		return String(text || '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#39;');
	}

	isStockFailureMessage(message = '') {
		const text = String(message || '').toLowerCase();
		return text.includes('hết hàng')
			|| text.includes('het hang')
			|| text.includes('tồn kho')
			|| text.includes('ton kho')
			|| text.includes('không còn tồn tại')
			|| text.includes('khong con ton tai');
	}

	markWishlistCardOutOfStock(card) {
		if (!card) return;

		const addButton = card.querySelector('.js-wishlist-add-cart');
		if (addButton) {
			addButton.remove();
		}

		let notice = card.querySelector('.js-wishlist-out-of-stock');
		if (!notice) {
			notice = document.createElement('p');
			notice.className = 'account-wishlist-state account-wishlist-state--missing js-wishlist-out-of-stock';
			const actions = card.querySelector('.account-wishlist-actions');
			if (actions) {
				actions.insertAdjacentElement('afterend', notice);
			} else {
				const content = card.querySelector('.account-wishlist-content');
				if (content) {
					content.appendChild(notice);
				}
			}
		}

		notice.textContent = 'Sản phẩm đã hết hàng';
	}

	showNotice(text, status = 'success') {
		if (typeof showNotify === 'function') {
			showNotify(text, 'Thông báo', status);
		}
	}
}

window.addEventListener('load', function () {
	window.WISHLIST = new WishlistManager({
		urls: typeof WISHLIST_URL !== 'undefined' ? WISHLIST_URL : {},
		cartUrl: typeof CART_URL !== 'undefined' ? CART_URL : {},
		isMember: typeof IS_MEMBER !== 'undefined' ? IS_MEMBER : false,
		memberId: typeof MEMBER_ID !== 'undefined' ? MEMBER_ID : 0
	});
});
