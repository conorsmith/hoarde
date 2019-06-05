class TransferModal {
    constructor(el) {
        this.el = el;

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
