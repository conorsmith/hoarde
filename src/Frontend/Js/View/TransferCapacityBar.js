class TransferCapacityBar {
    constructor(el) {
        this.el = el;
    }

    createModel() {
        return new TransferInventory(
            this.el.dataset.entityId,
            parseInt(this.el.dataset.weight, 10),
            parseInt(this.el.dataset.capacity, 10)
        );
    }

    repaint(firstSegmentWidth, secondSegmentWidth, isIncreasing, isOverCapacity) {
        const firstSegment = this.el.querySelectorAll(".progress-bar")[0];
        const secondSegment = this.el.querySelectorAll(".progress-bar")[1];

        firstSegment.style.width = firstSegmentWidth + "%";
        secondSegment.style.width = secondSegmentWidth + "%";

        secondSegment.classList.remove("bg-danger");
        secondSegment.classList.remove("bg-info");
        secondSegment.classList.remove("bg-success");

        if (isOverCapacity) {
            secondSegment.classList.add("bg-danger");
        } else if (isIncreasing) {
            secondSegment.classList.add("bg-success");
        } else {
            secondSegment.classList.add("bg-info");
        }
    }
}