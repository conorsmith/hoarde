class InventoryWeightView {
    constructor(el) {
        this.el = el;
    }

    repaint(weight) {
        this.el.innerText = weight / 1000;
    }
}