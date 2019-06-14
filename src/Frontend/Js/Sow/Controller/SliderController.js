class SliderController {
    constructor(eventBus, view, model) {
        this.eventBus = eventBus;
        this.view = view;
        this.model = model;

        this.addEventListeners(this);
    }

    addEventListeners(controller) {
        this.view.getInputEl().addEventListener("input", function (e) {
            controller.onInput(e);
        })
    }

    onInput() {
        this.view.repaintCounter();

        this.model.quantity = parseInt(this.view.getInputValue(), 10);

        this.eventBus.dispatchEvent("sow.itemModified", {
            item: this.model
        });
    }
}
