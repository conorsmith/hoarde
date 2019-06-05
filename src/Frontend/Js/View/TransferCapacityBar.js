class TransferCapacityBar {
    constructor(el) {
        this.el = el;
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