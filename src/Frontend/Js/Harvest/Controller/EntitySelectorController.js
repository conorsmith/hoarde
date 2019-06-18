class EntitySelectorController {
    constructor(eventBus, view, entitySelector) {
        this.eventBus = eventBus;
        this.view = view;
        this.entitySelector = entitySelector;

        this.addEventListeners(this);
    }

    addEventListeners(controller) {
        this.view.getOptions().forEach(function (option) {
            option.addEventListener("click", controller.onSelect.bind(controller));
        });
    }

    onSelect(e) {
        e.preventDefault();

        this.entitySelector.select(e.currentTarget.dataset.entityId);

        this.view.repaint(this.entitySelector);
        this.addEventListeners(this);

        this.eventBus.dispatchEvent("harvest.selectEntity");
    }
}
