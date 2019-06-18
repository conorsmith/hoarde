class CapacityCounterView {
    constructor(el) {
        this.el = el;
    }

    repaint(capacityBar) {
        this.el.querySelector(".js-inventory-weight").innerText = capacityBar.getWeight();
        this.el.querySelector(".js-inventory-capacity").innerText = capacityBar.getCapacity();
    }
}
