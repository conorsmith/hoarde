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

        scavengeModalView.el.dispatchEvent(new CustomEvent("inventory.modify", {
            detail: {
                inventory: this,
                modifiedItem: modifiedItem
            }
        }));
    }
}

class Haul {
    constructor(id, items, inventory) {
        this.id = id;
        this.items = items;
        this.isInitiallyEmpty = items.length === 0;
        this.inventory = inventory;

        scavengeModalView.el.dispatchEvent(new CustomEvent("haul.created", {
            detail: {
                id: id,
                weight: this.getWeight(),
                isEmpty: this.items.length === 0,
                isOverCapacity: this.isOverCapacity(),
                items: this.items,
                inventory: this.inventory,
                haul: this
            }
        }));
    }

    modifyItemQuantity(varietyId, newQuantity) {
        for (var i = 0; i < this.items.length; i++) {
            if (varietyId === this.items[i].varietyId) {
                this.items[i].quantity = newQuantity;
            }
        }

        scavengeModalView.el.dispatchEvent(new CustomEvent("haul.modify", {
            detail: {
                newQuantity: newQuantity,
                newWeight: this.getWeight(),
                isOverCapacity: this.isOverCapacity(),
                inventory: this.inventory,
                modifiedItemVarietyId: varietyId
            }
        }));
    }

    getWeight() {
        var weight = 0;

        for (var i = 0; i < this.items.length; i++) {
            weight += this.items[i].weight * this.items[i].quantity;
        }

        return weight;
    }

    isOverCapacity() {
        return this.inventory.weight + this.getWeight() > this.inventory.capacity;
    }

    isEmpty() {
        return this.items.length === 0;
    }

