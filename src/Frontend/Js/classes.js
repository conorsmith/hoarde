class Inventory {
    constructor(items, capacity) {
        this.items = items;
        this.capacity = capacity;
        this.weight = this.calculateWeight();
    }

    calculateWeight() {
        var weight = 0;

        for (var i = 0; i < this.items.length; i++) {
            weight += this.items[i].weight * this.items[i].quantity;
        }

        return weight;
    }

    modifyItemQuantity(varietyId, newQuantity) {
        var modifiedItem;

        for (var i = 0; i < this.items.length; i++) {
            if (varietyId === this.items[i].id) {
                this.items[i].quantity = newQuantity;
                modifiedItem = this.items[i];
            }
        }

        this.weight = this.calculateWeight();

        scavengeModal.dispatchEvent(new CustomEvent("inventory.modify", {
            detail: {
                inventory: this,
                modifiedItem: modifiedItem
            }
        }));
    }
}

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