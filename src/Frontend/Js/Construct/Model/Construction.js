class Construction {
    constructor(construction, entity, gameId) {
        this.gameId = gameId;
        this.entityId = entity.id;

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

            if (materialsFromEntitysInventory === undefined
                || materialsFromEntitysInventory.quantity < material.quantity
            ) {
                isConstructable = false;
            }

            return {
                label: material.label,
                icon: material.icon,
                quantity: material.quantity,
                isAvailable: materialsFromEntitysInventory !== undefined
                    && materialsFromEntitysInventory.quantity >= material.quantity
            };
        });

        this.isConstructable = isConstructable;
    }
}
