class Construction {
    constructor(construction, entity, target, localEntities, gameId) {
        const model = this;

        this.gameId = gameId;
        this.entityId = entity.id;
        this.targetId = target.id;

        this.id = construction.id;
        this.label = construction.label;
        this.icon = construction.icon;
        this.turns = construction.turns;

        let isConstructable = true;

        this.tools = construction.tools.map(function (tool) {
            let toolFromEntitysInventory = entity.inventory.items.find(function (item) {
                return item.id === tool.id;
            });

            if (toolFromEntitysInventory === undefined) {
                isConstructable = false;
            }

            return {
                label: tool.label,
                icon: tool.icon,
                isAvailable: toolFromEntitysInventory !== undefined
            };
        });

        this.materials = construction.materials.map(function (material) {
            let materialsFromEntitysInventory = entity.inventory.items.find(function (item) {
                return item.id === material.id;
            });

            let isAvailable = true;

            if (materialsFromEntitysInventory === undefined
                || materialsFromEntitysInventory.quantity < material.quantity
            ) {
                let totalQuantity = 0;

                if (materialsFromEntitysInventory !== undefined) {
                    totalQuantity += materialsFromEntitysInventory.quantity;
                }

                localEntities.forEach(function (entity) {
                    if (entity.id !== model.entityId
                        && entity.inventory !== undefined
                    ) {
                        entity.inventory.items.forEach(function (item) {
                            if (item.id === material.id) {
                                totalQuantity += item.quantity;
                            }
                        });
                    }
                });

                isAvailable = totalQuantity >= material.quantity;

                if (totalQuantity < material.quantity) {
                    isConstructable = false;
                }
            }

            return {
                label: material.label,
                icon: material.icon,
                quantity: material.quantity,
                isAvailable: isAvailable
            };
        });

        this.isConstructable = isConstructable;
    }
}
