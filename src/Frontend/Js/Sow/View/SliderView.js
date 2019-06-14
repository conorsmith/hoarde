class SliderView {
    constructor(el) {
        this.el = el;
    }

    getInputEl() {
        return this.el.querySelector("input[type='range']");
    }

    getInputValue() {
        return this.el.querySelector("input[type='range']").value;
    }

    repaint(item) {
        this.el.querySelector(".tmpl-icon").classList.add("fa-" + item.icon);
        this.el.querySelector(".tmpl-label").innerText = item.label;

        let listId = "sow-slider-" + item.id;


        let input = this.el.querySelector("input[type='range']");
        input.max = item.quantity;
        input.value = 0;
        input.setAttribute("list", listId);

        this.el.querySelector("datalist").id = listId;

        for (let i = 0; i <= item.quantity; i++) {
            let tick = document.createElement("option");
            tick.value = i;
            this.el.querySelector("datalist").appendChild(tick);
        }

        this.el.querySelector(".js-item-counter").innerText = 0;
    }

    repaintCounter() {
        this.el.querySelector(".js-item-counter").innerText = this.el.querySelector("input[type='range']").value;
    }
}
