class ModalView {
    constructor(el, sliderTemplate) {
        this.el = el;
        this.sliderTemplate = sliderTemplate;
    }

    getSubmitButtonEl() {
        return this.el.querySelector(".js-sow-submit");
    }

    repaint(plot) {
        this.repaintCapacityBar(plot);
        this.repaintFooter(plot);
        this.el.querySelector(".js-item-sliders").innerHTML = "";
    }

    repaintCapacityBar(plot) {
        let capacityBar = this.el.querySelector(".js-capacity-bar");

        capacityBar.querySelector(".progress-bar").style.width = (plot.capacityUsed / plot.capacityTotal * 100) + "%";
        this.el.querySelector(".js-capacity-used").innerText = plot.capacityUsed;
        this.el.querySelector(".js-capacity-total").innerText = plot.capacityTotal;

        if (plot.capacityUsed > plot.capacityTotal) {
            this.el.querySelector(".progress-bar").classList.add("bg-danger");
        } else {
            this.el.querySelector(".progress-bar").classList.remove("bg-danger");
        }
    }

    repaintFooter(plot) {
        if (plot.capacityUsed > plot.capacityTotal
            || plot.capacityUsed === 0
        ) {
            this.el.querySelector(".js-sow-submit").setAttribute("disabled", "disabled");
        } else {
            this.el.querySelector(".js-sow-submit").removeAttribute("disabled");
        }
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
