class TransferErrorController {
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

        this.eventBus.addEventListener("transfer.submitRequest", function (e) {
            controller.onSubmitRequest(e);
        });

        this.eventBus.addEventListener("transfer.submitResponse", function (e) {
            controller.onSubmitResponse(e);
        });
    }

    onInitialise(e) {
        this.model = new Error("");
        this.view.repaint(this.model);
    }

    onSubmitRequest(e) {
        this.model = new Error("");
        this.view.repaint(this.model);
    }

    onSubmitResponse(e) {
        this.model = new Error(e.detail.error.message);
        this.view.repaint(this.model);
    }
}