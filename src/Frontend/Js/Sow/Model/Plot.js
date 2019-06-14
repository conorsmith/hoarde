class Plot {
    constructor(capacityTotal) {
        this.capacityUsed = 0;
        this.capacityTotal = capacityTotal;
        this.items = {};
    }

    add(item) {
        const model = this;

        this.items[item.inventoryItem.id] = item;

        this.capacityUsed = 0;

        Object.values(this.items).forEach(function (item) {
            model.capacityUsed += item.quantity;
        });
    }
}
