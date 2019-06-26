class ModalView {
    constructor(el) {
        this.el = el;

        this.sortable = sortable(".js-sortable", {
            forcePlaceholderSize: true,
            placeholderClass: "list-group-item",
            hoverClass: "list-group-item-secondary"
        })[0];
    }

    getSubmitButtonEl() {
        return this.el.querySelector(".js-sort-submit");
    }

    createOrderManifest() {
        return new OrderManifest(Array.from(this.sortable.querySelectorAll("li"))
            .map(function (el) {
                return el.dataset.entityId;
            }));
    }
}
