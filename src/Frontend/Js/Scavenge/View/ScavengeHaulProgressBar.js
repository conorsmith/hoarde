class ScavengeHaulProgressBar {
    constructor(el, inventory) {
        this.el = el;
        this.inventory = inventory;
    }

    attachHaul(haul) {
        this.haul = haul;
    }

    repaint() {
        if (this.haul.isOverCapacity()) {
            this.el.classList.remove("bg-success");
            this.el.classList.add("bg-danger");
            this.el.style.width = (100 - (this.inventory.weight / this.inventory.capacity * 100)) + "%";
        } else {
            this.el.classList.remove("bg-danger");
            this.el.classList.add("bg-success");
            this.el.style.width = (this.haul.getWeight() / this.inventory.capacity * 100) + "%";
        }
    }
}
