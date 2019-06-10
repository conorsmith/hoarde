class TransferEntityController {
    constructor(eventBus, view, model, entities) {
        this.eventBus = eventBus;
        this.view = view;
        this.model = model;
        this.entities = entities;

        this.entitySelectorController = new TransferEntitySelectorController(
            this.eventBus,
            this.view.entitySelectorView,
            this.model,
            this.entities
        );

        this.addEventListeners(this);
    }

    addEventListeners(controller) {
        this.eventBus.addEventListener("transfer.initialise", function (e) {
            controller.onInitialise(e);
        });
    }

    destroy() {
        this.entitySelectorController.destroy();
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

            $(itemSliderView.el).find("[data-toggle='popover']").popover({
                trigger: "focus",
                html: true
            });
        });
    }
}
