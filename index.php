<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Хлеб и выпечка с самовывозом. Закажите заранее и заберите к удобному времени.">
  <title>Тёплый хлеб — пекарня</title>
  <link rel="stylesheet" href="site.css">
  <link rel="stylesheet" href="reference.css">
  <link rel="stylesheet" href="pages.css">
  <link rel="stylesheet" href="polish.css">
  <link rel="manifest" href="manifest.webmanifest">
  <script src="site.js?v=2" defer></script>
  <script src="site-nav.js" defer></script>
</head>
<body>
  <header class="header wrap">
    <a class="logo" href="/">Тёплый хлеб</a>
    <button class="nav-toggle" type="button" aria-label="Открыть меню" aria-controls="main-nav" aria-expanded="false"><span></span><span></span><span></span></button>
    <nav id="main-nav" aria-label="Навигация">
      <a href="#catalog">Ассортимент</a>
      <a href="about.php">О нас</a>
      <a href="promotions.php">Акции</a>
      <a href="news.php">Новости</a>
      <a href="contact.php">Контакты</a>
      <a href="account.php">Личный кабинет</a>
    </nav>
    <button class="search-toggle" type="button" aria-label="Открыть поиск" aria-controls="site-search" aria-expanded="false"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m15.5 15.5 5 5"/></svg></button>
    <button id="cart-open" class="text-button" type="button">
      Корзина <span id="cart-count">0</span>
    </button>
  </header>
  <form id="site-search" class="site-search" action="/" method="get" hidden><label for="site-search-input">Поиск по ассортименту</label><input id="site-search-input" type="search" name="q" placeholder="Название изделия" required><button type="submit">Найти</button></form>

  <main>
    <section class="intro">
      <div class="wrap intro-layout">
        <div class="intro-main">
          <h1>Хлеб, булочки<br>и слойки</h1>
          <p class="lead">Выберите выпечку и удобный способ получения. Оплата при получении.</p>
          <a class="button hero-link" href="#catalog">Смотреть меню</a>
          <form class="hero-address" action="contact.php" method="get"><label for="hero-address-input">Найти адрес на карте</label><div><input id="hero-address-input" name="address" type="search" placeholder="Город, улица и дом" required><button type="submit" aria-label="Найти адрес">→</button></div></form>
        </div>
        <figure class="intro-visual">
          <img src="assets/hero.jpg" alt="Свежая выпечка крупным планом">
          <figcaption><span>К завтраку</span><a href="promotions.php#offer-1">Хлеб и круассан →</a></figcaption>
        </figure>
      </div>
    </section>


    <section id="catalog" class="catalog wrap">
      <div class="section-title">
        <h2>Ассортимент</h2>
        <p>Выберите категорию или найдите изделие по названию.</p>
      </div>
      <div class="toolbar">
        <div id="categories" class="categories" role="group" aria-label="Категории">
          <button class="selected" type="button" data-category="Все">Все</button>
          <button type="button" data-category="Хлеб">Хлеб</button>
          <button type="button" data-category="Сладкое">Сладкое</button>
          <button type="button" data-category="Сытное">Сытное</button>
        </div>
        <label class="search">Поиск <input id="search" type="search" placeholder="По названию"></label>
      </div>
      <p id="catalog-message" class="message" role="status">Загружаем ассортимент…</p>
      <div id="product-list" class="products"></div>
    </section>

    <section class="latest wrap" aria-labelledby="latest-title">
      <div class="latest-head"><div><h2 id="latest-title">Новости</h2><p>Что появилось в каталоге</p></div><div class="slider-controls"><button type="button" id="news-prev" aria-label="Предыдущая новость">←</button><button type="button" id="news-next" aria-label="Следующая новость">→</button></div></div>
      <div class="latest-slides" aria-live="polite">
        <article class="latest-slide" data-slide><img src="assets/rye.jpg" alt="Ржаной хлеб"><div><span>01 / 02</span><h3>Ржаной хлеб в каталоге</h3><p>Состав, аллергены и цена указаны рядом с изделием. Заказ можно оформить с самовывозом.</p><a class="inline-link" href="news.php#news-1">Читать новость →</a></div></article>
        <article class="latest-slide" data-slide hidden><img src="assets/cinnamon.jpg" alt="Булочки с корицей"><div><span>02 / 02</span><h3>Булочка с корицей</h3><p>Ещё один вариант в разделе сладкой выпечки. Состав и аллергены доступны в каталоге.</p><a class="inline-link" href="news.php#news-2">Читать новость →</a></div></article>
      </div>
    </section>

    <section id="about" class="about">
      <div class="wrap about-layout">
        <div>
          <h2>Получение заказа</h2>
        </div>
        <div class="about-copy">
          <p>Добавьте выпечку в корзину и выберите самовывоз или доставку.</p>
          <p>После оформления сохраните номер заказа. Оплатить можно при получении.</p>
        </div>
      </div>
    </section>

    <section id="contacts" class="contact wrap">
      <div>
        <h2>Статус заказа</h2>
        <p>Чтобы узнать, готов ли заказ, введите его номер и телефон.</p>
      </div>
      <form id="status-form" class="status-form">
        <h3>Проверить заказ</h3>
        <label>Номер заказа <input name="code" required maxlength="12" placeholder="12 символов"></label>
        <label>Телефон из заказа <input name="phone" required type="tel"></label>
        <button class="button outline" type="submit">Проверить</button>
        <p id="status-result" role="status"></p>
      </form>
    </section>

    <section class="mobile-site" aria-labelledby="mobile-site-title"><div class="wrap mobile-site-layout"><div><h2 id="mobile-site-title">На телефоне</h2><p>Откройте каталог по QR-коду и сохраните сайт, чтобы вернуться к заказу.</p><div class="mobile-site-actions"><button id="install-app" class="button" type="button" hidden>Установить</button><a class="button outline" href="/#catalog">Открыть каталог</a></div></div><div class="mobile-site-qr"><img src="assets/site-qr.svg" alt="QR-код со ссылкой на сайт Тёплый хлеб"><small>Сканируйте камерой телефона</small></div></div></section>
  </main>

  <footer class="wrap">
    <span>© Тёплый хлеб</span>
    <span>Оплата при получении</span>
    <a href="about.php">О нас</a><a href="promotions.php">Акции</a><a href="news.php">Новости</a><a href="contact.php">Контакты</a>
    <a href="admin.php">Для сотрудников</a>
    <a href="account.php">Кабинет покупателя</a>
  </footer>

  <div id="cart-overlay" class="overlay" hidden></div>
  <div id="cart-drawer" class="drawer" role="dialog" aria-modal="true" aria-labelledby="cart-title" hidden>
    <div class="drawer-heading">
      <div><p class="kicker">Ваш выбор</p><h2 id="cart-title">Корзина</h2></div>
      <button id="cart-close" class="close" type="button" aria-label="Закрыть">×</button>
    </div>
    <div id="cart-items" class="cart-items"></div>
    <div class="cart-footer">
      <div class="sum"><span>Итого</span><strong id="cart-total">0 ₽</strong></div>
      <button id="checkout-open" class="button full" type="button">Оформить заказ</button>
    </div>
  </div>

  <div id="checkout-overlay" class="overlay" hidden></div>
  <section id="checkout-dialog" class="modal" role="dialog" aria-modal="true" aria-labelledby="checkout-title" hidden>
    <div class="drawer-heading">
      <div><p id="fulfillment-kicker" class="kicker">Самовывоз</p><h2 id="checkout-title">Ваш заказ</h2></div>
      <button id="checkout-close" class="close" type="button" aria-label="Закрыть">×</button>
    </div>
    <form id="checkout-form">
      <label>Имя <input name="name" required minlength="2" maxlength="80" autocomplete="name"></label>
      <label>Телефон <input name="phone" required type="tel" pattern="[+0-9() -]{10,20}" autocomplete="tel" placeholder="+7 999 123-45-67"></label>
      <label>Получение <select name="fulfillment"><option value="pickup">Самовывоз</option><option value="delivery">Доставка</option></select></label>
      <label id="delivery-address-field" hidden>Адрес доставки <input name="address" maxlength="255"></label>
      <label id="pickup-time-label">Время получения <input name="pickup" required type="datetime-local"></label>
      <label class="checkbox"><input type="checkbox" required><span>Согласен на обработку указанных данных для выполнения заказа</span></label>
      <p id="order-error" class="error" role="alert" hidden></p>
      <button class="button full" type="submit">Подтвердить заказ</button>
      <p class="hint">Оплата при получении.</p>
    </form>
    <div id="order-success" hidden>
      <h3>Заказ принят</h3>
      <p id="order-message"></p>
      <button id="success-close" class="button full" type="button">Готово</button>
    </div>
  </section>
</body>
</html>
