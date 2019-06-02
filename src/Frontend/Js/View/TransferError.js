class TransferError {
    constructor(el) {
        this.el = el;
    }

    repaint(error) {
        if (error.isEmpty()) {
            this.el.style.display = "none";
        } else {
            this.el.innerText = error.message;
            this.el.style.display = "block";
        }
    }
}
