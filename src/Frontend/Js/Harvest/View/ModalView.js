class ModalView {
    constructor(el, sliderTemplate) {
        this.el = el;
        this.sliderTemplate = sliderTemplate;
    }

    getSubmitButton() {
        return this.el.querySelector(".js-harvest-submit");
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

    createSliderView(harvest) {
        this.el.querySelector(".js-item-sliders").innerHTML = "";

        this.el.querySelector(".js-item-sliders").appendChild(
            this.sliderTemplate.content.cloneNode(true)
        );

        let sliderView = new SliderView(this.el.querySelector(".js-item-sliders").lastElementChild);

        sliderView.repaint(harvest);

        return sliderView;
    }
}
