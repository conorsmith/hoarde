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
