class MainController {
    constructor(eventBus, view, entities, gameId) {
        this.eventBus = eventBus;
        this.view = view;
        this.entities = entities;
        this.gameId = gameId;

        this.addEventListeners(this);
    }

    addEventListeners(controller) {
        $(this.view.el).on("show.bs.modal", this.onShow.bind(this));
    }

    onShow(e) {
        const entity = this.entities.find(function (entity) {
            return entity.id === e.currentTarget.dataset.entityId;
        });

        this.view.repaint(entity);
    }
}
