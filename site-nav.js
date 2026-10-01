const navButton = document.querySelector('.nav-toggle');
const nav = document.getElementById('main-nav');
const searchButton = document.querySelector('.search-toggle');
const searchPanel = document.getElementById('site-search');

navButton?.addEventListener('click', () => {
  const open = navButton.getAttribute('aria-expanded') !== 'true';
  navButton.setAttribute('aria-expanded', String(open));
  navButton.setAttribute('aria-label', open ? 'Закрыть меню' : 'Открыть меню');
  nav.classList.toggle('is-open', open);
});
searchButton?.addEventListener('click', () => {
  const open = searchButton.getAttribute('aria-expanded') !== 'true';
  searchButton.setAttribute('aria-expanded', String(open));
  searchPanel.hidden = !open;
  if (open) document.getElementById('site-search-input').focus();
});
document.addEventListener('click', event => {
  if (nav?.classList.contains('is-open') && !nav.contains(event.target) && !navButton.contains(event.target)) {
    nav.classList.remove('is-open');
    navButton.setAttribute('aria-expanded', 'false');
    navButton.setAttribute('aria-label', 'Открыть меню');
  }
  if (searchPanel && !searchPanel.hidden && !searchPanel.contains(event.target) && !searchButton.contains(event.target)) {
    searchPanel.hidden = true;
    searchButton.setAttribute('aria-expanded', 'false');
  }
});
document.addEventListener('keydown', event => {
  if (event.key !== 'Escape') return;
  if (searchPanel && !searchPanel.hidden) {
    searchPanel.hidden = true;
    searchButton.setAttribute('aria-expanded', 'false');
    searchButton.focus();
  }
  if (nav?.classList.contains('is-open')) {
    nav.classList.remove('is-open');
    navButton.setAttribute('aria-expanded', 'false');
    navButton.setAttribute('aria-label', 'Открыть меню');
  }
});

document.querySelectorAll('[data-share]').forEach(button => {
  button.addEventListener('click', async () => {
    const url = new URL(location.href);
    url.hash = button.dataset.share;
    const result = button.nextElementSibling;
    try {
      if (navigator.share) await navigator.share({title: button.dataset.title, url: url.href});
      else if (navigator.clipboard) {
        await navigator.clipboard.writeText(url.href);
        result.textContent = 'Ссылка скопирована';
      } else result.textContent = url.href;
    } catch (error) {
      if (error.name !== 'AbortError') result.textContent = url.href;
    }
  });
});

const branches = {
  center: {address: 'Москва, Тверская улица, 1', lat: 55.7534, lon: 37.6134},
  arbat: {address: 'Москва, улица Арбат, 10', lat: 55.75143, lon: 37.59656}
};
const branchSelect = document.getElementById('branch-select');
branchSelect?.addEventListener('change', () => {
  const branch = branches[branchSelect.value];
  document.getElementById('branch-address').textContent = branch.address;
  const {lat, lon} = branch;
  const bbox = [lon - .006, lat - .004, lon + .006, lat + .004].join(',');
  document.getElementById('branch-map').src =
    'https://www.openstreetmap.org/export/embed.html?' +
    new URLSearchParams({bbox, layer: 'mapnik', marker: lat + ',' + lon});
  document.getElementById('map-link').href =
    'https://www.openstreetmap.org/?' +
    new URLSearchParams({mlat: lat, mlon: lon}) + '#map=16/' + lat + '/' + lon;
});
document.getElementById('address-form')?.addEventListener('submit', event => {
  event.preventDefault();
  const address = document.getElementById('address-input').value.trim();
  if (address) window.open('https://www.openstreetmap.org/search?query=' + encodeURIComponent(address), '_blank', 'noopener,noreferrer');
});

document.getElementById('feedback-form')?.addEventListener('submit', async event => {
  event.preventDefault();
  const form = event.currentTarget;
  const output = document.getElementById('feedback-result');
  const button = form.querySelector('[type=submit]');
  button.disabled = true;
  output.textContent = 'Отправляем…';
  try {
    const sessionResponse = await fetch('/server/api.php?route=session', {credentials:'same-origin'});
    if (!sessionResponse.ok) throw new Error('Не удалось связаться с сервером');
    const session = await sessionResponse.json();
    const response = await fetch('/server/api.php?route=feedback', {
      method: 'POST', credentials: 'same-origin',
      headers: {'Content-Type':'application/json', 'X-CSRF-Token':session.csrf},
      body: JSON.stringify({
        name: form.elements.name.value.trim(),
        phone: form.elements.phone.value.trim(),
        message: form.elements.message.value.trim(),
        consent: form.elements.consent.checked
      })
    });
    const result = await response.json();
    if (!response.ok) throw new Error(result.error || 'Сообщение не отправлено');
    output.textContent = 'Сообщение отправлено.';
    form.reset();
  } catch (error) {
    output.textContent = error.message;
  } finally {
    button.disabled = false;
  }
});

const slides = [...document.querySelectorAll('[data-slide]')];
let currentSlide = 0;
function showSlide(index) {
  currentSlide = (index + slides.length) % slides.length;
  slides.forEach((slide, i) => { slide.hidden = i !== currentSlide; });
}
document.getElementById('news-prev')?.addEventListener('click', () => showSlide(currentSlide - 1));
document.getElementById('news-next')?.addEventListener('click', () => showSlide(currentSlide + 1));

const addressFromHome = new URLSearchParams(location.search).get('address');
if (addressFromHome && document.getElementById('address-input')) {
  document.getElementById('address-input').value = addressFromHome.slice(0, 180);
  document.getElementById('address-input').focus();
}

let installPrompt;
window.addEventListener('beforeinstallprompt', event => {
  event.preventDefault();
  installPrompt = event;
  const button = document.getElementById('install-app');
  if (button) button.hidden = false;
});
document.getElementById('install-app')?.addEventListener('click', async () => {
  if (!installPrompt) return;
  await installPrompt.prompt();
  installPrompt = null;
  document.getElementById('install-app').hidden = true;
});

if ('IntersectionObserver' in window && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
  const sections = document.querySelectorAll('.catalog, .latest, .about, .contact, .mobile-site, .feature-story, .story-row, .faq-section');
  sections.forEach(section => section.classList.add('reveal'));
  document.documentElement.classList.add('motion-ready');
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      entry.target.classList.add('is-visible');
      observer.unobserve(entry.target);
    });
  }, {threshold: .08});
  sections.forEach(section => observer.observe(section));
}
