class TransferController {
    constructor(eventBus, view, entities) {
        const controller = this;

        this.eventBus = eventBus;
        this.view = view;
        this.entities = entities;

        this.itemSliderTemplate = document.getElementById("transfer-item-slider");
        this.itemPopoverTemplate = document.getElementById("item-popover");

        this.capacityBarControllers = [];
        this.itemRangeInputControllers = [];

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

        this.transferA = new Transfer(inventoryA, inventoryB);
        this.transferB = new Transfer(inventoryB, inventoryA);

        this.capacityBarControllers.push(new TransferCapacityBarController(
            this.eventBus,
            this.view.capacityBars[0],
            this.transferA
        ));

        new TransferInventoryWeightController(
            this.eventBus,
            this.view.inventoryWeights[0],
            this.transferA
        );

        this.capacityBarControllers.push(new TransferCapacityBarController(
            this.eventBus,
            this.view.capacityBars[1],
            this.transferB
        ));

        new TransferInventoryWeightController(
            this.eventBus,
            this.view.inventoryWeights[1],
            this.transferB
        );

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
        const controller = this;

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

        let entities = [source, destination];

        this.view.el.querySelectorAll(".js-inventory").forEach(function (body) {
            let entity = entities.shift();

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

            body.querySelector(".js-item-sliders").innerHTML = "";

            entity.inventory.items.forEach(function (item) {

                const initialQuantity = 0;
                const transferItem = new TransferItem(
                    entity.id,
                    item.varietyId,
                    item.weight,
                    initialQuantity
                );

                controller.transferA.addItem(transferItem);
                controller.transferB.addItem(transferItem);

                const itemSliderTemplate = controller.itemSliderTemplate.content.cloneNode(true);
                const itemPopoverTemplate = controller.itemPopoverTemplate.content.cloneNode(true);
                const itemPopoverRenderer = document.createElement("div");
                const itemSliderDatalistId = "item-slider-" + item.entityId + "-" + item.varietyId;

                itemPopoverTemplate.querySelector(".tmpl-description").innerText = item.description;
                itemPopoverTemplate.querySelector(".tmpl-weight").innerText = item.weight > 1000
                    ? (item.weight / 1000) + " kg"
                    : item.weight + " g";
                itemPopoverTemplate.querySelector(".tmpl-resources").innerText = item.resourceLabel;
                itemPopoverRenderer.appendChild(itemPopoverTemplate);

                itemSliderTemplate.querySelector(".tmpl-label").innerText = item.label;

                itemSliderTemplate.querySelector(".tmpl-icon").classList.add("fa-" + item.icon);
                itemSliderTemplate.querySelector(".tmpl-icon").title = item.label;
                itemSliderTemplate.querySelector(".tmpl-icon").dataset.content = itemPopoverRenderer.innerHTML;

                itemSliderTemplate.querySelector("input[type='range']").value = initialQuantity;
                itemSliderTemplate.querySelector("input[type='range']").max = item.quantity;
                itemSliderTemplate.querySelector("input[type='range']").setAttribute("list", itemSliderDatalistId);

                itemSliderTemplate.querySelector("datalist").id = itemSliderDatalistId;

                for (let i = 0; i <= item.quantity; i++) {
                    let option = document.createElement("option");
                    option.value = i;
                    itemSliderTemplate.querySelector("datalist").appendChild(option);
                }

                body.querySelector(".js-item-sliders").appendChild(itemSliderTemplate);

                let itemRangeInput = new TransferItemRangeInput(
                    body.querySelector(".js-item-sliders").lastElementChild.querySelector("input[type='range']")
                );

                let itemCounter = new TransferItemCounter(
                    body.querySelector(".js-item-sliders").lastElementChild.querySelector(".js-item-counter")
                );

                itemCounter.repaint(transferItem);

                controller.itemRangeInputControllers.push(new TransferItemRangeInputController(
                    controller.eventBus,
                    itemRangeInput,
                    transferItem
                ));

                new TransferItemCounterController(
                    controller.eventBus,
                    itemCounter,
                    transferItem
                );
            });
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

        this.itemRangeInputControllers.forEach(function (itemRangeInputController) {
            const entityId = itemRangeInputController.model.entityId;

            body[entityId].items.push({
                varietyId: itemRangeInputController.model.varietyId,
                quantity: itemRangeInputController.model.quantity
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
