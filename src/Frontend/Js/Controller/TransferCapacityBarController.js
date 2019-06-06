class TransferCapacityBarController {
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
        const newWeight = this.model.getNewInventoryWeight();
        const oldWeight = this.model.getOldInventoryWeight();
        const capacity = this.model.getInventoryCapacity();

        if (newWeight > capacity) {
            this.view.repaint(
                oldWeight / capacity * 100,
                (capacity - oldWeight) / capacity * 100,
                true,
                true
            );
        } else if (newWeight > oldWeight) {
            this.view.repaint(
                oldWeight / capacity * 100,
                (newWeight - oldWeight) / capacity * 100,
                true,
                false
            );
        } else if (newWeight < oldWeight) {
            this.view.repaint(
                newWeight / capacity * 100,
                (oldWeight - newWeight) / capacity * 100,
                false,
                false
            );
        } else {
            this.view.repaint(
                oldWeight / capacity * 100,
                0,
                false,
                false
            );
        }
    }
}