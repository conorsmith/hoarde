class TransferEntityView {
    constructor(el, itemSliderTemplate, itemPopoverTemplate) {
        this.el = el;
        this.itemSliderTemplate = itemSliderTemplate;
        this.itemPopoverTemplate = itemPopoverTemplate;

        this.entitySelectorView = new TransferEntitySelectorView(this.el.querySelector(".js-entity-selector"))
        this.inventoryWeightView = new TransferInventoryWeightView(this.el.querySelector(".js-inventory-weight"));
        this.capacityBarView = new TransferCapacityBarView(this.el.querySelector(".js-capacity-bar"));
    }

    repaint(entity) {
        if (this.previousIcon !== undefined) {
            this.el.querySelector(".js-icon").classList.remove("fa-" + this.previousIcon);
        }
        this.el.querySelector(".js-icon").classList.add("fa-" + entity.icon);
        this.previousIcon = entity.icon;

        this.el.querySelector(".js-label").innerText = entity.label;
        this.el.querySelector(".js-inventory-weight").innerText = entity.inventory.weight / 1000;
        this.el.querySelector(".js-inventory-capacity").innerText = entity.inventory.capacity / 1000;

        let capacityBar = this.el.querySelector(".js-capacity-bar");

        let capacityBarPrimary = capacityBar.querySelectorAll(".progress-bar")[0];
        let capacityBarSecondary = capacityBar.querySelectorAll(".progress-bar")[1];

        if (entity.inventory.weight < entity.inventory.capacity) {
            capacityBarPrimary.classList.add("bg-primary");
        } else {
            capacityBarPrimary.classList.add("bg-danger");
        }

        capacityBarPrimary.style.width = (entity.inventory.weight / entity.inventory.capacity * 100) + "%";
        capacityBarSecondary.style.width = 0;

        this.el.querySelector(".js-item-sliders").innerHTML = "";
    }
}
