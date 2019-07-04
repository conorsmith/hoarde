class ScavengeSubmitButton {
    constructor(el, inventory) {
        this.el = el;
        this.inventory = inventory;

        this.original = {
            innerText: this.el.innerText
        }
    }

    attachHaul(haul) {
        this.haul = haul;
        this.el.dataset.haulId = haul.id;
        this.el.dataset.isEmpty = haul.isEmpty();
    }

    repaint() {
        if (this.haul.isOverCapacity()) {
            this.el.setAttribute("disabled", true);
        } else {
            this.el.removeAttribute("disabled");
        }

        if (this.haul.isEmpty()) {
            this.el.innerText = "Oh well...";
        } else if (this.haul.isBeingDiscarded()) {
            this.el.innerText = "Discard Haul";
        } else {
            this.el.innerText = this.original.innerText;
        }
    }
}
