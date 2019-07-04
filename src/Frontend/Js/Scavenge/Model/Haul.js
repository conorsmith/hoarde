class Haul {
    constructor(id, items, inventory, scavengeModalView) {
        this.id = id;
        this.items = items;
        this.isInitiallyEmpty = items.length === 0;
        this.inventory = inventory;
        this.scavengeModalView = scavengeModalView;

        this.scavengeModalView.el.dispatchEvent(new CustomEvent("haul.created", {
            detail: {
                id: id,
                weight: this.getWeight(),
                isEmpty: this.items.length === 0,
                isOverCapacity: this.isOverCapacity(),
                items: this.items,
                inventory: this.inventory,
                haul: this
            }
        }));
    }

    modifyItemQuantity(varietyId, newQuantity) {
        for (var i = 0; i < this.items.length; i++) {
            if (varietyId === this.items[i].varietyId) {
                this.items[i].quantity = newQuantity;
            }
        }

        this.scavengeModalView.el.dispatchEvent(new CustomEvent("haul.modify", {
            detail: {
                newQuantity: newQuantity,
                newWeight: this.getWeight(),
                isOverCapacity: this.isOverCapacity(),
                inventory: this.inventory,
                modifiedItemVarietyId: varietyId
            }
        }));
    }

    getWeight() {
        var weight = 0;

        for (var i = 0; i < this.items.length; i++) {
            weight += this.items[i].weight * this.items[i].quantity;
        }

        return weight;
    }

    isOverCapacity() {
        return this.inventory.weight + this.getWeight() > this.inventory.capacity;
    }

    isEmpty() {
        return this.items.length === 0;
    }

    isBeingDiscarded() {
        for (var i = 0; i < this.items.length; i++) {
            if (this.items[i].quantity > 0) {
                return false;
            }
        }

        return true;
    }
}
