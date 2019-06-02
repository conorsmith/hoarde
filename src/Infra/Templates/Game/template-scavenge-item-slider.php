<template id="scavange-item-slider">
    <div class="item-slider d-flex">
        <div class="align-self-center" style="margin-right: 1rem;">
            <i class="tmpl-icon fas fa-fw"
               data-toggle="popover"
               data-placement="top"
               tabindex="0"
            ></i>
            <span class="tmpl-label"></span>
        </div>
        <div class="flex-fill" style="height: 32px;">
            <input type="range" min="0" style="width: 100%">
            <datalist></datalist>
        </div>
        <div class="align-self-center">
            <span class="tmpl-quantity js-scavange-quantity" style="margin-left: 1rem; text-align: right;"></span>
        </div>
    </div>
</template>
