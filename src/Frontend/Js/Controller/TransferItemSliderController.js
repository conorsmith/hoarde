class TransferItemSliderController {
    constructor(eventBus, view, model) {
        this.eventBus = eventBus;
        this.view = view;
        this.model = model;

        this.addEventListeners(this);
    }

    addEventListeners(controller) {
        this.view.el.addEventListener("input", function (e) {
            controller.onInput(e);
        })
    }

    onInput(e) {
        this.model.quantity = e.target.value;

        this.eventBus.dispatchEvent("transfer.itemModified", {
            item: this.model
        });
    }
}