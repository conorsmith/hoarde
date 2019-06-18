class Harvest {
    constructor(id, label, icon, weight, quantity, maximumQuantity) {
        this.id = id;
        this.label = label;
        this.icon = icon;
        this.weight = weight;
        this.quantity = quantity;
        this.maximumQuantity = maximumQuantity;
    }

    setQuantity(quantity) {
        this.quantity = quantity;
    }
}
