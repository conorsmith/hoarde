class TransferEntityController {
    constructor(eventBus, view, model, transfers) {
        this.eventBus = eventBus;
        this.view = view;
        this.model = model;
        this.transfers = transfers;

        this.addEventListeners(this);
    }

    addEventListeners(controller) {
        this.eventBus.addEventListener("transfer.initialise", function (e) {
            controller.onInitialise(e);
        });
    }

    onInitialise(e) {
        const controller = this;

        this.view.repaint(this.model);

        this.model.inventory.items.forEach(function (item) {

            const initialQuantity = 0;
            const transferItem = new TransferItem(
                controller.model.id,
                item.varietyId,
                item.weight,
                initialQuantity
            );

            controller.transfers.forEach(function (transfer) {
                transfer.addItem(transferItem);
            });

            let itemSlider = TransferItemSlider.fromTemplate(
                controller.view.el.querySelector(".js-item-sliders"),
                controller.view.itemSliderTemplate.content.cloneNode(true),
                controller.view.itemPopoverTemplate.content.cloneNode(true),
                item,
                initialQuantity,
                transferItem
            );

            new TransferItemRangeInputController(
                controller.eventBus,
                itemSlider.itemRangeInput,
                transferItem
            );

            new TransferItemCounterController(
                controller.eventBus,
                itemSlider.itemCounter,
                transferItem
            );
        });
    }
}
