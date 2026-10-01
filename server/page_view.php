<?php
declare(strict_types=1);

$titles = [
    'about' => 'О нас',
    'promotions' => 'Акции',
    'news' => 'Новости',
    'contact' => 'Контакты',
];
if (!isset($page, $titles[$page])) {
    http_response_code(404);
    exit('Страница не найдена');
}
$title = $titles[$page];
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?> — Тёплый хлеб</title>
  <link rel="stylesheet" href="site.css">
  <link rel="stylesheet" href="reference.css">
  <link rel="stylesheet" href="pages.css">
  <link rel="stylesheet" href="polish.css">
  <link rel="manifest" href="manifest.webmanifest">
  <script src="site-nav.js" defer></script>
</head>
<body>
<header class="header wrap">
  <a class="logo" href="/">Тёплый хлеб</a>
  <button class="nav-toggle" type="button" aria-label="Открыть меню" aria-controls="main-nav" aria-expanded="false"><span></span><span></span><span></span></button>
  <nav id="main-nav" aria-label="Навигация">
    <a href="/#catalog">Ассортимент</a>
    <a href="about.php" <?= $page === 'about' ? 'aria-current="page"' : '' ?>>О нас</a>
    <a href="promotions.php" <?= $page === 'promotions' ? 'aria-current="page"' : '' ?>>Акции</a>
    <a href="news.php" <?= $page === 'news' ? 'aria-current="page"' : '' ?>>Новости</a>
    <a href="contact.php" <?= $page === 'contact' ? 'aria-current="page"' : '' ?>>Контакты</a>
    <a href="account.php">Личный кабинет</a>
  </nav>
  <button class="search-toggle" type="button" aria-label="Открыть поиск" aria-controls="site-search" aria-expanded="false"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m15.5 15.5 5 5"/></svg></button>
  <a class="header-order" href="/#catalog">Заказать</a>
</header>
<form id="site-search" class="site-search" action="/" method="get" hidden>
  <label for="site-search-input">Поиск по ассортименту</label>
  <input id="site-search-input" type="search" name="q" placeholder="Название изделия" required>
  <button type="submit">Найти</button>
</form>
<main class="page-main wrap">
  <div class="page-heading">
    <a href="/">Главная</a><span aria-hidden="true">/</span>
    <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
  </div>

<?php if ($page === 'about'): ?>
  <section class="about-story">
    <div>
      <p class="page-intro">Печём хлеб и выпечку на каждый день.</p>
      <p>В меню — пшеничный и ржаной хлеб, круассаны, булочки и сытная выпечка. Выберите то, что хотите забрать, и укажите время самовывоза.</p>
      <p>Состав и аллергены указаны у каждого изделия в каталоге. После оформления заказу присваивается номер, по которому можно проверить его статус.</p>
      <a class="button" href="/#catalog">Посмотреть ассортимент</a>
    </div>
    <img src="assets/bread.jpg" alt="Буханка хлеба на деревянной доске">
  </section>
  <section class="faq-section">
    <h2>Частые вопросы</h2>
    <div class="faq-list">
      <details><summary>Как оформить заказ?</summary><p>Выберите изделия в каталоге, откройте корзину и укажите имя, телефон и время самовывоза.</p></details>
      <details><summary>Как оплатить?</summary><p>Оплата указана при получении заказа.</p></details>
      <details><summary>Как проверить статус?</summary><p>На главной странице введите номер заказа и телефон, указанный при оформлении.</p></details>
      <details><summary>Можно ли заказать доставку?</summary><p>Да. При оформлении выберите доставку, укажите адрес и удобное время получения.</p></details>
    </div>
  </section>
<?php elseif ($page === 'promotions'): ?>
  <p class="page-intro">Подборки из нашего меню, которые удобно взять вместе.</p>
  <article class="feature-story" id="offer-1">
    <img src="assets/croissant.jpg" alt="Круассан на тарелке">
    <div>
      <p class="story-type">К завтраку</p>
      <h2>Хлеб и круассан</h2>
      <p>Хлеб на закваске с плотной корочкой и круассан из слоёного теста со сливочным маслом. Добавьте оба изделия одним нажатием и выберите удобное время самовывоза.</p>
      <p>Состав и аллергены каждого изделия указаны в каталоге. Стоимость набора складывается из цен товаров в корзине.</p>
      <a class="inline-link" href="/?add=1,2#catalog">Добавить в корзину →</a>
    </div>
  </article>
  <section class="story-list" aria-labelledby="other-offers">
    <h2 id="other-offers">Другие предложения</h2>
    <article class="story-row" id="offer-2"><img src="assets/cinnamon.jpg" alt="Булочки с корицей"><div><p class="story-type">К чаю</p><h3>Булочка с корицей</h3><p>Мягкая булочка с корицей и глазурью. Её можно добавить к любому заказу с самовывозом.</p><a class="inline-link" href="/?add=3#catalog">Добавить в корзину →</a></div></article>
  </section>
