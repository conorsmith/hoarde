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
