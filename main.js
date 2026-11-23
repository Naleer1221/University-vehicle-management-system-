'use strict';
// Keep the header compact after the user scrolls. Also works if page loads mid-scroll.
const siteHeader = document.querySelector('.site-header');
if (siteHeader) {
  const updateHeader = () => siteHeader.classList.toggle('is-scrolled', window.scrollY > 24);
  window.addEventListener('scroll', updateHeader, { passive: true });
  updateHeader();
}
const menuButton = document.querySelector('.menu-toggle');
const menu = document.querySelector('.site-nav');
if (menuButton && menu) {
  menuButton.addEventListener('click', () => {
    const opened = menu.classList.toggle('open');
    menuButton.setAttribute('aria-expanded', String(opened));
  });
}
document.querySelectorAll('[data-confirm]').forEach((form) => {
  form.addEventListener('submit', (event) => {
    if (!window.confirm(form.dataset.confirm || 'Are you sure?')) event.preventDefault();
  });
});
const flash = document.querySelector('[data-flash]');
if (flash) window.setTimeout(() => flash.classList.add('fade-out'), 6000);
