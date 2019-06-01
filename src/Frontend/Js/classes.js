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

class ScavengeHaul {
    constructor(el, itemTemplate) {
        this.el = el;
        this.itemTemplate = itemTemplate;
    }

    attachHaul(haul) {
        this.haul = haul;
    }

    repaint() {
        for (var i = 0; i < this.haul.items.length; i++) {

            var item = this.haul.items[i];
            var haul = this.haul;

            const datalistId = "scavange-tickmarks-" + item.varietyId;

            const template = this.itemTemplate.content.cloneNode(true);

            template.querySelector(".tmpl-icon").classList.add("fa-" + item.icon);
            template.querySelector(".tmpl-label").innerText = item.label;

            template.querySelector(".tmpl-quantity").innerText = item.quantity;
            template.querySelector(".tmpl-quantity").item = this.haul.items[i];
            template.querySelector(".tmpl-quantity").handleHaulModified = function (haulModifiedEvent) {
                if (haulModifiedEvent.detail.modifiedItemVarietyId === this.item.varietyId) {
                    this.innerHTML = haulModifiedEvent.detail.newQuantity;
                }
            };

            template.querySelector("input[type='range']").setAttribute("list", datalistId);
            template.querySelector("input[type='range']").dataset.varietyId = item.varietyId;
            template.querySelector("input[type='range']").dataset.weight = item.weight;
            template.querySelector("input[type='range']").max = item.quantity;
            template.querySelector("input[type='range']").value = item.quantity;
            template.querySelector("input[type='range']").addEventListener("input", function (inputEvent) {
                haul.modifyItemQuantity(inputEvent.target.dataset.varietyId, inputEvent.target.value);
            });

            template.querySelector("datalist").id = datalistId;

            for (var t = 0; t <= item.quantity; t++) {
                var tickmark = document.createElement("option");
                tickmark.value = t;
                template.querySelector("datalist").appendChild(tickmark);
            }

            this.el.appendChild(template);
        }
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

class ScavengeInventoryProgressBar {
    constructor(el, inventory) {
        this.el = el;
        this.inventory = inventory;
    }

    repaint() {
        this.el.style.width = (this.inventory.weight / this.inventory.capacity * 100) + "%";
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

class ScavengeError {
    constructor(el) {
        this.el = el;
    }

    attachMessage(message) {
        this.message = message;
    }

    repaint() {
        if (this.message === undefined) {
            var previousAlert = this.el.querySelector(".alert");

            if (previousAlert) {
                previousAlert.remove();
            }

            return;
        }

        var alert = document.createElement("div");
        alert.classList.add("alert");
        alert.classList.add("alert-danger");
        alert.style.marginBottom = "1rem";
        alert.innerHTML = this.message;

        this.el.appendChild(alert);
    }
}
