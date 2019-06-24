class ItemCounterView {
    constructor(el) {
        this.el = el;
    }

    repaint(item) {
        this.el.innerText = item.quantity;
    }
}