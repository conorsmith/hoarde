class MainController {
    constructor(eventBus, view, gameId) {
        this.eventBus = eventBus;
        this.view = view;
        this.gameId = gameId;

        this.addEventListeners(this);
    }

    addEventListeners(controller) {
        $(this.view.el).on("show.bs.modal", this.onShow.bind(this));

        this.view.sortable.addEventListener("sortupdate", this.onSort.bind(this));

        this.view.getSubmitButtonEl().addEventListener("click", this.onSubmit.bind(this));
    }

    onShow(e) {
        this.orderManifest = this.view.createOrderManifest();
    }

    onSort(e) {
        this.orderManifest = this.view.createOrderManifest();
    }

    onSubmit(e) {
        let form = document.createElement("form");
        form.setAttribute("action", "/" + this.gameId + "/sort");
        form.setAttribute("method", "POST");
        form.setAttribute("hidden", true);

        let input = document.createElement("input");
        input.setAttribute("type", "hidden");
        input.setAttribute("name", "orderedEntityIds");
        input.setAttribute("value", JSON.stringify(this.orderManifest.orderedEntityIds));
        form.appendChild(input);

        document.body.appendChild(form);

        form.submit();
    }
}
