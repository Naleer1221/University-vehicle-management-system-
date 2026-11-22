'use strict';
// JavaScript is preview only. PHP calculates and stores the real total.
document.querySelectorAll('[data-booking-calculator]').forEach((box) => {
  const pickup = box.querySelector('[name="pickup_date"]');
  const returned = box.querySelector('[name="return_date"]');
  const output = box.querySelector('[data-total]');
  const price = Number(box.dataset.price || 0);
  if (!pickup || !returned || !output) return;
  const today = new Date().toLocaleDateString('en-CA');
  if (today.length === 10) pickup.min = today;
  function calculate() {
    if (pickup.value) returned.min = pickup.value;
    if (pickup.value && returned.value && returned.value >= pickup.value) {
      const from = Date.parse(pickup.value + 'T00:00:00Z');
      const to = Date.parse(returned.value + 'T00:00:00Z');
      const days = Math.round((to - from) / 86400000) + 1;
      output.textContent = days + ' day(s) × LKR ' + price.toLocaleString() + ' = LKR ' + (days * price).toLocaleString();
    } else {
      output.textContent = 'Choose pickup and return dates.';
    }
  }
  pickup.addEventListener('change', calculate);
  returned.addEventListener('change', calculate);
  calculate();
});
