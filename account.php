<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Личный кабинет — Тёплый хлеб</title>
  <link rel="stylesheet" href="site.css">
  <link rel="stylesheet" href="reference.css">
  <link rel="stylesheet" href="pages.css">
  <link rel="stylesheet" href="polish.css">
  <link rel="stylesheet" href="account.css?v=2">
  <script src="site-nav.js" defer></script>
  <script src="account.js?v=2" defer></script>
</head>
<body>
<header class="header wrap">
  <a class="logo" href="/">Тёплый хлеб</a>
  <button class="nav-toggle" type="button" aria-label="Открыть меню" aria-controls="main-nav" aria-expanded="false"><span></span><span></span><span></span></button>
  <nav id="main-nav" aria-label="Навигация"><a href="/#catalog">Ассортимент</a><a href="about.php">О нас</a><a href="promotions.php">Акции</a><a href="news.php">Новости</a><a href="contact.php">Контакты</a></nav>
  <a class="header-order" href="/#catalog">К покупкам</a>
</header>
<main class="wrap page-main account-page">
  <div class="page-heading"><a href="/">Главная</a><span aria-hidden="true">/</span><h1>Личный кабинет</h1></div>
  <p class="page-intro">Аккаунт помогает сохранить корзину и увидеть свои заказы. Заказ без регистрации тоже доступен.</p>
  <div id="guest" class="account-grid">
    <section class="account-card"><h2>Войти</h2><form id="customer-login"><label>Телефон<input name="phone" type="tel" autocomplete="tel" required></label><label>Пароль<input name="password" type="password" autocomplete="current-password" required></label><button class="button" type="submit">Войти</button><p role="status"></p></form></section>
    <section class="account-card"><h2>Зарегистрироваться</h2><form id="customer-register"><label>Телефон<input name="phone" type="tel" autocomplete="tel" required></label><label>Пароль, от 10 символов<input name="password" type="password" minlength="10" autocomplete="new-password" required></label><label>Повтор пароля<input name="repeat" type="password" minlength="10" autocomplete="new-password" required></label><button class="button" type="submit">Создать аккаунт</button><p role="status"></p></form></section>
    <section class="account-card"><h2>Восстановить пароль</h2><p>Получите одноразовый код у сотрудника пекарни. Код действует 15 минут.</p><form id="customer-reset"><label>Телефон<input name="phone" type="tel" required></label><label>Одноразовый код<input name="code" maxlength="12" required></label><label>Новый пароль<input name="password" type="password" minlength="10" required></label><label>Повтор пароля<input name="repeat" type="password" minlength="10" required></label><button class="button" type="submit">Сменить пароль</button><p role="status"></p></form></section>
  </div>
  <section id="customer-dashboard" class="account-card" hidden><div class="admin-heading"><h2>Мои заказы</h2><button id="customer-logout" class="text-button" type="button">Выйти</button></div><p>Корзина сохраняется в аккаунте и доступна после повторного входа.</p><div id="customer-orders" role="status"></div></section>
</main>
<footer class="wrap"><span>© Тёплый хлеб</span><a href="/">Ассортимент</a><a href="contact.php">Контакты</a></footer>
</body>
</html>
