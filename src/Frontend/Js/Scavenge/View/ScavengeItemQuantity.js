class ScavengeItemQuantity {
    constructor(el, item) {
        this.el = el;
        this.item = item;
    }

    repaint() {
        this.el.innerText = this.item.quantity;
    }
}
