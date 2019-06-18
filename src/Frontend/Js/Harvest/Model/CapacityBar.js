class CapacityBar {
    constructor(entity, harvest) {
        this.entity = entity;
        this.harvest = harvest;
    }

    selectEntity(entity) {
        this.entity = entity;
    }

    getFirstSegmentWidth() {
        return this.entity.inventory.weight / this.entity.inventory.capacity * 100;
    }

    getSecondSegmentWidth() {
        if (this.isOverCapacity()) {
            return 100 - this.getFirstSegmentWidth();
        } else {
            return this.getHarvestWeight() / this.entity.inventory.capacity * 100;
        }
    }

    isIncreasing() {
        return true;
    }

    isOverCapacity() {
        return this.entity.inventory.weight + this.getHarvestWeight() > this.entity.inventory.capacity;
    }

    getWeight() {
        return (this.entity.inventory.weight / 1000) + (this.getHarvestWeight() / 1000);
    }

    getCapacity() {
        return this.entity.inventory.capacity / 1000;
    }

    getHarvestWeight() {
        return this.harvest.weight * this.harvest.quantity;
    }
}
