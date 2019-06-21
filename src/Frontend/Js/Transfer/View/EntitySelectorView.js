class EntitySelectorView {
    constructor(el) {
        this.el = el;
    }

    getOptions() {
        return this.el.querySelectorAll(".dropdown-item");
    }

    repaint(entities, selectedEntity, otherEntity) {
        const view = this;

        view.el.innerHTML = "";

        entities.forEach(function (entity) {
            if (entity.inventory !== undefined
                && entity.varietyId !== "608e795a-2b62-436b-b636-74fd13ff076d" // Garden Plot
            ) {
                let menuItem = document.createElement("a");

                menuItem.href = "#";
                menuItem.classList.add("dropdown-item");
                if (entity.id === selectedEntity.id) {
                    menuItem.classList.add("active");
                }
                if (entity.id === otherEntity.id) {
                    menuItem.classList.add("disabled");
                }

                menuItem.dataset.entityId = entity.id;

                let icon = document.createElement("i");
                icon.classList.add("fas");
                icon.classList.add("fa-fw");
                icon.classList.add("fa-" + entity.icon);

                let label = document.createTextNode(" " + entity.label);

                menuItem.appendChild(icon);
                menuItem.appendChild(label);

                view.el.appendChild(menuItem);
            }
        });
    }
}
