class CapacityBar {
    constructor(entity) {
        this.entity = entity;
    }

    selectEntity(entity) {
        this.entity = entity;
    }

    getFirstSegmentWidth() {
        return this.entity.inventory.weight / this.entity.inventory.capacity * 100;
    }

    getSecondSegmentWidth() {
        return 0;
    }

    isIncreasing() {
        return false;
    }

    isOverCapacity() {
        return false;
    }

    getWeight() {
        return this.entity.inventory.weight / 1000;
    }

    getCapacity() {
        return this.entity.inventory.capacity / 1000;
    }
}
