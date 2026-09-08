document.querySelectorAll('.toggle-password').forEach(function (button) {
    button.addEventListener('click', function () {
        var wrap = button.closest('.step-input');
        var input = wrap ? wrap.querySelector('input') : null;
        var icon = button.querySelector('i');
        if (!input) {
            return;
        }
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        if (icon) {
            icon.classList.toggle('fa-eye', !show);
            icon.classList.toggle('fa-eye-slash', show);
        }
        button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
});
