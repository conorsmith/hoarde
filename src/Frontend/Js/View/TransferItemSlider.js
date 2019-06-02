class TransferItemSlider {
    constructor(el) {
        this.el = el;
    }

    createModel() {
        return new TransferItem(
            this.el.dataset.entityId,
            this.el.dataset.varietyId,
            parseInt(this.el.dataset.weight, 10),
            parseInt(this.el.value, 10)
        )
    }
}