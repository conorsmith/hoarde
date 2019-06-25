class ConstructionController {
    constructor(eventBus, view, model) {
        this.eventBus = eventBus;
        this.view = view;
        this.model = model;

        this.view.repaint(this.model);

        this.addEventListeners(this);
    }

    addEventListeners(controller) {
        this.view.el.querySelector(".btn-primary").addEventListener("click", this.onSubmit.bind(this));
    }

    onSubmit(e) {
        let form = document.createElement("form");
        form.setAttribute("action", "/" + this.model.gameId + "/" + this.model.entityId + "/repair/" + this.model.targetId);
        form.setAttribute("method", "POST");
        form.setAttribute("hidden", true);

        document.body.appendChild(form);

        form.submit();
    }
}
