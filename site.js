const $ = id => document.getElementById(id);
const money = value => `${Number(value).toLocaleString('ru-RU')} ₽`;
const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, char => ({
  '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
}[char]));

const samplePhotos = {
  1: 'assets/bread.jpg',
  2: 'assets/croissant.jpg',
  3: 'assets/cinnamon.jpg',
  4: 'assets/rye.jpg',
  5: 'assets/cabbage-pie.jpg',
  6: 'assets/cheese-pastry.jpg'
};

let csrf = '';
let products = [];
let category = 'Все';
let cart = {};
let customer = false;
try {
  cart = JSON.parse(localStorage.getItem('teply-hleb-cart-v2')) || {};
} catch {
  cart = {};
}

async function api(route, options = {}) {
  const response = await fetch(`/server/api.php?route=${route}`, {
    credentials: 'same-origin', ...options
  });
  const data = await response.json();
  if (!response.ok) throw new Error(data.error || 'Ошибка запроса');
  return data;
}

function saveCart() {
  localStorage.setItem('teply-hleb-cart-v2', JSON.stringify(cart));
  renderCart();
  if (customer && csrf) {
    const items = Object.entries(cart).filter(([,quantity]) => Number(quantity) > 0).map(([id,quantity]) => ({id:Number(id),quantity:Math.min(20,Number(quantity))}));
    api('customer-cart',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':csrf},body:JSON.stringify({items})}).catch(console.error);
  }
}

function renderProducts() {
  const term = $('search').value.trim().toLocaleLowerCase('ru');
  const matches = products.filter(product =>
    Number(product.available) === 1 &&
    (category === 'Все' || product.category === category) &&
    product.name.toLocaleLowerCase('ru').includes(term)
  );

  $('catalog-message').hidden = matches.length > 0;
  $('catalog-message').textContent = 'По вашему запросу ничего не найдено.';
  $('product-list').innerHTML = matches.map(product => {
    const photo = product.image_path || samplePhotos[product.id];
    return `<article class="product${photo ? '' : ' product--text'}">
      ${photo ? `<img class="product-image" src="${escapeHtml(photo)}" alt="${escapeHtml(product.name)}" loading="lazy">` : ''}
      <div class="product-copy">
        <span class="product-category">${escapeHtml(product.category)}</span>
        <h3>${escapeHtml(product.name)}</h3>
        <p>${escapeHtml(product.description)}</p>
        <small>Вес: ${Number(product.weight_g)} г · Приготовление: ${Number(product.prep_minutes)} мин<br>Состав: ${escapeHtml(product.ingredients)}<br>Аллергены: ${escapeHtml(product.allergens)}</small>
      </div>
      <div class="product-bottom">
        <strong>${money(product.price)}</strong>
        <button type="button" data-add="${Number(product.id)}">В корзину</button>
      </div>
    </article>`;
  }).join('');
}

function renderCart() {
  let total = 0;
  let count = 0;
  const rows = [];

  for (const product of products) {
    const quantity = Math.min(20, Math.max(0, Number(cart[product.id]) || 0));
    if (!Number(product.available) || !quantity) continue;
    total += quantity * Number(product.price);
    count += quantity;
    rows.push(`<div class="cart-row">
      <div>
        <strong>${escapeHtml(product.name)}</strong>
        <small>${money(product.price)} за шт.</small>
        <div class="qty">
          <button type="button" data-change="${Number(product.id)}" data-delta="-1" aria-label="Уменьшить количество">−</button>
          <span>${quantity}</span>
          <button type="button" data-change="${Number(product.id)}" data-delta="1" aria-label="Увеличить количество">+</button>
          <button type="button" class="remove" data-remove="${Number(product.id)}">Удалить</button>
        </div>
      </div>
      <strong>${money(quantity * product.price)}</strong>
    </div>`);
  }

  $('cart-items').innerHTML = rows.join('') || '<p>Корзина пока пуста.</p>';
  $('cart-count').textContent = count;
  $('cart-total').textContent = money(total);
  $('checkout-open').disabled = count === 0;
}

let focusBeforeDialog = null;
function openDialog(type) {
  focusBeforeDialog = document.activeElement;
  $(type + '-overlay').hidden = false;
  $(type === 'cart' ? 'cart-drawer' : 'checkout-dialog').hidden = false;
  document.body.style.overflow = 'hidden';
  $(type + '-close').focus();
}

function closeDialog(type) {
  $(type + '-overlay').hidden = true;
  $(type === 'cart' ? 'cart-drawer' : 'checkout-dialog').hidden = true;
  document.body.style.overflow = '';
  const target = focusBeforeDialog && document.contains(focusBeforeDialog) ? focusBeforeDialog : $('cart-open');
  target.focus();
}

document.addEventListener('click', event => {
  const addButton = event.target.closest('[data-add]');
  if (addButton) {
    const id = addButton.dataset.add;
    cart[id] = Math.min(20, (Number(cart[id]) || 0) + 1);
    saveCart();
    addButton.textContent = 'Добавлено';
    setTimeout(() => { addButton.textContent = 'В корзину'; }, 900);
  }

  const changeButton = event.target.closest('[data-change]');
  if (changeButton) {
    const id = changeButton.dataset.change;
    const quantity = (Number(cart[id]) || 0) + Number(changeButton.dataset.delta);
    if (quantity <= 0) delete cart[id];
    else cart[id] = Math.min(20, quantity);
    saveCart();
  }

  const removeButton = event.target.closest('[data-remove]');
  if (removeButton) {
    delete cart[removeButton.dataset.remove];
    saveCart();
  }
});

document.querySelectorAll('[data-category]').forEach(button => {
  button.addEventListener('click', () => {
    category = button.dataset.category;
    document.querySelectorAll('[data-category]').forEach(item => {
      item.classList.toggle('selected', item === button);
    });
    renderProducts();
  });
});

$('search').addEventListener('input', renderProducts);
$('cart-open').addEventListener('click', () => openDialog('cart'));
for (const type of ['cart', 'checkout']) {
  $(type + '-close').addEventListener('click', () => closeDialog(type));
  $(type + '-overlay').addEventListener('click', () => closeDialog(type));
}
document.addEventListener('keydown', event => {
  if (event.key === 'Tab') {
    const dialog = !$('checkout-dialog').hidden ? $('checkout-dialog') : (!$('cart-drawer').hidden ? $('cart-drawer') : null);
    if (dialog) {
      const focusable = [...dialog.querySelectorAll('button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), a[href]')]
        .filter(element => element.getClientRects().length);
      if (focusable.length) {
        const first = focusable[0], last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
      }
    }
  }
  if (event.key !== 'Escape') return;
  if (!$('checkout-dialog').hidden) closeDialog('checkout');
  else if (!$('cart-drawer').hidden) closeDialog('cart');
});

$('checkout-open').addEventListener('click', () => {
  if ($('checkout-open').disabled) return;
  closeDialog('cart');
  $('checkout-form').hidden = false;
  $('order-success').hidden = true;

  const pickupInput = $('checkout-form').elements.pickup;
  const localInputValue = date => {
    date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
    return date.toISOString().slice(0, 16);
  };
  pickupInput.min = localInputValue(new Date(Date.now() + 30 * 60000));
  pickupInput.max = localInputValue(new Date(Date.now() + 14 * 86400000));
  openDialog('checkout');
  $('checkout-form').elements.name.focus();
});
$('checkout-form').elements.fulfillment.addEventListener('change', event => {
  const delivery = event.target.value === 'delivery';
  $('delivery-address-field').hidden = !delivery;
  $('checkout-form').elements.address.required = delivery;
  $('fulfillment-kicker').textContent = delivery ? 'Доставка' : 'Самовывоз';
  $('pickup-time-label').firstChild.textContent = delivery ? 'Время доставки ' : 'Время самовывоза ';
});
$('success-close').addEventListener('click', () => closeDialog('checkout'));

$('checkout-form').addEventListener('submit', async event => {
  event.preventDefault();
  const form = event.currentTarget;
  const button = form.querySelector('[type=submit]');
  $('order-error').hidden = true;
  button.disabled = true;

  try {
    const pickup = new Date(form.elements.pickup.value);
    const items = products.filter(product => Number(cart[product.id]) > 0).map(product => ({
      id: Number(product.id), quantity: Number(cart[product.id])
    }));
    const result = await api('order', {
      method: 'POST',
      headers: {'Content-Type': 'application/json', 'X-CSRF-Token': csrf},
      body: JSON.stringify({
        name: form.elements.name.value.trim(),
        phone: form.elements.phone.value.trim(),
        pickup: pickup.toISOString().replace('.000Z', '+00:00'),
        fulfillment: form.elements.fulfillment.value,
        address: form.elements.address.value.trim(),
        items
      })
    });
    cart = {};
    saveCart();
    form.hidden = true;
    $('order-message').textContent = `Номер ${result.code}. Сумма ${money(result.total)}. Сохраните номер для проверки статуса заказа.`;
    $('order-success').hidden = false;
  } catch (error) {
    $('order-error').textContent = error.message;
    $('order-error').hidden = false;
  } finally {
    button.disabled = false;
  }
});

$('status-form').addEventListener('submit', async event => {
  event.preventDefault();
  const form = event.currentTarget;
  const output = $('status-result');
  output.textContent = 'Проверяем…';
  try {
    const result = await api('status', {
      method: 'POST',
      headers: {'Content-Type': 'application/json', 'X-CSRF-Token': csrf},
      body: JSON.stringify({code: form.elements.code.value, phone: form.elements.phone.value})
    });
    output.textContent = `Статус: ${result.order.status}. Сумма: ${money(result.order.total)}.`;
  } catch (error) {
    output.textContent = error.message;
  }
});

async function start() {
  try {
    const query = new URLSearchParams(location.search).get('q');
    if (query) $('search').value = query.slice(0, 100);
    const [session, catalog] = await Promise.all([api('session'), api('products')]);
    csrf = session.csrf;
    customer = !!session.customer;
    products = catalog.products;
    if (customer) {
      const saved = await api('customer-cart');
      const stored = Object.fromEntries(saved.items.map(item => [item.id,Number(item.quantity)]));
      cart = {...cart,...stored};
      saveCart();
    }
    renderProducts();
    renderCart();
    const bundle = new URLSearchParams(location.search).get('add');
    if (bundle === '1,2' || bundle === '3') {
      const ids = bundle.split(',');
      if (ids.every(id => products.some(product => String(product.id) === id && Number(product.available)))) {
        ids.forEach(id => { cart[id] = Math.min(20, (Number(cart[id]) || 0) + 1); });
        saveCart();
        const cleanUrl = new URL(location.href);
        cleanUrl.searchParams.delete('add');
        try { history.replaceState(null, '', cleanUrl); } catch { /* Локальный просмотр может не разрешить смену file: URL. */ }
        openDialog('cart');
      }
    }
    if (query) $('catalog').scrollIntoView();
  } catch (error) {
    $('catalog-message').hidden = false;
    $('catalog-message').textContent = 'Каталог пока недоступен. Проверьте подключение к серверу.';
    console.error(error);
  }
}
start();
