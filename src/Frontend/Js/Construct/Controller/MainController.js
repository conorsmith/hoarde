class MainController {
    constructor(eventBus, view, entities, constructions, actions, gameId) {
        this.eventBus = eventBus;
        this.view = view;
        this.entities = entities;
        this.constructions = constructions;
        this.actions = actions;
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

        let availableConstructions = this.constructions.filter(function (construction) {
            let requiresSelectedTool = false;

            construction.tools.forEach(function (tool) {
                if (tool.id === e.currentTarget.dataset.toolVarietyId) {
                    requiresSelectedTool = true;
                }
            });

            return requiresSelectedTool;
        });

        let action = this.actions.find(function (action) {
            return action.id === e.currentTarget.dataset.actionId;
        });

        this.view.repaint(action);

        availableConstructions.forEach(function (construction) {
            new ConstructionController(
                controller.eventBus,
                controller.view.createConstructionView(),
                new Construction(construction, entity, action, controller.entities, controller.gameId)
            );
        });
    }
}