    isBeingDiscarded() {
        for (var i = 0; i < this.items.length; i++) {
            if (this.items[i].quantity > 0) {
                return false;
            }
        }

        return true;
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
        this.el.innerHTML = "";
        this.itemQuantities = [];
        this.itemSliders = [];

        for (var i = 0; i < this.haul.items.length; i++) {

            var item = this.haul.items[i];
            var haul = this.haul;

            const datalistId = "scavange-tickmarks-" + item.varietyId;

            const template = this.itemTemplate.content.cloneNode(true);

            template.querySelector(".tmpl-icon").classList.add("fa-" + item.icon);
            template.querySelector(".tmpl-label").innerText = item.label;

            template.querySelector(".tmpl-quantity").innerText = item.quantity;
            this.itemQuantities.push(new ScavengeItemQuantity(
                template.querySelector(".tmpl-quantity"),
                item
            ));

            template.querySelector("input[type='range']").setAttribute("list", datalistId);
            template.querySelector("input[type='range']").dataset.varietyId = item.varietyId;
            template.querySelector("input[type='range']").dataset.weight = item.weight;
            template.querySelector("input[type='range']").max = item.quantity;
            template.querySelector("input[type='range']").value = item.quantity;
            this.itemSliders.push(new ScavengeItemSlider(
                template.querySelector("input[type='range']"),
                item,
                this.haul
            ));

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

class ScavengeInventory {
    constructor(el, inventory, isShown) {
        this.el = el;
        this.isShown = isShown;

        var itemQuantities = [];
        var itemSliders = [];

        inventory.items.forEach(function (item) {
            itemQuantities.push(new ScavengeItemQuantity(
                el.querySelector(".js-scavenge-inventory-quantity[data-variety-id='" + item.id + "']"),
                item
            ));

            itemSliders.push(new ScavengeItemSlider(
                el.querySelector("input[type='range'][data-variety-id='" + item.id + "']"),
                item,
                inventory
            ));
        });

        this.itemQuantities = itemQuantities;
        this.itemSliders = itemSliders;
    }

    show() {
        this.isShown = true;
    }

    hide() {
        this.isShown = false;
    }

    repaint() {
        if (this.isShown) {
            this.el.style.display = "block";
        } else {
            this.el.style.display = "none";
        }
    }
}

class ScavengeItemQuantity {
    constructor(el, item) {
        this.el = el;
        this.item = item;
    }

    repaint() {
        this.el.innerText = this.item.quantity;
    }
}

class ScavengeItemSlider {
    constructor(el, item, inventory) {
        this.el = el;
        this.item = item;
        this.inventory = inventory;

        this.addEventListeners(this);
    }

    addEventListeners(view) {
        this.el.addEventListener("input", function (e) {
            view.onInput(e);
        })
    }

    onInput(e) {
        this.inventory.modifyItemQuantity(this.item.varietyId, e.target.value);
    }
}

class ScavengeManageInventoryButton {
    constructor(el, inventoryItemsView, isInventoryShown) {
        this.el = el;
        this.inventoryItemsView = inventoryItemsView;
        this.isInventoryShown = isInventoryShown;

        this.addEventListeners(this);
    }

    addEventListeners(view) {
        this.el.addEventListener("click", function (e) {
            e.preventDefault();
            view.onClick(e);
        })
    }

    repaint() {
        if (this.isInventoryShown) {
            this.el.querySelector(".fas").classList.remove("fa-caret-down");
            this.el.querySelector(".fas").classList.add("fa-caret-up");
        } else {
            this.el.querySelector(".fas").classList.remove("fa-caret-up");
            this.el.querySelector(".fas").classList.add("fa-caret-down");
        }
    }

    onClick(e) {
        if (this.isInventoryShown) {
            this.isInventoryShown = false;
            this.inventoryItemsView.hide();
        } else {
            this.isInventoryShown = true;
            this.inventoryItemsView.show();
        }

        this.repaint();
        this.inventoryItemsView.repaint();
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

class ScavengeModal {
    constructor(el, inventory, itemTemplate) {
        this.el = el;

        this.haulItems = new ScavengeHaul(
            this.el.querySelector(".js-scavenge-haul"),
            itemTemplate
        );

        this.inventoryItems = new ScavengeInventory(
            this.el.querySelector(".js-scavenge-inventory-items"),
            inventory,
            false
        );

        this.haulWeight = new ScavengeHaulWeight(
            this.el.querySelector(".js-scavenge-inventory-haul-weight")
        );

        this.haulProgressBar = new ScavengeHaulProgressBar(
            this.el.querySelector(".js-scavenge-inventory-haul-progress"),
            inventory
        );

        this.inventoryProgressBar = new ScavengeInventoryProgressBar(
            this.el.querySelector(".js-scavenge-inventory-progress"),
            inventory
        );

        this.manageInventoryButton = new ScavengeManageInventoryButton(
            this.el.querySelector(".js-scavenge-toggle-inventory"),
            this.inventoryItems,
            false
        );

        this.submitButton = new ScavengeSubmitButton(
            this.el.querySelector(".js-scavenge-submit"),
            inventory
        );

        this.error = new ScavengeError(
            this.el.querySelector(".js-scavenge-error")
        );

        this.addEventListeners(this);
    }

    addEventListeners(view) {
        this.el.addEventListener("haul.created", function (e) {
            view.onHaulCreated(e);
        });

        this.el.addEventListener("haul.modify", function (e) {
            view.onHaulModified(e);
        });

        this.el.addEventListener("inventory.modify", function (e) {
            view.onInventoryModified(e);
        });

        this.el.addEventListener("haul.add", function (e) {
            view.onHaulAdd(e);
        });

        this.el.addEventListener("haul.notAdded", function (e) {
            view.onHaulNotAdded(e);
        });
    }

    onHaulCreated(e) {
        this.haulItems.attachHaul(e.detail.haul);
        this.haulWeight.attachHaul(e.detail.haul);
        this.haulProgressBar.attachHaul(e.detail.haul);
        this.submitButton.attachHaul(e.detail.haul);

        this.submitButton.repaint();
        this.haulItems.repaint();
        this.haulWeight.repaint();
        this.haulProgressBar.repaint();
    }

    onHaulModified(e) {
        this.submitButton.repaint();
        this.haulWeight.repaint();
        this.haulProgressBar.repaint();

        this.haulItems.itemQuantities.forEach(function (quantity) {
            quantity.repaint();
        });
    }

    onInventoryModified(e) {

        this.submitButton.repaint();
        this.haulProgressBar.repaint();
        this.inventoryProgressBar.repaint();

        this.inventoryItems.itemQuantities.forEach(function (quantity) {
            quantity.repaint();
        });
    }

    onHaulAdd(e) {
        this.error.attachMessage();
        this.error.repaint();
    }

    onHaulNotAdded(e) {
        this.error.attachMessage(e.detail.message);
        this.error.repaint();
    }

    findHaulInputs() {
        return this.haulItems.el.querySelectorAll("input[type='range']");
    }

    findInventoryInputs() {
        return this.inventoryItems.el.querySelectorAll("input[type='range']")
    }
}