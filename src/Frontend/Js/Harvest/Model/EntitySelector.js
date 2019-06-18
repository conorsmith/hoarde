class EntitySelector {
    constructor(entities, selectedEntity) {
        this.entities = entities;
        this.selectedEntity = selectedEntity;
    }

    select(entityId) {
        this.selectedEntity = this.entities.find(function (entity) {
            return entity.id === entityId;
        });
    }
}
