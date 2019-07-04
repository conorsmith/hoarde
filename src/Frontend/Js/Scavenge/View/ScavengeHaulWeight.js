class ScavengeHaulWeight {
    constructor(el) {
        this.el = el;
    }

    attachHaul(haul) {
        this.haul = haul;
    }

    repaint() {
        if (this.haul.getWeight() < 100 && this.haul.getWeight() > 0) {
            this.el.innerHTML = "+" + (this.haul.getWeight()) + " g";
        } else {
            this.el.innerHTML = "+" + (this.haul.getWeight() / 1000) + " kg";
        }
    }
}
