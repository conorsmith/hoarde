class TransferModal {
    constructor(el, itemSliderTemplate, itemPopoverTemplate) {
        this.el = el;

        this.entities = Array.from(this.el.querySelectorAll(".js-inventory")).map(function (entity) {
            return new TransferEntityView(entity, itemSliderTemplate, itemPopoverTemplate);
        });

        this.submitButton = new TransferSubmitButton(
            this.el.querySelector(".js-submit")
        );

        this.error = new TransferError(
            this.el.querySelector(".js-error")
        );
    }
}
