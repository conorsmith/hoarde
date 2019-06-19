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
        let staticBar = capacityBar.querySelectorAll(".progress-bar")[0];
        let dynamicBar = capacityBar.querySelectorAll(".progress-bar")[1];

        staticBar.style.width = (plot.capacityUsedInitially / plot.capacityTotal * 100) + "%";

        if (plot.capacityUsed > plot.capacityTotal) {
            dynamicBar.style.width = (100 - (plot.capacityUsedInitially / plot.capacityTotal * 100)) + "%";
            dynamicBar.classList.add("bg-danger");
        } else {
            dynamicBar.style.width = ((plot.capacityUsed - plot.capacityUsedInitially) / plot.capacityTotal * 100) + "%";
            dynamicBar.classList.remove("bg-danger");
        }

        this.el.querySelector(".js-capacity-used").innerText = plot.capacityUsed;
        this.el.querySelector(".js-capacity-total").innerText = plot.capacityTotal;
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
