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
