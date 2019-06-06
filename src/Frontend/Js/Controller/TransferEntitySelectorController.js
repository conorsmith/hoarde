class TransferEntitySelectorController {
    constructor(eventBus, view, model, entities) {
        this.eventBus = eventBus;
        this.view = view;
        this.model = model;
        this.entities = entities;

        this.addEventListeners(this);
    }

    addEventListeners(controller) {
        this.eventBus.addEventListener(
            "transfer.initialise",
            this.boundOnInitialise = this.onInitialise.bind(this)
        );
    }

    destroy() {
        this.eventBus.removeEventListener("transfer.initialise", this.boundOnInitialise);
    }

    onInitialise(e) {
        const controller = this;

        this.view.repaint(this.entities, this.model.entityFrom, this.model.entityTo);

        this.view.getOptions().forEach(function (option) {
            option.addEventListener("click", controller.onSelect.bind(controller));
        });
    }

    onSelect(e) {
        const controller = this;

        e.preventDefault();

        let selectedEntity;

        this.entities.forEach(function (entity) {
            if (entity.id === e.currentTarget.dataset.entityId) {
                selectedEntity = entity;
            }
        });

        this.eventBus.dispatchEvent("transfer.switchEntity", {
            currentEntityId: this.model.entityFrom.id,
            selectedEntityId: selectedEntity.id
        });
    }
}