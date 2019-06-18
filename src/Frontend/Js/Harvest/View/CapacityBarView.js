class CapacityBarView {
    constructor(el) {
        this.el = el;
    }

    repaint(capacityBar) {
        const firstSegment = this.el.querySelectorAll(".progress-bar")[0];
        const secondSegment = this.el.querySelectorAll(".progress-bar")[1];

        firstSegment.style.width = capacityBar.getFirstSegmentWidth() + "%";
        secondSegment.style.width = capacityBar.getSecondSegmentWidth() + "%";

        secondSegment.classList.remove("bg-danger");
        secondSegment.classList.remove("bg-info");
        secondSegment.classList.remove("bg-success");

        if (capacityBar.isOverCapacity()) {
            secondSegment.classList.add("bg-danger");
        } else if (capacityBar.isIncreasing()) {
            secondSegment.classList.add("bg-success");
        } else {
            secondSegment.classList.add("bg-info");
        }
    }
}
