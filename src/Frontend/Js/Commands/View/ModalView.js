class ModalView {
    constructor(el) {
        this.el = el;
    }

    repaint(entity) {
        this.el.querySelector(".modal-title").innerText = "Command " + entity.label;
    }
}
