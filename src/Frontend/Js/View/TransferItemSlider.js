class TransferItemSlider {
    static fromTemplate(el, template, popoverTemplate, item, initialQuantity, transferItem) {
        const popoverRenderer = document.createElement("div");
        const itemSliderDatalistId = "item-slider-" + item.entityId + "-" + item.varietyId;

        popoverTemplate.querySelector(".tmpl-description").innerText = item.description;
        popoverTemplate.querySelector(".tmpl-weight").innerText = item.weight > 1000
            ? (item.weight / 1000) + " kg"
            : item.weight + " g";
        popoverTemplate.querySelector(".tmpl-resources").innerText = item.resourceLabel;
        popoverRenderer.appendChild(popoverTemplate);

        template.querySelector(".tmpl-label").innerText = item.label;

        template.querySelector(".tmpl-icon").classList.add("fa-" + item.icon);
        template.querySelector(".tmpl-icon").title = item.label;
        template.querySelector(".tmpl-icon").dataset.content = popoverRenderer.innerHTML;

        template.querySelector("input[type='range']").value = initialQuantity;
        template.querySelector("input[type='range']").max = item.quantity;
        template.querySelector("input[type='range']").setAttribute("list", itemSliderDatalistId);

        template.querySelector("datalist").id = itemSliderDatalistId;

        for (let i = 0; i <= item.quantity; i++) {
            let option = document.createElement("option");
            option.value = i;
            template.querySelector("datalist").appendChild(option);
        }

        el.appendChild(template);

        let itemRangeInput = new TransferItemRangeInput(
            el.lastElementChild.querySelector("input[type='range']")
        );

        let itemCounter = new TransferItemCounter(
            el.lastElementChild.querySelector(".js-item-counter")
        );

        itemCounter.repaint(transferItem);

        return new TransferItemSlider(
            el.lastElementChild,
            itemRangeInput,
            itemCounter
        )
    }

    constructor(el, itemRangeInput, itemCounter) {
        this.el = el;
        this.itemRangeInput = itemRangeInput;
        this.itemCounter = itemCounter;
    }

    repaint() {
        //
    }
}