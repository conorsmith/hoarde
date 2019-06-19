class Plot {
    constructor(capacityUsed, capacityTotal) {
        this.capacityUsedInitially = capacityUsed;
        this.capacityUsed = capacityUsed;
        this.capacityTotal = capacityTotal;
        this.items = {};
    }

    add(item) {
        const model = this;

        this.items[item.inventoryItem.id] = item;

        this.capacityUsed = this.capacityUsedInitially;

        Object.values(this.items).forEach(function (item) {
            model.capacityUsed += item.quantity;
        });
    }

    getContents() {
        let contents = [];

        Object.values(this.items).forEach(function (item) {
            if (item.quantity > 0) {
                contents.push({
                    varietyId: item.inventoryItem.id,
                    quantity: item.quantity
                });
            }
        });

        return contents;
    }
}
