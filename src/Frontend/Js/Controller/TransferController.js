class TransferController {
    constructor(eventBus, view) {
        this.eventBus = eventBus;
        this.view = view;

        this.view.itemSliders.forEach(function (itemSlider) {
            new TransferItemSliderController(
                eventBus,
                itemSlider,
                itemSlider.createModel()
            );
        });

        this.view.itemCounters.forEach(function (itemCounter) {
            new TransferItemCounterController(
                eventBus,
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

    onClick(e) {
        const controller = this;
        var body = [];

        this.eventBus.dispatchEvent("transfer.submitRequest");

        document.getElementById("transferModal").querySelectorAll(".js-inventory").forEach(function (inventory) {
            var transfer = {
                entityId: inventory.dataset.entityId,
                items: []
            };
            inventory.querySelectorAll("input[type='range']").forEach(function (input) {
                transfer.items.push({
                    varietyId: input.dataset.varietyId,
                    quantity: input.value
                })
            });
            body.push(transfer)
        });

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
        xhr.send(JSON.stringify(body));
    }
}