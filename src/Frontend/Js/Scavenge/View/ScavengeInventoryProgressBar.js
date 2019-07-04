class ScavengeInventoryProgressBar {
    constructor(el, inventory) {
        this.el = el;
        this.inventory = inventory;
    }

    repaint() {
        this.el.style.width = (this.inventory.weight / this.inventory.capacity * 100) + "%";
    }
}
