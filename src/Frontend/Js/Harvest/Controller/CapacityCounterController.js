class CapacityCounterController {
    constructor(eventBus, view, capacityBar) {
        this.eventBus = eventBus;
        this.view = view;
        this.capacityBar = capacityBar;

        this.addEventListeners(this);
    }

    addEventListeners(controller) {
        this.eventBus.addEventListener("harvest.selectEntity", this.onSelectEntity.bind(this));
    }

    onSelectEntity(e) {
        this.view.repaint(this.capacityBar);
    }
}
