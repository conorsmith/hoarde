class Transfer {
    constructor(inventoryFrom, inventoryTo) {
        this.inventoryFrom = inventoryFrom;
        this.inventoryTo = inventoryTo;
        this.itemsFrom = [];
        this.itemsTo = [];
    }

    addItem(item) {
        if (this.inventoryFrom.entityId === item.entityId) {
            this.itemsFrom.push(item);

        } else if (this.inventoryTo.entityId === item.entityId) {
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

        return this.inventoryFrom.weight + transferWeight;
    }
}