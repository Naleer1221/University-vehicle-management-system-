'use strict';
// The browser checks the same basic rules; PHP always rechecks on the server.
document.querySelectorAll('form[data-validate]').forEach((form) => {
  form.addEventListener('submit', (event) => {
    const password = form.querySelector('[name="password"]');
    const confirm = form.querySelector('[name="confirm_password"]');
    if (password && confirm && confirm.value !== password.value) {
      event.preventDefault();
      confirm.setCustomValidity('Passwords must match.');
      confirm.reportValidity();
      return;
    }
    if (confirm) confirm.setCustomValidity('');
    if (!form.reportValidity()) event.preventDefault();
  });
});
