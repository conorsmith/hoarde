class TransferEntityController {
    constructor(eventBus, view, model, transfer) {
        this.eventBus = eventBus;
        this.view = view;
        this.model = model;
        this.transfer = transfer;

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

        this.transfer.itemsFrom.forEach(function (transferItem) {
            let itemSlider = TransferItemSlider.fromTemplate(
                controller.view.el.querySelector(".js-item-sliders"),
                controller.view.itemSliderTemplate.content.cloneNode(true),
                controller.view.itemPopoverTemplate.content.cloneNode(true),
                transferItem.quantity,
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
