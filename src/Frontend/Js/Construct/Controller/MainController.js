class MainController {
    constructor(eventBus, view, entities, constructions, gameId) {
        this.eventBus = eventBus;
        this.view = view;
        this.entities = entities;
        this.constructions = constructions;
        this.gameId = gameId;

        this.addEventListeners(this);
    }

    addEventListeners(controller) {
        $(this.view.el).on("show.bs.modal", function (e) {
            controller.onShow(e);
        });
    }

    onShow(e) {
        const controller = this;

        const entity = this.entities.find(function (entity) {
            return entity.id === e.currentTarget.dataset.entityId;
        });

        this.view.clearConstructions();

        this.constructions.forEach(function (construction) {
            new ConstructionController(
                controller.eventBus,
                controller.view.createConstructionView(),
                new Construction(construction, entity, controller.gameId)
            );
        });
    }
}
