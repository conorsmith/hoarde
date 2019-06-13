class MainController {
    constructor(eventBus, view, entities, gameId) {
        this.eventBus = eventBus;
        this.view = view;
        this.entities = entities;
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

        let inventory = new Inventory();

        this.entities.forEach(function (entity) {
            if (entity.inventory !== undefined) {
                entity.inventory.items.forEach(function (item) {
                    let sowAction = item.actions.find(function (action) {
                        return action.id === "99b65213-9cee-42ec-9dfe-8a04a790469e";
                    });
                    if (sowAction !== undefined) {
                        inventory.add(item);
                    }
                });
            }
        });

        this.view.repaint(new Plot());

        Object.values(inventory.items).forEach(function (item) {
            new SliderController(
                controller.eventBus,
                controller.view.createSliderView(item),
                item
            );
        });
    }
}
