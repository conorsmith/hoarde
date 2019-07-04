class ScavengeModal {
    constructor(el, inventory, itemTemplate, itemPopoverTemplate) {
        this.el = el;

        this.haulItems = new ScavengeHaul(
            this.el.querySelector(".js-scavenge-haul"),
            itemTemplate,
            itemPopoverTemplate
        );

        this.inventoryItems = new ScavengeInventory(
            this.el.querySelector(".js-scavenge-inventory-items"),
            inventory,
            false
        );

        this.haulWeight = new ScavengeHaulWeight(
            this.el.querySelector(".js-scavenge-inventory-haul-weight")
        );

        this.inventoryWeight = new ScavengeInventoryWeight(
            this.el.querySelector(".js-scavenge-inventory-weight"),
            inventory
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
        this.inventoryWeight.repaint();

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
