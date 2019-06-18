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
        this.eventBus.addEventListener("harvest.quantityModified", this.onQuantityModified.bind(this));
    }

    onShow(e) {
        let harvestableEntities = this.entities
            .find(function (entity) {
                return entity.id === e.currentTarget.dataset.entityId;
            })
            .incubator
            .find(function (incubation) {
                return incubation.varietyId === e.currentTarget.dataset.varietyId
                    && incubation.construction.remainingSteps === 0;
            });

        this.entitySelector = new EntitySelector(this.entities, this.entities[0]);
        this.harvest = new Harvest(
            harvestableEntities.id,
            harvestableEntities.label,
            harvestableEntities.icon,
            harvestableEntities.harvestedVarietyWeight,
            0,
            harvestableEntities.quantity
        );
        this.capacityBar = new CapacityBar(this.entities[0], this.harvest);

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
        );

        new SliderController(
            this.eventBus,
            this.view.createSliderView(this.harvest),
            this.harvest
        );
    }

    onSelectEntity(e) {
        this.capacityBar.selectEntity(this.entitySelector.selectedEntity);
    }

    onQuantityModified(e) {
        this.harvest.setQuantity(e.detail.quantity);
    }
}
