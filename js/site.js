(function () {
    var overlay = document.getElementById('search-overlay');
    var toggle = document.getElementById('search-toggle');
    var closeBtn = document.getElementById('search-close');
    var customizeModal = document.getElementById('customize-cake-modal');

    function openCustomizeModal() {
        if (!customizeModal) {
            return;
        }
        customizeModal.hidden = false;
        document.body.classList.add('modal-open');
    }

    function closeCustomizeModal() {
        if (!customizeModal || customizeModal.hidden) {
            return;
        }
        customizeModal.hidden = true;
        document.body.classList.remove('modal-open');
    }

    if (toggle && overlay) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            overlay.hidden = !overlay.hidden;
            var input = overlay.querySelector('input[name="q"]');
            if (!overlay.hidden && input) {
                input.focus();
            }
        });
    }
    if (closeBtn && overlay) {
        closeBtn.addEventListener('click', function () {
            overlay.hidden = true;
        });
    }
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') {
            return;
        }
        if (overlay && !overlay.hidden) {
            overlay.hidden = true;
        }
        closeCustomizeModal();
    });

    document.querySelectorAll('.js-open-customize').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openCustomizeModal();
        });
    });
    document.querySelectorAll('[data-close-customize]').forEach(function (el) {
        el.addEventListener('click', function () {
            closeCustomizeModal();
        });
    });
    if (customizeModal && /(?:^|[?&])customize=1(?:&|$)/.test(window.location.search)) {
        openCustomizeModal();
    }

    document.querySelectorAll('.step-options').forEach(function (group) {
        group.querySelectorAll('.pill-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (btn.tagName === 'A' || btn.tagName === 'LABEL') {
                    return;
                }
                var target = group.getAttribute('data-input');
                if (target) {
                    group.querySelectorAll('.pill-btn').forEach(function (b) {
                        b.classList.remove('active');
                    });
                    btn.classList.add('active');
                    var hidden = document.getElementById(target);
                    if (hidden) {
                        hidden.value = btn.getAttribute('data-value') || btn.textContent.trim();
                    }
                }
            });
        });
    });

    var refBtn = document.getElementById('reference-btn');
    var refInput = document.getElementById('custom-reference');
    if (refBtn && refInput) {
        refBtn.addEventListener('click', function () {
            refInput.click();
        });
        refInput.addEventListener('change', function () {
            if (refInput.files && refInput.files[0]) {
                refBtn.textContent = refInput.files[0].name;
            }
        });
    }

    document.querySelectorAll('.heart-btn[data-id]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            fetch('favorite.php?ajax=1&id=' + encodeURIComponent(btn.getAttribute('data-id')))
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    var icon = btn.querySelector('i');
                    if (!icon) {
                        return;
                    }
                    icon.classList.toggle('fas', data.on);
                    icon.classList.toggle('far', !data.on);
                })
                .catch(function () {
                    window.location.href = 'favorite.php?id=' + encodeURIComponent(btn.getAttribute('data-id'));
                });
        });
    });

    document.querySelectorAll('.pill-btn input[type="radio"]').forEach(function (input) {
        input.addEventListener('change', function () {
            var group = input.closest('.step-options');
            if (!group) {
                return;
            }
            group.querySelectorAll('.pill-btn').forEach(function (b) {
                b.classList.remove('active');
            });
            input.closest('.pill-btn').classList.add('active');
        });
    });

    function syncCardPaymentFields() {
        var fields = document.getElementById('card-payment-fields');
        var selected = document.querySelector('input[name="payment"]:checked');
        if (!fields) {
            return;
        }
        fields.hidden = !(selected && selected.value === 'Card');
    }
    document.querySelectorAll('input[name="payment"]').forEach(function (input) {
        input.addEventListener('change', syncCardPaymentFields);
    });
    syncCardPaymentFields();
})();
