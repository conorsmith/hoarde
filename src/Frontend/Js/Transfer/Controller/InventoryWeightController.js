class InventoryWeightController {
    constructor(eventBus, view, model) {
        this.eventBus = eventBus;
        this.view = view;
        this.model = model;

        this.addEventListeners(this);
    }

    addEventListeners(controller) {
        this.eventBus.addEventListener("transfer.itemModified", function (e) {
            controller.onItemModified(e);
        });
    }

    onItemModified(e) {
        this.view.repaint(this.model.getNewInventoryWeight());
    }
}