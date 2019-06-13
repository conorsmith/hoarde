class Inventory {
    constructor() {
        this.items = {};
    }

    add(item) {
        if (this.items[item.id] === undefined) {
            this.items[item.id] = item;
        } else {
            this.items[item.id].quantity += item.quantity;
        }
    }
}
