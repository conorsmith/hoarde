import {Form} from "./utility.js";

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

        this.view.getSubmitButton().addEventListener("click", this.onSubmit.bind(this));
    }

    onShow(e) {

        this.harvestFood = e.currentTarget.dataset.harvestFood === "1";
        this.harvestSeeds = e.currentTarget.dataset.harvestSeeds === "1";

        let initialEntity = this.entities.find(function (entity) {
            return entity.isHuman;
        });

        let harvestableEntities = this.entities
            .find(function (entity) {
                return entity.id === e.currentTarget.dataset.entityId;
            })
            .incubator
            .find(function (incubation) {
                return incubation.varietyId === e.currentTarget.dataset.varietyId
                    && incubation.construction.remainingSteps === 0;
            });

        let storageEntities = this.entities.filter(function (entity) {
            return entity.inventory
                && !entity.incubator;
        });

        this.entityId = e.currentTarget.dataset.entityId;
        this.actorId = e.currentTarget.dataset.actorId;
        this.entitySelector = new EntitySelector(storageEntities, initialEntity);
        this.harvest = new Harvest(
            harvestableEntities.varietyId,
            harvestableEntities.label,
            harvestableEntities.icon,
            this.harvestFood
                ? harvestableEntities.harvestedFoodVarietyWeight
                : harvestableEntities.harvestedSeedVarietyWeight * harvestableEntities.harvestedSeedVarietyQuantity,
            0,
            harvestableEntities.quantity
        );
        this.capacityBar = new CapacityBar(initialEntity, this.harvest);

        this.view.repaint(this.harvestFood, this.harvestSeeds);

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

    onSubmit(e) {
        const endpointSegment = this.harvestFood ? "harvest-food" : "harvest-seeds";

        Form.post(
            "/" + this.gameId + "/" + this.actorId + "/" + endpointSegment + "/" + this.entityId,
            {
                inventoryEntityId: this.capacityBar.entity.id,
                varietyId: this.harvest.id,
                quantity: this.harvest.quantity
            }
        );
    }
}
