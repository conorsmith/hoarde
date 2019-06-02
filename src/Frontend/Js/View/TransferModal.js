class TransferModal {
    constructor(el, transfer) {
        this.el = el;

        this.submitButton = new TransferSubmitButton(
            this.el.querySelector(".js-submit"),
            transfer
        )
    }
}
