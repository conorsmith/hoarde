class TransferController {
    constructor(eventBus, view, entities) {
        const controller = this;

        this.eventBus = eventBus;
        this.view = view;
        this.entities = entities;

        this.capacityBarControllers = [];
        this.itemSliderControllers = [];

        const inventoryA = new TransferInventory(
            this.entities[0].id,
            this.entities[0].inventory.weight,
            this.entities[0].inventory.capacity
        );

        const inventoryB = new TransferInventory(
            this.entities[1].id,
            this.entities[1].inventory.weight,
            this.entities[1].inventory.capacity
        );

        const transferA = new Transfer(inventoryA, inventoryB);
        const transferB = new Transfer(inventoryB, inventoryA);

        this.capacityBarControllers.push(new TransferCapacityBarController(
            this.eventBus,
            this.view.capacityBars[0],
            transferA
        ));

        new TransferInventoryWeightController(
            this.eventBus,
            this.view.inventoryWeights[0],
            transferA
        );

        this.capacityBarControllers.push(new TransferCapacityBarController(
            this.eventBus,
            this.view.capacityBars[1],
            transferB
        ));

        new TransferInventoryWeightController(
            this.eventBus,
            this.view.inventoryWeights[1],
            transferB
        );

        this.view.itemSliders.forEach(function (itemSlider) {
            const item = itemSlider.createModel();

            controller.itemSliderControllers.push(new TransferItemSliderController(
                controller.eventBus,
                itemSlider,
                item
            ));

            transferA.addItem(item);
            transferB.addItem(item);
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
        $(this.view.el).on("show.bs.modal", function (e) {
            controller.onShow(e);
        });

        this.view.submitButton.el.addEventListener("click", function (e) {
            controller.onClick(e);
        });
    }

    onShow(e) {
        let source = {};

        this.entities.forEach(function (entity) {
            if (entity.id === e.relatedTarget.dataset.sourceId) {
                source = entity;
            }
        });

        let destination = {};

        this.entities.forEach(function (entity) {
            if ((
                    source.varietyId === "fde2146a-c29d-4262-b96f-ec7b696eccad"
                    && entity.varietyId === "59593b72-3845-491e-9721-4452a337019b"
                ) || (
                    source.varietyId === "59593b72-3845-491e-9721-4452a337019b"
                    && entity.varietyId === "fde2146a-c29d-4262-b96f-ec7b696eccad"
                )
            ) {
                destination = entity;
            }
        });

        let entities = [destination, source];

        this.view.el.querySelectorAll(".js-inventory").forEach(function (body) {
            let entity = entities.pop();

            body.querySelector(".js-icon").classList.add("fa-" + entity.icon);
            body.querySelector(".js-label").innerText = entity.label;
            body.querySelector(".js-inventory-weight").innerText = entity.inventory.weight / 1000;
            body.querySelector(".js-inventory-capacity").innerText = entity.inventory.capacity / 1000;

            let capacityBar = body.querySelector(".js-capacity-bar");

            let capacityBarPrimary = capacityBar.querySelectorAll(".progress-bar")[0];
            if (entity.inventory.weight < entity.inventory.capacity) {
                capacityBarPrimary.classList.add("bg-primary");
            } else {
                capacityBarPrimary.classList.add("bg-danger");
            }
            capacityBarPrimary.style.width = (entity.inventory.weight / entity.inventory.capacity * 100) + "%";
        });
    }

    createRequestBody() {
        let body = {};

        this.capacityBarControllers.forEach(function (capacityBar) {
            const entityId = capacityBar.model.inventoryFrom.entityId;

            body[entityId] = {
                entityId: entityId,
                items: []
            }
        });

        this.itemSliderControllers.forEach(function (itemSlider) {
            const entityId = itemSlider.model.entityId;

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
