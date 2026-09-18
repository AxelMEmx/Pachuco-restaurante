document.addEventListener('DOMContentLoaded', () => {
    const current = window.location.pathname.split('/').pop() || 'index.php';

    document.querySelectorAll('.main-nav a').forEach((link) => {
        const href = link.getAttribute('href') || '';
        if (href.endsWith(current)) {
            link.classList.add('is-active');
        }
    });

    const revealItems = document.querySelectorAll('.card, .form, .table-wrap, .hero-panel, .cart-panel');
    revealItems.forEach((item) => item.classList.add('reveal'));

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });

    revealItems.forEach((item) => observer.observe(item));

    const filters = document.querySelectorAll('[data-filter]');
    const menuCards = document.querySelectorAll('[data-category]');

    filters.forEach((button) => {
        button.addEventListener('click', () => {
            const filter = button.dataset.filter;

            filters.forEach((item) => item.classList.remove('is-active'));
            button.classList.add('is-active');

            menuCards.forEach((card) => {
                const show = filter === 'todos' || card.dataset.category === filter;
                card.style.display = show ? '' : 'none';
            });
        });
    });

    const cartRoot = document.querySelector('[data-cart-root]');
    if (!cartRoot) {
        return;
    }

    const storageKey = 'pachuco_cart';
    const isAuthenticated = cartRoot.dataset.authenticated === '1';
    const loginUrl = cartRoot.dataset.loginUrl || 'login.php';
    const deliveryFee = 35;
    const cartItemsEl = document.querySelector('[data-cart-items]');
    const cartEmptyEl = document.querySelector('[data-cart-empty]');
    const cartJsonEl = document.querySelector('[data-cart-json]');
    const cartCountEl = document.querySelector('[data-cart-count]');
    const cartSubtotalEl = document.querySelector('[data-cart-subtotal]');
    const cartDeliveryEl = document.querySelector('[data-cart-delivery]');
    const cartTotalEl = document.querySelector('[data-cart-total]');
    const deliveryRowEl = document.querySelector('[data-delivery-row]');
    const submitEl = document.querySelector('[data-cart-submit]');
    const formEl = document.querySelector('[data-cart-form]');
    const orderTypeEl = document.querySelector('[data-order-type]');
    const notesEl = document.querySelector('[data-order-notes]');
    const formatter = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });
    let cart = loadCart();

    function loadCart() {
        try {
            const saved = JSON.parse(localStorage.getItem(storageKey) || '[]');
            return Array.isArray(saved) ? saved : [];
        } catch (error) {
            return [];
        }
    }

    function saveCart() {
        localStorage.setItem(storageKey, JSON.stringify(cart));
    }

    function findItem(id) {
        return cart.find((item) => item.id === String(id));
    }

    function setCardQty(id, quantity) {
        const qtyEl = document.querySelector(`[data-menu-qty="${id}"]`);
        if (qtyEl) {
            qtyEl.textContent = String(quantity);
        }
    }

    function syncCardQuantities() {
        document.querySelectorAll('[data-menu-qty]').forEach((qtyEl) => {
            qtyEl.textContent = '0';
        });
        cart.forEach((item) => setCardQty(item.id, item.quantity));
    }

    function updateQuantity(id, quantity) {
        const existing = findItem(id);
        const nextQuantity = Math.max(0, Math.min(20, quantity));

        if (!existing && nextQuantity > 0) {
            const button = document.querySelector(`[data-cart-add][data-id="${id}"]`);
            if (!button) return;
            cart.push({
                id: String(id),
                name: button.dataset.name,
                price: Number(button.dataset.price),
                quantity: nextQuantity,
            });
        } else if (existing && nextQuantity === 0) {
            cart = cart.filter((item) => item.id !== String(id));
        } else if (existing) {
            existing.quantity = nextQuantity;
        }

        saveCart();
        renderCart();
    }

    function addItem(button) {
        const id = button.dataset.id;
        const existing = findItem(id);
        updateQuantity(id, existing ? existing.quantity + 1 : 1);
    }

    function totals() {
        const count = cart.reduce((sum, item) => sum + item.quantity, 0);
        const subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
        const fee = orderTypeEl && orderTypeEl.value === 'entrega' && count > 0 ? deliveryFee : 0;
        return { count, subtotal, fee, total: subtotal + fee };
    }

    function renderCart() {
        syncCardQuantities();
        const currentTotals = totals();
        cartItemsEl.innerHTML = '';

        cart.forEach((item) => {
            const row = document.createElement('div');
            row.className = 'cart-item';
            row.innerHTML = `
                <div>
                    <strong>${item.name}</strong>
                    <span>${formatter.format(item.price)} c/u</span>
                </div>
                <div class="cart-item-controls">
                    <button type="button" data-cart-step="-1" data-item-id="${item.id}">-</button>
                    <span>${item.quantity}</span>
                    <button type="button" data-cart-step="1" data-item-id="${item.id}">+</button>
                </div>
                <strong>${formatter.format(item.price * item.quantity)}</strong>
            `;
            cartItemsEl.appendChild(row);
        });

        cartEmptyEl.hidden = cart.length > 0;
        cartCountEl.textContent = String(currentTotals.count);
        cartSubtotalEl.textContent = formatter.format(currentTotals.subtotal);
        cartDeliveryEl.textContent = formatter.format(deliveryFee);
        cartTotalEl.textContent = formatter.format(currentTotals.total);
        deliveryRowEl.hidden = currentTotals.fee === 0;
        cartJsonEl.value = JSON.stringify(cart.map(({ id, quantity }) => ({ id, quantity })));
        submitEl.disabled = currentTotals.count === 0;
    }

    document.querySelectorAll('[data-cart-add]').forEach((button) => {
        button.addEventListener('click', () => addItem(button));
    });

    document.querySelectorAll('[data-menu-step]').forEach((button) => {
        button.addEventListener('click', () => {
            const id = button.dataset.itemId;
            const existing = findItem(id);
            const currentQty = existing ? existing.quantity : 0;
            updateQuantity(id, currentQty + Number(button.dataset.menuStep));
        });
    });

    cartItemsEl.addEventListener('click', (event) => {
        const button = event.target.closest('[data-cart-step]');
        if (!button) return;
        const existing = findItem(button.dataset.itemId);
        if (!existing) return;
        updateQuantity(existing.id, existing.quantity + Number(button.dataset.cartStep));
    });

    document.querySelector('[data-cart-clear]').addEventListener('click', () => {
        cart = [];
        saveCart();
        renderCart();
    });

    orderTypeEl.addEventListener('change', renderCart);

    formEl.addEventListener('submit', (event) => {
        if (totals().count === 0) {
            event.preventDefault();
            return;
        }

        if (!isAuthenticated) {
            event.preventDefault();
            saveCart();
            window.location.href = loginUrl;
            return;
        }

        cartJsonEl.value = JSON.stringify(cart.map(({ id, quantity }) => ({ id, quantity })));
        localStorage.removeItem(storageKey);
    });

    if (notesEl) {
        notesEl.addEventListener('input', () => {
            notesEl.style.height = 'auto';
            notesEl.style.height = `${notesEl.scrollHeight}px`;
        });
    }

    renderCart();
});
