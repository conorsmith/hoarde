class ModalView {
    constructor(el, sliderTemplate) {
        this.el = el;
        this.sliderTemplate = sliderTemplate;
    }

    repaint(plot) {
        let capacityBar = this.el.querySelector(".js-capacity-bar");

        capacityBar.querySelector(".progress-bar").style.width = (plot.capacityUsed / plot.capacityTotal * 100) + "%";
        this.el.querySelector(".js-capacity-used").innerText = plot.capacityUsed;
        this.el.querySelector(".js-capacity-total").innerText = plot.capacityTotal;
    }

    createSliderView(item) {
        this.el.querySelector(".js-item-sliders").appendChild(
            this.sliderTemplate.content.cloneNode(true)
        );

        let sliderView = new SliderView(this.el.querySelector(".js-item-sliders").lastElementChild);

        sliderView.repaint(item);

        return sliderView;
    }
}
