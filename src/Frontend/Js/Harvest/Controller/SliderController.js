class SliderController {
    constructor(eventBus, view, model) {
        this.eventBus = eventBus;
        this.view = view;
        this.model = model;

        this.addEventListeners(this);
    }

    addEventListeners(controller) {
        this.view.getInputEl().addEventListener("input", this.onInput.bind(this));
    }

    onInput() {
        this.view.repaintCounter();

        this.model.quantity = parseInt(this.view.getInputValue(), 10);

        this.eventBus.dispatchEvent("harvest.quantityModified", {
            quantity: this.model.quantity
        });
    }
}
