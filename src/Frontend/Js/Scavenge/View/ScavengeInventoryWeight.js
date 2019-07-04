class ScavengeInventoryWeight {
    constructor(el, inventory) {
        this.el = el;
        this.inventory = inventory;
    }

    repaint() {
        this.el.innerText = this.inventory.weight / 1000;
    }
}
