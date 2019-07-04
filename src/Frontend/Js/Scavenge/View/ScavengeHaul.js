class ScavengeHaul {
    constructor(el, itemTemplate, itemPopoverTemplate) {
        this.el = el;
        this.itemTemplate = itemTemplate;
        this.itemPopoverTemplate = itemPopoverTemplate;
    }

    attachHaul(haul) {
        this.haul = haul;
    }

    repaint() {
        this.el.innerHTML = "";
        this.itemQuantities = [];
        this.itemSliders = [];

        for (var i = 0; i < this.haul.items.length; i++) {

            var item = this.haul.items[i];
            var haul = this.haul;

            const datalistId = "scavange-tickmarks-" + item.varietyId;

            const popoverTemplate = this.itemPopoverTemplate.content.cloneNode(true);
            const popoverRenderer = document.createElement("div");

            popoverTemplate.querySelector(".popover-description").innerHTML = item.description;
            popoverTemplate.querySelector(".popover-weight .popover-value").innerText = item.weight >= 1000
                ? (item.weight / 1000) + " kg"
                : item.weight + " g";
            popoverTemplate.querySelector(".popover-resources .popover-value").innerText = item.resourceLabel;
            popoverRenderer.appendChild(popoverTemplate);
            if (item.resourceLabel === "") {
                popoverRenderer.removeChild(popoverRenderer.querySelector(".popover-resources"));
            }

            const template = this.itemTemplate.content.cloneNode(true);

            template.querySelector(".tmpl-icon").classList.add("fa-" + item.icon);
            template.querySelector(".tmpl-icon").title = item.label;
            template.querySelector(".tmpl-icon").dataset.content = popoverRenderer.innerHTML;

            template.querySelector(".tmpl-label").innerText = item.label;

            template.querySelector(".tmpl-quantity").innerText = item.quantity;
            this.itemQuantities.push(new ScavengeItemQuantity(
                template.querySelector(".tmpl-quantity"),
                item
            ));

            template.querySelector("input[type='range']").setAttribute("list", datalistId);
            template.querySelector("input[type='range']").dataset.varietyId = item.varietyId;
            template.querySelector("input[type='range']").dataset.weight = item.weight;
            template.querySelector("input[type='range']").max = item.quantity;
            template.querySelector("input[type='range']").value = item.quantity;
            this.itemSliders.push(new ScavengeItemSlider(
                template.querySelector("input[type='range']"),
                item,
                this.haul
            ));

            template.querySelector("datalist").id = datalistId;

            for (var t = 0; t <= item.quantity; t++) {
                var tickmark = document.createElement("option");
                tickmark.value = t;
                template.querySelector("datalist").appendChild(tickmark);
            }

            this.el.appendChild(template);
        }

        $(this.el).find(".tmpl-icon").popover({
            trigger: 'focus',
            html: true,
            boundary: 'viewport'
        });
    }
}
