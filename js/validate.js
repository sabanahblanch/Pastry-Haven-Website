(function () {
    function emailOk(value) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
    }

    function phoneOk(value, required) {
        if (!value) {
            return !required;
        }
        return /^(09\d{9}|\+639\d{9}|\d{7,15})$/.test(value.replace(/[^0-9+]/g, ''));
    }

    function showError(form, message) {
        var existing = form.querySelector('.js-form-error');
        if (!existing) {
            existing = document.createElement('p');
            existing.className = 'about-text auth-flash auth-flash-error js-form-error';
            form.insertBefore(existing, form.firstChild);
        }
        existing.textContent = message;
    }

    function clearError(form) {
        var existing = form.querySelector('.js-form-error');
        if (existing) {
            existing.remove();
        }
    }

    document.querySelectorAll('form.js-validate').forEach(function (form) {
        form.setAttribute('novalidate', 'novalidate');
        form.addEventListener('submit', function (e) {
            clearError(form);

            var name = form.querySelector('[name="name"]');
            var email = form.querySelector('[name="email"]');
            var phone = form.querySelector('[name="phone"]');
            var message = form.querySelector('[name="message"]');
            var password = form.querySelector('[name="password"]');
            var confirm = form.querySelector('[name="confirm_password"]');
            var address = form.querySelector('[name="address"]');
            var fulfillment = form.querySelector('[name="fulfillment"]:checked');

            if (name && name.hasAttribute('required') && name.value.trim().length < 2) {
                e.preventDefault();
                showError(form, 'Please enter your name.');
                return;
            }
            if (email && !emailOk(email.value.trim())) {
                e.preventDefault();
                showError(form, 'Please enter a valid email address.');
                return;
            }
            if (phone && phone.hasAttribute('required') && !phoneOk(phone.value.trim(), true)) {
                e.preventDefault();
                showError(form, 'Please enter a valid phone number.');
                return;
            }
            if (phone && phone.value.trim() !== '' && !phoneOk(phone.value.trim(), false)) {
                e.preventDefault();
                showError(form, 'Please enter a valid phone number.');
                return;
            }
            if (password && password.hasAttribute('required') && password.value.length < 6) {
                e.preventDefault();
                showError(form, 'Password must be at least 6 characters.');
                return;
            }
            if (confirm && password && confirm.value !== password.value) {
                e.preventDefault();
                showError(form, 'Passwords do not match.');
                return;
            }
            if (message && message.hasAttribute('required') && message.value.trim().length < 5) {
                e.preventDefault();
                showError(form, 'Please enter a message.');
                return;
            }
            if (fulfillment && fulfillment.value === 'Delivery' && address && address.value.trim() === '') {
                e.preventDefault();
                showError(form, 'Please enter a delivery address in Dumaguete City.');
            }
        });
    });
})();