<?php elseif ($page === 'news'): ?>
  <p class="page-intro">Новое в меню и полезное о заказах.</p>
  <article class="feature-story" id="news-1">
    <img src="assets/rye.jpg" alt="Ржаной хлеб">
    <div>
      <p class="story-type">Ассортимент</p>
      <h2>Ржаной хлеб в каталоге</h2>
      <p>В каталоге есть ржаной хлеб из ржаной и пшеничной муки. На странице товара указаны состав, аллергены и цена. Его можно добавить в корзину вместе с другими изделиями.</p>
      <p>После оформления сохраните номер заказа: он понадобится для проверки статуса на главной странице. Оплата производится при получении.</p>
      <button class="share-button" type="button" data-share="news-1" data-title="Ржаной хлеб в каталоге">Поделиться</button><span class="share-result" role="status"></span>
    </div>
  </article>
  <section class="story-list" aria-labelledby="other-news">
    <h2 id="other-news">Другие новости</h2>
    <article class="story-row" id="news-2"><img src="assets/cinnamon.jpg" alt="Булочки с корицей"><div><p class="story-type">Ассортимент</p><h3>Булочка с корицей</h3><p>В разделе сладкой выпечки представлена булочка с корицей и глазурью. Перед заказом можно посмотреть состав и указанные аллергены.</p><button class="share-button" type="button" data-share="news-2" data-title="Булочка с корицей">Поделиться</button><span class="share-result" role="status"></span></div></article>
  </section>
<?php else: ?>
  <p class="page-intro">Напишите нам через форму или выберите филиал на карте.</p>
  <div class="contact-page-grid">
    <section>
      <h2>Самовывоз и доставка</h2>
      <p>При оформлении заказа выберите самовывоз или доставку к указанному времени. Для доставки укажите адрес. Оплата при получении.</p>
      <label for="branch-select">Выберите филиал</label>
      <select id="branch-select">
        <option value="center">Центр — Москва, Тверская улица, 1</option>
        <option value="arbat">Арбат — Москва, улица Арбат, 10</option>
      </select>
      <p id="branch-address" class="branch-address" role="status">Москва, Тверская улица, 1</p>
      <iframe id="branch-map" class="branch-map" title="Карта выбранного филиала" loading="lazy" referrerpolicy="no-referrer" src="https://www.openstreetmap.org/export/embed.html?bbox=37.608%2C55.749%2C37.619%2C55.758&amp;layer=mapnik&amp;marker=55.7534%2C37.6134"></iframe>
      <a id="map-link" class="inline-link" href="https://www.openstreetmap.org/?mlat=55.7534&amp;mlon=37.6134#map=16/55.7534/37.6134" target="_blank" rel="noopener noreferrer">Открыть большую карту →</a>
      <form id="address-form" class="address-form"><label for="address-input">Найти свой адрес на карте</label><div><input id="address-input" type="search" required placeholder="Город, улица и дом"><button type="submit">Найти</button></div><p class="form-help">Поиск откроется на OpenStreetMap в новой вкладке.</p></form>
    </section>
    <section>
      <h2>Обратная связь</h2>
      <p>Сообщение появится в панели сотрудника. Укажите номер телефона, чтобы мы могли ответить.</p>
      <form id="feedback-form" class="feedback-form">
        <label>Имя<input name="name" required minlength="2" maxlength="80" autocomplete="name"></label>
        <label>Телефон<input name="phone" required type="tel" pattern="[+0-9() -]{10,20}" autocomplete="tel"></label>
        <label>Сообщение<textarea name="message" required minlength="10" maxlength="2000"></textarea></label>
        <label class="checkbox"><input name="consent" type="checkbox" required><span>Согласен на обработку имени и телефона для ответа на сообщение</span></label>
        <button class="button" type="submit">Отправить</button>
        <p id="feedback-result" role="status"></p>
      </form>
    </section>
  </div>
<?php endif; ?>
</main>
<footer class="wrap">
  <span>© Тёплый хлеб</span>
  <a href="/">Ассортимент</a><a href="about.php">О нас</a><a href="promotions.php">Акции</a><a href="news.php">Новости</a><a href="contact.php">Контакты</a>
  <a href="account.php">Личный кабинет</a>
</footer>
</body>
</html>
