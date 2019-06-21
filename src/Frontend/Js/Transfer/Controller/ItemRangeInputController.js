class ItemRangeInputController {
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
        this.model.quantity = parseInt(e.target.value, 10);

        this.eventBus.dispatchEvent("transfer.itemModified", {
            item: this.model
        });
    }
}