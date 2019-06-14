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
