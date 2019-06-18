class ModalView {
    constructor(el, sliderTemplate) {
        this.el = el;
        this.sliderTemplate = sliderTemplate;
    }

    repaint() {
        //
    }

    createEntitySelectorView(entitySelector) {
        let entitySelectorView = new EntitySelectorView(this.el.querySelector(".js-entity-selector"));

        entitySelectorView.repaint(entitySelector);

        return entitySelectorView;
    }

    createCapacityBarView(capacityBar) {
        let capacityBarView = new CapacityBarView(this.el.querySelector(".js-capacity-bar"));

        capacityBarView.repaint(capacityBar);

        return capacityBarView;
    }

    createCapacityCounterView(capacityBar) {
        let capacityCounterView = new CapacityCounterView(this.el.querySelector(".js-capacity-counter"));

        capacityCounterView.repaint(capacityBar);

        return capacityCounterView;
    }
}
