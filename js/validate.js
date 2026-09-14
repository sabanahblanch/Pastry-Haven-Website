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
            var payment = form.querySelector('[name="payment"]:checked');

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
                return;
            }
            var readyDate = form.querySelector('[name="ready_date"]');
            if (readyDate && readyDate.hasAttribute('required')) {
                if (!readyDate.value) {
                    e.preventDefault();
                    showError(form, 'Please select a date for your custom cake.');
                    return;
                }
                if (readyDate.min && readyDate.value < readyDate.min) {
                    e.preventDefault();
                    showError(form, 'Please select a date at least 1 day from today.');
                    return;
                }
            }
            if (payment && payment.value === 'Card') {
                var cardName = form.querySelector('[name="card_name"]');
                var cardNumber = form.querySelector('[name="card_number"]');
                var cardExpiry = form.querySelector('[name="card_expiry"]');
                var cardCvv = form.querySelector('[name="card_cvv"]');
                var digits = cardNumber ? cardNumber.value.replace(/\D+/g, '') : '';
                if (!cardName || cardName.value.trim().length < 2) {
                    e.preventDefault();
                    showError(form, 'Please enter the name on the card.');
                    return;
                }
                if (digits.length < 13 || digits.length > 19) {
                    e.preventDefault();
                    showError(form, 'Please enter a valid card number.');
                    return;
                }
                if (!cardExpiry || !/^(0[1-9]|1[0-2])\/\d{2}$/.test(cardExpiry.value.trim())) {
                    e.preventDefault();
                    showError(form, 'Please enter the card expiry as MM/YY.');
                    return;
                }
                if (!cardCvv || !/^\d{3,4}$/.test(cardCvv.value.trim())) {
                    e.preventDefault();
                    showError(form, 'Please enter a valid CVV.');
                }
            }
        });
    });
})();
