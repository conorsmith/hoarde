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

        this.eventBus.addEventListener("harvest.selectEntity", this.onSelectEntity.bind(this));
    }

    onShow(e) {

        this.entitySelector = new EntitySelector(this.entities, this.entities[0]);
        this.capacityBar = new CapacityBar(this.entities[0]);

        this.view.repaint();

        new EntitySelectorController(
            this.eventBus,
            this.view.createEntitySelectorView(this.entitySelector),
            this.entitySelector
        );

        new CapacityBarController(
            this.eventBus,
            this.view.createCapacityBarView(this.capacityBar),
            this.capacityBar
        );

        new CapacityCounterController(
            this.eventBus,
            this.view.createCapacityCounterView(this.capacityBar),
            this.capacityBar
        )
    }

    onSelectEntity(e) {
        this.capacityBar.selectEntity(this.entitySelector.selectedEntity);
    }
}
