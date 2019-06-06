class TransferController {
    constructor(eventBus, view, entities) {
        this.eventBus = eventBus;
        this.view = view;
        this.entities = entities;

        this.capacityBarControllers = [];
        this.itemRangeInputControllers = [];

        new TransferErrorController(
            this.eventBus,
            this.view.errorView,
            new Error("")
        );

        this.addEventListeners(this);
    }

    addEventListeners(controller) {
        $(this.view.el).on("show.bs.modal", function (e) {
            controller.onShow(e);
        });

        this.view.submitButtonView.el.addEventListener("click", function (e) {
            controller.onClick(e);
        });
    }

    findTransferringEntities(sourceId) {
        let source = {};

        this.entities.forEach(function (entity) {
            if (entity.id === sourceId) {
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

        return [source, destination];
    }

    onShow(e) {
        const controller = this;

        let entities = this.findTransferringEntities(e.relatedTarget.dataset.sourceId);
        let transferEntities = TransferEntity.createPair(entities[0], entities[1]);

        this.view.entityViews.forEach(function (entityView) {
            let transferEntity = transferEntities.shift();

            new TransferEntityController(
                controller.eventBus,
                entityView,
                transferEntity
            );

            entityView.repaint(transferEntity.entity);
        });

        this.eventBus.dispatchEvent("transfer.initialise");
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
