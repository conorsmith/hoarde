class TransferModal {
    constructor(el, itemSliderTemplate, itemPopoverTemplate) {
        this.el = el;

        this.entities = Array.from(this.el.querySelectorAll(".js-inventory")).map(function (entity) {
            return new TransferEntity(entity, itemSliderTemplate, itemPopoverTemplate);
        });

        this.capacityBars = Array.from(this.el.querySelectorAll(".js-capacity-bar")).map(function (bar) {
            return new TransferCapacityBar(bar);
        });

        this.inventoryWeights = Array.from(this.el.querySelectorAll(".js-inventory-weight")).map(function (weight) {
            return new TransferInventoryWeight(weight);
        });

        this.submitButton = new TransferSubmitButton(
            this.el.querySelector(".js-submit")
        );

        this.error = new TransferError(
            this.el.querySelector(".js-error")
        );
    }
}
