class TransferModal {
    constructor(el) {
        this.el = el;

        this.itemSliders = Array.from(this.el.querySelectorAll("input[type='range']")).map(function (input) {
            return new TransferItemSlider(input);
        });

        this.itemCounters = Array.from(this.el.querySelectorAll(".js-item-counter")).map(function (counter) {
            return new TransferItemCounter(counter);
        });

        this.submitButton = new TransferSubmitButton(
            this.el.querySelector(".js-submit")
        )
    }
}
