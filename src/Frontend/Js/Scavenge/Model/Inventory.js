class Inventory {
    constructor(items, capacity, scavengeModal) {
        this.items = items;
        this.capacity = capacity;
        this.weight = this.calculateWeight();
        this.scavengeModal = scavengeModal;
    }

    calculateWeight() {
        var weight = 0;

        for (var i = 0; i < this.items.length; i++) {
            weight += this.items[i].weight * this.items[i].quantity;
        }

        return weight;
    }

    modifyItemQuantity(varietyId, newQuantity) {
        var modifiedItem;

        for (var i = 0; i < this.items.length; i++) {
            if (varietyId === this.items[i].id) {
                this.items[i].quantity = newQuantity;
                modifiedItem = this.items[i];
            }
        }

        this.weight = this.calculateWeight();

        this.scavengeModal.dispatchEvent(new CustomEvent("inventory.modify", {
            detail: {
                inventory: this,
                modifiedItem: modifiedItem
            }
        }));
    }
}
