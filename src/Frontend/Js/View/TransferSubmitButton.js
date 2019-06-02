class TransferSubmitButton {
    constructor(el, transfer) {
        this.el = el;
        this.transfer = transfer;

        this.addEventListeners(this);
    }

    addEventListeners(view) {
        this.el.addEventListener("click", function (e) {
            view.onClick(e);
        });
    }

    onClick(e) {
        this.transfer.execute();
    }
}
