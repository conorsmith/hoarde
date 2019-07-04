<template id="item-popover">
    <p class="popover-description"><?=$description?></p>
    <div class="popover-weight">
        <span class="popover-label">Weight:</span>
        <span class="popover-value"><?=$weight?></span>
    </div>
    <?php if ($resources !== "") : ?>
        <div class="popover-resources">
            <span class="popover-label">Resource:</span>
            <span class="popover-value"><?=$resources?></span>
        </div>
    <?php endif ?>
</template>
