class TransferEntity {
    static createPair(entityA, entityB) {
        const inventoryA = new TransferInventory(
            entityA.id,
            entityA.inventory.weight,
            entityA.inventory.capacity
        );

        const inventoryB = new TransferInventory(
            entityB.id,
            entityB.inventory.weight,
            entityB.inventory.capacity
        );

        const transferA = new Transfer(inventoryA, inventoryB);
        const transferB = new Transfer(inventoryB, inventoryA);

        [entityA, entityB].forEach(function (entity) {
            entity.inventory.items.forEach(function (item) {
                const transferItem = new TransferItem(
                    entity.id,
                    item.varietyId,
                    item.weight,
                    0,
                    item
                );

                [transferA, transferB].forEach(function (transfer) {
                    transfer.addItem(transferItem);
                });
            });
        });

        return [
            new TransferEntity(
                entityA,
                transferA
            ),
            new TransferEntity(
                entityB,
                transferB
            )
        ];
    }

    constructor(entity, transfer) {
        this.entity = entity;
        this.transfer = transfer;
    }
}
