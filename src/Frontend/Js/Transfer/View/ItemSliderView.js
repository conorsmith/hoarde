class ItemSliderView {
    static fromTemplate(el, template, popoverTemplate, initialQuantity, transferItem) {
        const popoverRenderer = document.createElement("div");
        const itemSliderDatalistId = "item-slider-" + transferItem.entityId + "-" + transferItem.item.varietyId;

        popoverTemplate.querySelector(".tmpl-description").innerHTML = transferItem.item.description;
        popoverTemplate.querySelector(".tmpl-weight").innerText = transferItem.item.weight > 1000
            ? (transferItem.item.weight / 1000) + " kg"
            : transferItem.item.weight + " g";
        popoverTemplate.querySelector(".tmpl-resources").innerText = transferItem.item.resourceLabel;
        popoverRenderer.appendChild(popoverTemplate);

        template.querySelector(".tmpl-label").innerText = transferItem.item.label;

        template.querySelector(".tmpl-icon").classList.add("fa-" + transferItem.item.icon);
        template.querySelector(".tmpl-icon").title = transferItem.item.label;
        template.querySelector(".tmpl-icon").dataset.content = popoverRenderer.innerHTML;

        template.querySelector("input[type='range']").value = initialQuantity;
        template.querySelector("input[type='range']").max = transferItem.item.quantity;
        template.querySelector("input[type='range']").setAttribute("list", itemSliderDatalistId);

        template.querySelector("datalist").id = itemSliderDatalistId;

        for (let i = 0; i <= transferItem.item.quantity; i++) {
            let option = document.createElement("option");
            option.value = i;
            template.querySelector("datalist").appendChild(option);
        }

        el.appendChild(template);

        let itemRangeInputView = new ItemRangeInputView(
            el.lastElementChild.querySelector("input[type='range']")
        );

        let itemCounterView = new ItemCounterView(
            el.lastElementChild.querySelector(".js-item-counter")
        );

        itemCounterView.repaint(transferItem);

        return new ItemSliderView(
            el.lastElementChild,
            itemRangeInputView,
            itemCounterView
        )
    }

    constructor(el, itemRangeInputView, itemCounterView) {
        this.el = el;
        this.itemRangeInputView = itemRangeInputView;
        this.itemCounterView = itemCounterView;
    }

    repaint() {
        //
    }
}