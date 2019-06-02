class TransferController {
    constructor(eventBus, view) {
        const controller = this;

        this.eventBus = eventBus;
        this.view = view;
        this.itemSliderControllers = [];

        this.view.itemSliders.forEach(function (itemSlider) {
            controller.itemSliderControllers.push(new TransferItemSliderController(
                controller.eventBus,
                itemSlider,
                itemSlider.createModel()
            ));
        });

        this.view.itemCounters.forEach(function (itemCounter) {
            new TransferItemCounterController(
                controller.eventBus,
                itemCounter,
                itemCounter.createModel()
            )
        });

        new TransferErrorController(
            this.eventBus,
            this.view.error,
            new Error("")
        );

        this.addEventListeners(this);
    }

    addEventListeners(controller) {
        this.view.submitButton.el.addEventListener("click", function (e) {
            controller.onClick(e);
        });
    }

    createRequestBody() {
        var body = {};

        this.itemSliderControllers.forEach(function (itemSlider) {
            const entityId = itemSlider.model.entityId;

            if (body[entityId] === undefined) {
                body[entityId] = {
                    entityId: entityId,
                    items: []
                };
            }

            body[entityId].items.push({
                varietyId: itemSlider.model.varietyId,
                quantity: itemSlider.model.quantity
            });
        });

        return Object.values(body);
    }

    onClick(e) {
        const controller = this;

        this.eventBus.dispatchEvent("transfer.submitRequest");

        var xhr = new XMLHttpRequest();

        xhr.onload = function () {
            if (this.responseText === "") {
                window.location.reload();
                return;
            }

            controller.eventBus.dispatchEvent("transfer.submitResponse", {
                error: {
                    message: this.responseText
                }
            });
        };

        xhr.open("POST", "/" + gameId + "/transfer");
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.send(JSON.stringify(this.createRequestBody()));
    }
}
