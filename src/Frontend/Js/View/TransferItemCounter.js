class TransferItemCounter {
    constructor(el) {
        this.el = el;
    }

    createModel() {
        return new TransferItem(
            this.el.dataset.entityId,
            this.el.dataset.varietyId,
            parseInt(this.el.innerText, 10)
        )
    }

    repaint(item) {
        this.el.innerText = item.quantity;
    }
}