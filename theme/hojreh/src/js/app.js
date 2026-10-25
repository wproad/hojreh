/**
 * Hojreh front-end behaviour. No dependencies.
 */
(() => {
	'use strict';

	const cfg = window.HOJREH || {};
	const doc = document;
	const html = doc.documentElement;
	const body = doc.body;
	const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	const $ = (selector, root = doc) => root.querySelector(selector);
	const $$ = (selector, root = doc) => Array.from(root.querySelectorAll(selector));

	const t = (key, fallback) => (cfg.i18n && cfg.i18n[key]) || fallback || '';

	const faDigits = (value) =>
		String(value).replace(/[0-9]/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]);

	/* ---------------------------------------------------------------------
	 * Theme
	 * ------------------------------------------------------------------ */
	function setTheme(dark) {
		html.classList.toggle('dark', dark);
		html.style.colorScheme = dark ? 'dark' : 'light';
		try {
			localStorage.setItem('hojreh-theme', dark ? 'dark' : 'light');
		} catch (error) {}
		$$('[data-theme-toggle]').forEach((button) => {
			button.setAttribute('aria-pressed', dark ? 'true' : 'false');
		});
	}

	$$('[data-theme-toggle]').forEach((button) => {
		button.setAttribute('aria-pressed', html.classList.contains('dark') ? 'true' : 'false');
		button.addEventListener('click', () => setTheme(!html.classList.contains('dark')));
	});

	try {
		if (!localStorage.getItem('hojreh-theme')) {
			window.matchMedia('(prefers-color-scheme: dark)').addEventListener?.('change', (event) => {
				try {
					if (!localStorage.getItem('hojreh-theme')) {
						html.classList.toggle('dark', event.matches);
						html.style.colorScheme = event.matches ? 'dark' : 'light';
					}
				} catch (error) {}
			});
		}
	} catch (error) {}

	/* ---------------------------------------------------------------------
	 * Header panels
	 * ------------------------------------------------------------------ */
	const searchPanel = $('#hojreh-search');
	const searchToggle = $('[data-search-toggle]');
	const mobileMenu = $('#hojreh-mobile-menu');
	const menuToggle = $('[data-menu-toggle]');

	function closeSearch() {
		if (!searchPanel) return;
		searchPanel.hidden = true;
		searchToggle?.setAttribute('aria-expanded', 'false');
	}

	function closeMobileMenu() {
		if (!mobileMenu) return;
		mobileMenu.hidden = true;
		menuToggle?.setAttribute('aria-expanded', 'false');
	}

	searchToggle?.addEventListener('click', () => {
		if (!searchPanel) return;
		const willOpen = searchPanel.hidden;
		closeMobileMenu();
		searchPanel.hidden = !willOpen;
		searchToggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
		if (willOpen) {
			requestAnimationFrame(() => $('#hojreh-search-input')?.focus());
		}
	});

	menuToggle?.addEventListener('click', () => {
		if (!mobileMenu) return;
		const willOpen = mobileMenu.hidden;
		closeSearch();
		mobileMenu.hidden = !willOpen;
		menuToggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
	});

	doc.addEventListener('keydown', (event) => {
		if (event.key !== 'Escape') return;
		closeSearch();
		closeMobileMenu();
	});

	doc.addEventListener('click', (event) => {
		if (searchPanel && !searchPanel.hidden && !searchPanel.contains(event.target) && !searchToggle?.contains(event.target)) {
			closeSearch();
		}

		if (mobileMenu && !mobileMenu.hidden && !mobileMenu.contains(event.target) && !menuToggle?.contains(event.target)) {
			closeMobileMenu();
		}
	});

	/* ---------------------------------------------------------------------
	 * Drawer
	 * ------------------------------------------------------------------ */
	const drawer = $('#hojreh-drawer');
	const backdrop = $('[data-drawer-backdrop]');
	const drawerBody = $('#hojreh-drawer-body');
	let lastFocused = null;

	function drawerIsOpen() {
		return drawer?.classList.contains('is-open');
	}

	function openDrawer() {
		if (!drawer) return;
		lastFocused = doc.activeElement;
		if (backdrop) {
			backdrop.hidden = false;
			requestAnimationFrame(() => backdrop.classList.add('is-open'));
		}
		drawer.classList.add('is-open');
		drawer.setAttribute('aria-hidden', 'false');
		body.classList.add('hojreh-no-scroll');
		requestAnimationFrame(() => $('[data-drawer-close]', drawer)?.focus());
	}

	function closeDrawer() {
		if (!drawer || !drawerIsOpen()) return;
		drawer.classList.remove('is-open');
		drawer.setAttribute('aria-hidden', 'true');
		body.classList.remove('hojreh-no-scroll');
		backdrop?.classList.remove('is-open');
		window.setTimeout(() => {
			if (backdrop && !drawerIsOpen()) backdrop.hidden = true;
		}, 240);
		if (lastFocused instanceof HTMLElement) lastFocused.focus();
	}

	doc.addEventListener('click', (event) => {
		const trigger = event.target.closest('[data-drawer-open]');
		if (trigger) {
			event.preventDefault();
			openDrawer();
			return;
		}

		if (event.target.closest('[data-drawer-close]')) {
			closeDrawer();
		}
	});

	backdrop?.addEventListener('click', closeDrawer);

	doc.addEventListener('keydown', (event) => {
		if (!drawerIsOpen()) return;

		if (event.key === 'Escape') {
			event.preventDefault();
			closeDrawer();
			return;
		}

		if (event.key !== 'Tab' || !drawer) return;

		const focusable = $$(
			'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
			drawer
		).filter((element) => element.offsetParent !== null);

		if (!focusable.length) return;

		const first = focusable[0];
		const last = focusable[focusable.length - 1];

		if (event.shiftKey && doc.activeElement === first) {
			event.preventDefault();
			last.focus();
		} else if (!event.shiftKey && doc.activeElement === last) {
			event.preventDefault();
			first.focus();
		}
	});

	/* ---------------------------------------------------------------------
	 * Toast
	 * ------------------------------------------------------------------ */
	const toastEl = $('#hojreh-toast');
	let toastTimer;

	function decodeEntities(value) {
		const decoder = doc.createElement('textarea');
		decoder.innerHTML = String(value);
		return decoder.value;
	}

	function toast(message, type = 'success') {
		if (!toastEl || !message) return;
		toastEl.textContent = decodeEntities(message);
		toastEl.dataset.type = type;
		toastEl.hidden = false;
		requestAnimationFrame(() => toastEl.classList.add('is-visible'));
		window.clearTimeout(toastTimer);
		toastTimer = window.setTimeout(() => {
			toastEl.classList.remove('is-visible');
			window.setTimeout(() => {
				if (!toastEl.classList.contains('is-visible')) toastEl.hidden = true;
			}, 220);
		}, 3400);
	}

	/* ---------------------------------------------------------------------
	 * Cart requests
	 * ------------------------------------------------------------------ */
	async function cartRequest(endpoint, data = {}) {
		if (!endpoint) throw new Error(t('error'));

		const params = new URLSearchParams();
		params.append('nonce', cfg.nonce || '');

		Object.entries(data).forEach(([key, value]) => {
			if (value && typeof value === 'object') {
				Object.entries(value).forEach(([innerKey, innerValue]) => {
					params.append(`${key}[${innerKey}]`, innerValue);
				});
			} else if (value !== undefined && value !== null) {
				params.append(key, value);
			}
		});

		const response = await fetch(endpoint, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
				'X-Requested-With': 'XMLHttpRequest',
			},
			body: params.toString(),
		});

		let json = null;

		try {
			json = await response.json();
		} catch (error) {}

		if (!response.ok || !json || !json.success) {
			throw new Error(json?.data?.message || t('error'));
		}

		return json.data;
	}

	function setCount(count) {
		$$('[data-cart-count]').forEach((el) => {
			el.textContent = faDigits(count);
			el.hidden = count < 1;
			el.classList.toggle('is-filled', count > 0);
		});
	}

	function syncCart(data) {
		if (!data) return;
		if (typeof data.count !== 'undefined') setCount(data.count);
		if (typeof data.html === 'string' && drawerBody) drawerBody.innerHTML = data.html;
	}

	/* ---------------------------------------------------------------------
	 * AJAX add to cart (cards + single product form)
	 * ------------------------------------------------------------------ */
	doc.addEventListener('click', async (event) => {
		const button = event.target.closest('[data-hojreh-atc]');
		if (!button) return;

		event.preventDefault();

		if (button.classList.contains('is-loading')) return;

		const original = button.innerHTML;
		button.classList.add('is-loading');
		button.setAttribute('aria-busy', 'true');
		button.disabled = true;

		try {
			const data = await cartRequest(cfg.ajax.add, {
				product_id: button.dataset.productId,
				quantity: button.dataset.quantity || 1,
			});

			syncCart(data);
			openDrawer();
			button.classList.add('is-added');
			window.setTimeout(() => button.classList.remove('is-added'), 1600);
		} catch (error) {
			toast(error.message || t('error'), 'error');
		} finally {
			button.classList.remove('is-loading');
			button.removeAttribute('aria-busy');
			button.disabled = false;
			button.innerHTML = original;
		}
	});

	// Single product form: intercept simple and variable products.
	doc.addEventListener('submit', async (event) => {
		const form = event.target;
		if (!(form instanceof HTMLFormElement) || !form.classList.contains('cart')) return;
		if (form.classList.contains('grouped_form')) return;

		const submitButton = form.querySelector('.single_add_to_cart_button');
		if (!submitButton) return;

		const formData = new FormData(form);
		const productId = formData.get('product_id') || formData.get('add-to-cart') || submitButton.value;
		const variationId = formData.get('variation_id');
		const quantity = formData.get('quantity') || 1;

		// A variable product with no selection is handled by WooCommerce's own validation.
		if (form.classList.contains('variations_form') && (!variationId || variationId === '0')) return;
		if (!productId) return;

		event.preventDefault();

		if (submitButton.classList.contains('is-loading')) return;

		const variations = {};
		formData.forEach((value, key) => {
			if (key.startsWith('attribute_')) variations[key] = value;
		});

		submitButton.classList.add('is-loading');
		submitButton.setAttribute('aria-busy', 'true');

		try {
			const data = await cartRequest(cfg.ajax.add, {
				product_id: productId,
				quantity,
				variation_id: variationId || 0,
				variations,
			});

			syncCart(data);
			openDrawer();

			try {
				if (window.jQuery) window.jQuery(form).trigger('reset_data');
			} catch (error) {}
		} catch (error) {
			toast(error.message || t('error'), 'error');
		} finally {
			submitButton.classList.remove('is-loading');
			submitButton.removeAttribute('aria-busy');
		}
	});

	// Compatibility with WooCommerce's own AJAX cart events (cart page remove/undo,
	// quantity updates, applied coupons…). Keep our badge and drawer in sync.
	if (window.jQuery) {
		const $body = window.jQuery(doc.body);

		$body.on('added_to_cart', (event, fragments, hash, button) => {
			if (button && button.hasAttribute('data-hojreh-atc')) return;
			cartRequest(cfg.ajax.cart)
				.then((data) => {
					syncCart(data);
					openDrawer();
				})
				.catch(() => {});
		});

		const syncOnWcEvent = () => {
			cartRequest(cfg.ajax.cart)
				.then(syncCart)
				.catch(() => {});
		};

		$body.on('removed_from_cart updated_cart_totals updated_wc_div wc_cart_emptied', syncOnWcEvent);
	}

	window.addEventListener('pageshow', (event) => {
		if (event.persisted) {
			cartRequest(cfg.ajax.cart)
				.then(syncCart)
				.catch(() => {});
		}
	});

	/* ---------------------------------------------------------------------
	 * Quantity steppers
	 * ------------------------------------------------------------------ */
	function enhanceQuantity(input) {
		if (input.closest('.hojreh-qty')) return;

		const wrapper = doc.createElement('div');
		wrapper.className = 'hojreh-qty';
		input.parentNode.insertBefore(wrapper, input);
		wrapper.appendChild(input);

		const minus = doc.createElement('button');
		minus.type = 'button';
		minus.className = 'hojreh-qty__btn';
		minus.dataset.qty = 'dec';
		minus.setAttribute('aria-label', 'کاهش تعداد');
		minus.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M5 12h14"></path></svg>';

		const plus = doc.createElement('button');
		plus.type = 'button';
		plus.className = 'hojreh-qty__btn';
		plus.dataset.qty = 'inc';
		plus.setAttribute('aria-label', 'افزایش تعداد');
		plus.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 5v14M5 12h14"></path></svg>';

		wrapper.insertBefore(minus, input);
		wrapper.appendChild(plus);

		const step = () => {
			const min = parseFloat(input.min || '0');
			const max = parseFloat(input.max || '');
			const current = parseFloat(input.value || '1');
			return { min: Number.isFinite(min) ? min : 0, max, current: Number.isFinite(current) ? current : 1 };
		};

		const update = (delta) => {
			const { min, max, current } = step();
			let next = current + delta;
			if (Number.isFinite(max) && max > 0) next = Math.min(next, max);
			next = Math.max(next, min);
			input.value = String(next);
			input.dispatchEvent(new Event('input', { bubbles: true }));
			input.dispatchEvent(new Event('change', { bubbles: true }));
		};

		minus.addEventListener('click', () => update(-1));
		plus.addEventListener('click', () => update(1));
	}

	$$('.quantity input.qty, .quantity input[type="number"]').forEach(enhanceQuantity);

	// Auto-submit the classic cart form after a quantity change (debounced).
	const cartForm = $('form.woocommerce-cart-form');

	if (cartForm) {
		let cartTimer;
		cartForm.addEventListener('change', (event) => {
			if (!event.target.matches('.quantity input')) return;
			window.clearTimeout(cartTimer);
			cartTimer = window.setTimeout(() => {
				const hidden = doc.createElement('input');
				hidden.type = 'hidden';
				hidden.name = 'update_cart';
				hidden.value = '1';
				cartForm.appendChild(hidden);
				cartForm.requestSubmit ? cartForm.requestSubmit() : cartForm.submit();
			}, 1000);
		});
	}

	/* ---------------------------------------------------------------------
	 * Drawer cart updates
	 * ------------------------------------------------------------------ */
	if (drawerBody) {
		let updateTimer;
		let pendingKey = '';

		const updateItem = (key, quantity) => {
			window.clearTimeout(updateTimer);
			pendingKey = key;
			drawerBody.querySelectorAll('.hojreh-qty').forEach((el) => {
				if (el.dataset.cartKey === key) el.classList.add('is-loading');
			});
			updateTimer = window.setTimeout(async () => {
				try {
					const data = await cartRequest(cfg.ajax.update, { key, quantity });
					syncCart(data);
				} catch (error) {
					toast(error.message || t('qtyError'), 'error');
					cartRequest(cfg.ajax.cart).then(syncCart).catch(() => {});
				}
			}, 450);
		};

		drawerBody.addEventListener('click', (event) => {
			const remove = event.target.closest('[data-hojreh-remove]');
			if (remove) {
				event.preventDefault();
				const key = remove.dataset.hojrehRemove;
				remove.classList.add('is-loading');
				remove.disabled = true;
				cartRequest(cfg.ajax.remove, { key })
					.then((data) => {
						syncCart(data);
					})
					.catch((error) => {
						remove.disabled = false;
						remove.classList.remove('is-loading');
						toast(error.message || t('error'), 'error');
					});
				return;
			}

			const button = event.target.closest('[data-qty]');
			if (button) {
				const wrapper = button.closest('.hojreh-qty');
				const input = $('input', wrapper);
				if (!wrapper || !input) return;
				const current = parseInt(input.value || '1', 10);
				const next = button.dataset.qty === 'inc' ? current + 1 : current - 1;
				input.value = String(Math.max(0, next));
				updateItem(wrapper.dataset.cartKey, Math.max(0, next));
			}
		});

		drawerBody.addEventListener('change', (event) => {
			const input = event.target.closest('.hojreh-qty input');
			if (!input) return;
			const wrapper = input.closest('.hojreh-qty');
			const quantity = Math.max(0, parseInt(input.value || '0', 10));
			input.value = String(quantity);
			updateItem(wrapper.dataset.cartKey, quantity);
		});
	}

	/* ---------------------------------------------------------------------
	 * Sticky add to cart (mobile product page)
	 * ------------------------------------------------------------------ */
	const stickyBar = $('[data-sticky-atc]');
	const stickyButton = $('[data-sticky-atc-button]');
	const productForm = $('form.cart');

	if (stickyBar && productForm && 'IntersectionObserver' in window) {
		const observer = new IntersectionObserver(
			(entries) => {
				entries.forEach((entry) => {
					const visible = !entry.isIntersecting && entry.boundingClientRect.top < 0;
					stickyBar.classList.toggle('is-visible', visible);
					stickyBar.setAttribute('aria-hidden', visible ? 'false' : 'true');
				});
			},
			{ threshold: 0 }
		);

		observer.observe(productForm);
	}

	stickyButton?.addEventListener('click', () => {
		if (!productForm) return;

		if (stickyButton.dataset.productType === 'simple') {
			const submit = productForm.querySelector('.single_add_to_cart_button');
			if (submit) {
				submit.click();
				return;
			}
		}

		productForm.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'center' });
		const focusTarget = productForm.querySelector('select, input.qty, .single_add_to_cart_button');
		window.setTimeout(() => focusTarget?.focus({ preventScroll: true }), reduceMotion ? 0 : 500);
	});

	/* ---------------------------------------------------------------------
	 * Small UI details
	 * ------------------------------------------------------------------ */
	const header = $('[data-header]');

	if (header) {
		const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 8);
		onScroll();
		window.addEventListener('scroll', onScroll, { passive: true });
	}

	// Close the mobile panels after navigating.
	$$('.hojreh-mobile-menu a, .hojreh-mobile-menu .btn').forEach((link) => {
		link.addEventListener('click', () => {
			closeMobileMenu();
			closeSearch();
		});
	});

	// Enable keyboard focus styles only when relevant.
	doc.addEventListener('keydown', (event) => {
		if (event.key === 'Tab') body.classList.add('hojreh-using-keyboard');
	});

	doc.addEventListener('mousedown', () => body.classList.remove('hojreh-using-keyboard'));
})();
