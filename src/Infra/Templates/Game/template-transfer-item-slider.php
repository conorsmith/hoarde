<template id="transfer-item-slider">
    <div class="item-slider d-flex">

        <div class="align-self-center"
             style="margin-right: 1rem; white-space: nowrap;"
        >
            <i class="tmpl-icon fas fa-fw"
               data-toggle="popover"
               data-placement="top"
               tabindex="0"
            ></i>
            <span class="tmpl-label"></span>
        </div>

        <div class="flex-fill"
             style="height: 32px;"
        >
            <input type="range"
                   min="0"
                   style="width: 100%"
            >
            <datalist></datalist>
        </div>

        <div class="align-self-center"
             style="margin-left: 1rem; text-align: right; width: 1.4rem;"
        >
            <span class="tmpl-item-counter js-item-counter">
              0
            </span>
        </div>

    </div>
</template>
