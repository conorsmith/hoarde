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
        form.setAttribute("action", "/" + this.model.gameId + "/construct");
        form.setAttribute("method", "POST");
        form.setAttribute("hidden", true);

        let actorIdInput = document.createElement("input");
        actorIdInput.setAttribute("type", "hidden");
        actorIdInput.setAttribute("name", "actorId");
        actorIdInput.setAttribute("value", this.model.entityId);
        form.appendChild(actorIdInput);

        document.body.appendChild(form);

        form.submit();
    }
}
