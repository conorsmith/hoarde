class TransferModalView {
    constructor(el, itemSliderTemplate, itemPopoverTemplate) {
        this.el = el;

        this.entityViews = Array.from(this.el.querySelectorAll(".js-inventory")).map(function (entity) {
            return new TransferEntityView(entity, itemSliderTemplate, itemPopoverTemplate);
        });

        this.submitButtonView = new TransferSubmitButtonView(
            this.el.querySelector(".js-submit")
        );

        this.errorView = new TransferErrorView(
            this.el.querySelector(".js-error")
        );
    }
}
