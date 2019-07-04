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
        Form.post(
            "/" + this.gameId + "/sort",
            {
                orderedEntityIds: JSON.stringify(this.orderManifest.orderedEntityIds)
            }
        );
    }
}
