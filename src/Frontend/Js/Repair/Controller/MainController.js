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

        const actor = this.entities.find(function (entity) {
            return entity.id === e.currentTarget.dataset.actorId;
        });

        const target = this.entities.find(function (entity) {
            return entity.id === e.currentTarget.dataset.entityId;
        });

        const construction = this.constructions.find(function (construction) {
            return construction.id === target.varietyId;
        });

        this.view.repaint();

        new ConstructionController(
            controller.eventBus,
            controller.view.createConstructionView(),
            new Construction(construction, actor, target, controller.entities, controller.gameId)
        );
    }
}
