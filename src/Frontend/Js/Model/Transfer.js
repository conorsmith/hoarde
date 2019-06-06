class Transfer {
    constructor(entityFrom, entityTo) {
        this.entityFrom = entityFrom;
        this.entityTo = entityTo;
        this.itemsFrom = [];
        this.itemsTo = [];
    }

    addItem(item) {
        if (this.entityFrom.id === item.entityId) {
            this.itemsFrom.push(item);

        } else if (this.entityTo.id === item.entityId) {
            this.itemsTo.push(item);
        }
    }

    getNewInventoryWeight() {
        let transferWeight = 0;

        this.itemsFrom.forEach(function (item) {
            transferWeight -= item.weight * item.quantity;
        });

        this.itemsTo.forEach(function (item) {
            transferWeight += item.weight * item.quantity;
        });

        return this.entityFrom.inventory.weight + transferWeight;
    }

    getOldInventoryWeight() {
        return this.entityFrom.inventory.weight;
    }

    getInventoryCapacity() {
        return this.entityFrom.inventory.capacity;
    }
}