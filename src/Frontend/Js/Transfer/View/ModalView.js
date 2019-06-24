class ModalView {
    constructor(el, itemSliderTemplate, itemPopoverTemplate) {
        this.el = el;

        this.entityViews = Array.from(this.el.querySelectorAll(".js-inventory")).map(function (entity) {
            return new EntityView(entity, itemSliderTemplate, itemPopoverTemplate);
        });

        this.submitButtonView = new SubmitButtonView(
            this.el.querySelector(".js-submit")
        );

        this.errorView = new ErrorView(
            this.el.querySelector(".js-error")
        );
    }
}
