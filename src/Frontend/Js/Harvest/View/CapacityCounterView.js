class CapacityCounterView {
    constructor(el) {
        this.el = el;
    }

    repaint(capacityBar) {
        this.el.querySelector(".js-inventory-weight").innerText = capacityBar.getWeight().toLocaleString("en-IE", {
            maximumFractionDigits: 3
        });
        this.el.querySelector(".js-inventory-capacity").innerText = capacityBar.getCapacity();
    }
}
