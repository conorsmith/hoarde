class ConstructionView {
    constructor(el) {
        this.el = el;
    }

    repaint(construction) {
        const controller = this;

        this.el.querySelector(".tmpl-icon").classList.add("fa-" + construction.icon);
        this.el.querySelector(".tmpl-label").innerText = construction.label;
        this.el.querySelector(".tmpl-turns").innerText = construction.turns;

        this.el.querySelector(".tmpl-button-action").innerText = construction.action.label;
        this.el.querySelector(".tmpl-button-label").innerText = construction.label;

        this.el.querySelector(".tmpl-collapse-target").dataset.target = "#collapse-" + construction.id;
        this.el.querySelector(".tmpl-collapse-id").id = "collapse-" + construction.id;

        if (this.el.parentNode.firstElementChild === this.el) {
            this.el.querySelector(".tmpl-collapse-id").classList.add("show");
        }

        construction.tools.forEach(function (tool) {
            let listItem = document.createElement("li");
            listItem.classList.add("list-group-item");
            listItem.classList.add("d-flex");
            listItem.classList.add("align-items-center");
            listItem.classList.add("justify-content-between");

            let labelContainer = document.createElement("div");

            let icon = document.createElement("i");
            icon.classList.add("fas");
            icon.classList.add("fa-fw");
            icon.classList.add("fa-" + tool.icon);

            let label = document.createTextNode(" " + tool.label);

            labelContainer.appendChild(icon);
            labelContainer.appendChild(label);

            let flag = document.createElement("i");
            flag.classList.add("fas");
            flag.classList.add("fa-fw");

            if (tool.isAvailable) {
                flag.classList.add("fa-check-circle");
                flag.classList.add("text-success");
            } else {
                flag.classList.add("fa-times-circle");
                flag.classList.add("text-danger");
            }

            listItem.appendChild(labelContainer);
            listItem.appendChild(flag);

            controller.el.querySelector(".list-group").insertBefore(
                listItem,
                controller.el.querySelector(".tmpl-tools").nextSibling
            );
        });

        construction.materials.forEach(function (material) {
            let listItem = document.createElement("li");
            listItem.classList.add("list-group-item");
            listItem.classList.add("d-flex");
            listItem.classList.add("align-items-center");
            listItem.classList.add("justify-content-between");

            let labelContainer = document.createElement("div");

            let icon = document.createElement("i");
            icon.classList.add("fas");
            icon.classList.add("fa-fw");
            icon.classList.add("fa-" + material.icon);

            let label = document.createTextNode(" " + material.label);

            labelContainer.appendChild(icon);
            labelContainer.appendChild(label);

            let badge = document.createElement("div");
            badge.classList.add("badge");
            badge.innerText = material.quantity;

            if (material.isAvailable) {
                badge.classList.add("badge-success");
            } else {
                badge.classList.add("badge-danger");
            }

            listItem.appendChild(labelContainer);
            listItem.appendChild(badge);

            controller.el.querySelector(".list-group").insertBefore(
                listItem,
                controller.el.querySelector(".tmpl-materials").nextSibling
            );
        });

        if (!construction.isConstructable) {
            this.el.querySelector(".btn-primary").disabled = true;
        }
    }
}
