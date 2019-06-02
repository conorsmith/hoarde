class TransferItemCounterController {
    constructor(eventBus, view, model) {
        this.eventBus = eventBus;
        this.view = view;
        this.model = model;

        this.addEventListeners(this);
    }

    addEventListeners(controller) {
        this.eventBus.addEventListener("transfer.itemModified", function (e) {
            controller.onItemModified(e)
        });
    }

    onItemModified(e) {
        if (e.detail.item.entityId === this.model.entityId
            && e.detail.item.varietyId === this.model.varietyId
        ) {
            this.view.repaint(e.detail.item);
        }
    }
}