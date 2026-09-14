<?php
$customize_return = $customize_return ?? 'menu.php?category=cakes&customize=1';
$customize_form_class = 'build-card customize-modal-card';
?>
<div id="customize-cake-modal" class="customize-modal" hidden>
    <div class="customize-modal-backdrop" data-close-customize></div>
    <div class="customize-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="customize-cake-title">
        <button type="button" class="heart-btn customize-modal-close" data-close-customize aria-label="Close customize cake">
            <i class="fas fa-times"></i>
        </button>
        <?php require __DIR__ . '/customize-cake-form.php'; ?>
    </div>
</div>
