class EntitySelectorView {
    constructor(el) {
        this.el = el;
    }

    getOptions() {
        return this.el.querySelectorAll(".dropdown-item");
    }

    repaint(entitySelector) {
        const view = this;
        view.el.innerHTML = "";

        const toggle = view.el.previousElementSibling;
        toggle.innerHTML = "";

        let icon = document.createElement("i");
        icon.classList.add("fas");
        icon.classList.add("fa-fw");
        icon.classList.add("fa-" + entitySelector.selectedEntity.icon);

        let label = document.createElement("span");
        label.classList.add("js-label");
        label.innerText = entitySelector.selectedEntity.label;

        toggle.appendChild(icon);
        toggle.appendChild(document.createTextNode(" "));
        toggle.appendChild(label);

        entitySelector.entities.forEach(function (entity) {
            if (entity.inventory !== undefined) {
                let menuItem = document.createElement("a");

                menuItem.href = "#";
                menuItem.classList.add("dropdown-item");
                if (entity.id === entitySelector.selectedEntity.id) {
                    menuItem.classList.add("active");
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