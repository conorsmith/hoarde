class TransferEntityController {
    constructor(eventBus, view, model) {
        this.eventBus = eventBus;
        this.view = view;
        this.model = model;

        this.addEventListeners(this);
    }

    addEventListeners(controller) {
        this.eventBus.addEventListener("transfer.initialise", function (e) {
            controller.onInitialise(e);
        });
    }

    onInitialise(e) {
        const controller = this;

        this.view.repaint(this.model.entityFrom);

        new TransferCapacityBarController(
            this.eventBus,
            this.view.capacityBarView,
            this.model
        );

        new TransferInventoryWeightController(
            this.eventBus,
            this.view.inventoryWeightView,
            this.model
        );

        this.model.itemsFrom.forEach(function (transferItem) {
            let itemSliderView = TransferItemSliderView.fromTemplate(
                controller.view.el.querySelector(".js-item-sliders"),
                controller.view.itemSliderTemplate.content.cloneNode(true),
                controller.view.itemPopoverTemplate.content.cloneNode(true),
                transferItem.quantity,
                transferItem
            );

            new TransferItemRangeInputController(
                controller.eventBus,
                itemSliderView.itemRangeInputView,
                transferItem
            );

            new TransferItemCounterController(
                controller.eventBus,
                itemSliderView.itemCounterView,
                transferItem
            );
        });
    }
}
