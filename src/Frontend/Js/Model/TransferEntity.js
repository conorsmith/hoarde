class TransferEntity {
    static createPair(entityA, entityB) {
        const transferA = new Transfer(entityA, entityB);
        const transferB = new Transfer(entityB, entityA);

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
