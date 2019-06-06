class TransferController {
    constructor(eventBus, view, entities) {
        this.eventBus = eventBus;
        this.view = view;
        this.entities = entities;

        this.transfers = [];

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

    onShow(e) {
        const controller = this;

        let source = {};
        let destination = {};
        let i = 0;

        this.entities.forEach(function (entity) {
            if (entity.id === e.relatedTarget.dataset.sourceId) {
                source = entity;
            } else if (entity.id === e.relatedTarget.dataset.destinationId) {
                destination = entity;
            }
        });

        this.transfers = Transfer.createPair(source, destination);

        this.view.entityViews.forEach(function (entityView) {
            let transfer = controller.transfers[i++];

            new TransferEntityController(
                controller.eventBus,
                entityView,
                transfer
            );

            entityView.repaint(transfer.entityFrom);
        });

        this.eventBus.dispatchEvent("transfer.initialise");
    }

    createRequestBody() {
        let body = [];

        this.transfers.forEach(function (transfer) {
            let items = [];

            transfer.itemsFrom.forEach(function (transferItem) {
                items.push({
                    varietyId: transferItem.varietyId,
                    quantity: transferItem.quantity
                });
            });

            body.push({
                entityId: transfer.entityFrom.id,
                items: items
            });
        });

        return body;
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
