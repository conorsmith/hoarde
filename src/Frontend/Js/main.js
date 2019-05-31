var gameId = document.getElementById("gameId").value;
var useButtons = document.getElementsByClassName("js-use");

$("#dropModal").on("show.bs.modal", function (e) {
    var button = e.relatedTarget;
    e.target.dataset.itemId = button.dataset.itemId;
    e.target.querySelector(".js-drop-title").innerHTML = "Drop " + button.dataset.itemLabel;
    e.target.querySelector(".js-drop-submit").innerHTML = "Drop 0";
    document.getElementById("js-drop-slider").value = 0;
    document.getElementById("js-drop-slider").max = button.dataset.itemQuantity;
    document.getElementById("js-drop-tickmarks").innerHTML = "";
    for (var i = 0; i <= button.dataset.itemQuantity; i++) {
        var tickmark = document.createElement("option");
        tickmark.value = i;
        document.getElementById("js-drop-tickmarks").appendChild(tickmark);
    }
});

document.getElementById("js-drop-slider").addEventListener("input", function (e) {
    var submit = document.getElementById("dropModal").querySelector(".js-drop-submit");
    submit.innerHTML = "Drop " + e.target.value;
    submit.dataset.itemQuantity = e.target.value;
});

document.getElementById("dropModal").querySelector(".js-drop-submit").onclick = function (e) {
    e.preventDefault();

    var itemId = document.getElementById("dropModal").dataset.itemId;
    var itemQuantity = e.target.dataset.itemQuantity;

    var form = document.createElement("form");
    form.setAttribute("action", "/" + gameId + "/drop");
    form.setAttribute("method", "POST");
    form.setAttribute("hidden", true);

    var itemInput = document.createElement("input");
    itemInput.setAttribute("type", "hidden");
    itemInput.setAttribute("name", "item");
    itemInput.setAttribute("value", itemId);
    form.appendChild(itemInput);

    var itemInput = document.createElement("input");
    itemInput.setAttribute("type", "hidden");
    itemInput.setAttribute("name", "quantity");
    itemInput.setAttribute("value", itemQuantity);
    form.appendChild(itemInput);

    document.body.appendChild(form);

    form.submit();
};

for (var i = 0; i < useButtons.length; i++) {
    useButtons[i].onclick = function (e) {
        e.preventDefault();

        const template = document.getElementById("spinner").content.cloneNode(true);
        this.innerText = "";
        this.appendChild(template);

        var itemId = e.target.dataset.itemId;

        var form = document.createElement("form");
        form.setAttribute("action", "/" + gameId + "/use");
        form.setAttribute("method", "POST");
        form.setAttribute("hidden", true);

        var itemInput = document.createElement("input");
        itemInput.setAttribute("type", "hidden");
        itemInput.setAttribute("name", "item");
        itemInput.setAttribute("value", itemId);

        form.appendChild(itemInput);

        document.body.appendChild(form);

        form.submit();
    }
}

var scavengeModal = document.getElementById("scavengeModal");
var scavengeButtons = document.getElementsByClassName("js-scavenge");

var haul;

var inventory = {
    items: JSON.parse('<?=json_encode($inventory)?>'),
    weight: parseInt(scavengeModal.querySelector(".js-scavenge-inventory").dataset.inventoryWeight, 10),
    capacity: parseInt(scavengeModal.querySelector(".js-scavenge-inventory").dataset.inventoryCapacity, 10),
    modifyItemQuantity: function (varietyId, newQuantity) {
        for (var i = 0; i < this.items.length; i++) {
            if (varietyId === this.items[i].id) {
                this.items[i].quantity = newQuantity;
            }
        }

        this.weight = 0;

        for (i = 0; i < this.items.length; i++) {
            this.weight += this.items[i].weight * this.items[i].quantity;
        }

        scavengeModal.dispatchEvent(new CustomEvent("inventory.modify", {
            detail: {
                newQuantity: newQuantity,
                newWeight: this.weight,
                isOverCapacity: this.isOverCapacity(),
                inventory: this,
                modifiedItemVarietyId: varietyId,
                haul: haul
            }
        }));
    },
    isOverCapacity: function () {
        return this.weight + haul.getWeight() > this.capacity;
    }
};

var createHaul = function (response) {
    var haul = {
        items: response.haul.items,
        inventory: inventory,
        modifyItemQuantity: function (varietyId, newQuantity) {
            for (var i = 0; i < this.items.length; i++) {
                if (varietyId === this.items[i].varietyId) {
                    this.items[i].quantity = newQuantity;
                }
            }

            scavengeModal.dispatchEvent(new CustomEvent("haul.modify", {
                detail: {
                    newQuantity: newQuantity,
                    newWeight: this.getWeight(),
                    isOverCapacity: this.isOverCapacity(),
                    inventory: this.inventory,
                    modifiedItemVarietyId: varietyId
                }
            }));
        },
        getWeight: function () {
            var weight = 0;

            for (var i = 0; i < this.items.length; i++) {
                weight += this.items[i].weight * this.items[i].quantity;
            }

            return weight;
        },
        isOverCapacity: function () {
            return this.inventory.weight + this.getWeight() > this.inventory.capacity;
        },
        isEmpty: function () {
            return this.items.length === 0;
        }
    };

    scavengeModal.dispatchEvent(new CustomEvent("haul.created", {
        detail: {
            id: response.haul.id,
            weight: haul.getWeight(),
            isEmpty: haul.items.length === 0,
            isOverCapacity: haul.isOverCapacity(),
            items: haul.items,
            inventory: haul.inventory,
            haul: haul
        }
    }));

    return haul;
};

scavengeModal.querySelectorAll(".js-scavenge-inventory-items .js-scavenge-inventory-quantity").forEach(function (quantity) {
    quantity.handleInventoryModified = function (e) {
        if (e.detail.modifiedItemVarietyId === this.dataset.varietyId) {
            this.innerHTML = e.detail.newQuantity;
        }
    };
})

scavengeModal.querySelectorAll(".js-scavenge-inventory-items input[type='range']").forEach(function (input) {
    input.addEventListener("input", function (e) {
        inventory.modifyItemQuantity(e.target.dataset.varietyId, e.target.value);
    });
});

scavengeModal.querySelector(".js-scavenge-inventory").findInputs = function () {
    return this.querySelectorAll("input[type='range']");
};

scavengeModal.addEventListener("inventory.modify", function (e) {
    this.querySelector(".js-scavenge-submit").handleInventoryModified(e);
    this.querySelector(".js-scavenge-inventory-progress").handleInventoryModified(e);
    this.querySelector(".js-scavenge-inventory-haul-progress").handleInventoryModified(e);
    this.querySelectorAll(".js-scavenge-inventory-quantity").forEach(function (quantity) {
        quantity.handleInventoryModified(e);
    })
});

scavengeModal.addEventListener("haul.created", function (e) {
    this.querySelector(".js-scavenge-submit").handleHaulCreated(e);
    this.querySelector(".js-scavenge-haul").handleHaulCreated(e);
    this.querySelector(".js-scavenge-inventory").handelHaulCreated(e);
    this.querySelector(".js-scavenge-inventory-haul-weight").handleHaulCreated(e);
    this.querySelector(".js-scavenge-inventory-haul-progress").handleHaulCreated(e);
});

scavengeModal.addEventListener("haul.modify", function (e) {
    this.querySelector(".js-scavenge-submit").handleHaulModified(e);
    this.querySelector(".js-scavenge-inventory-haul-weight").handleHaulModified(e);
    this.querySelector(".js-scavenge-inventory-haul-progress").handleHaulModified(e);
    this.querySelectorAll(".js-scavange-quantity").forEach(function (quantity) {
        quantity.handleHaulModified(e);
    })
});

scavengeModal.addEventListener("haul.add", function (e) {
    this.querySelector(".js-scavenge-haul").handleHaulAdd(e);
});

scavengeModal.addEventListener("haul.notAdded", function (e) {
    this.querySelector(".js-scavenge-haul").handleHaulNotAdded(e);
});

scavengeModal.querySelector(".js-scavenge-submit").handleInventoryModified = function (e) {
    if (e.detail.isOverCapacity) {
        this.setAttribute("disabled", true);
    } else {
        this.removeAttribute("disabled");
    }
};

scavengeModal.querySelector(".js-scavenge-submit").handleHaulCreated = function (e) {
    this.dataset.haulId = e.detail.id;
    this.dataset.isEmpty = e.detail.isEmpty;

    if (e.detail.isEmpty) {
        this.innerHTML = "Oh well...";

    } else if (e.detail.isOverCapacity) {
        this.setAttribute("disabled", true);
    }
};

scavengeModal.querySelector(".js-scavenge-submit").handleHaulModified = function (e) {
    if (e.detail.newWeight === 0) {
        this.innerHTML = "Discard Haul";
    } else {
        this.innerHTML = "Add to Inventory";
    }

    if (e.detail.isOverCapacity) {
        this.setAttribute("disabled", true);
    } else {
        this.removeAttribute("disabled");
    }
};

scavengeModal.querySelector(".js-scavenge-discard").onclick = function (e) {
    e.preventDefault();

    var haulInputs = scavengeModal.querySelector(".js-scavenge-haul").findInputs();
    var inventoryInputs = scavengeModal.querySelector(".js-scavenge-inventory").findInputs();

    for (var i = 0; i < haulInputs.length; i++) {
        haulInputs[i].value = 0;
        haul.modifyItemQuantity(haulInputs[i].dataset.varietyId, 0);
    }

    api.addHaul(scavengeModal.querySelector(".js-scavenge-submit").dataset.haulId, haulInputs, inventoryInputs);
};

scavengeModal.querySelector(".js-scavenge-submit").onclick = function (e) {
    if (this.dataset.isEmpty === "true") {
        window.location.reload();
        return;
    }

    var haulInputs = scavengeModal.querySelector(".js-scavenge-haul").findInputs();
    var inventoryInputs = scavengeModal.querySelector(".js-scavenge-inventory").findInputs();

    api.addHaul(this.dataset.haulId, haulInputs, inventoryInputs);
};

var api = {
    addHaul: function (haulId, haulInputs, inventoryInputs) {
        var body = {};
        body.selectedItems = {};
        body.modifiedInventory = {};

        for (var i = 0; i < haulInputs.length; i++) {
            body.selectedItems[haulInputs[i].dataset.varietyId] = parseInt(haulInputs[i].value, 10);
        }

        for (i = 0; i < inventoryInputs.length; i++) {
            body.modifiedInventory[inventoryInputs[i].dataset.varietyId] = parseInt(inventoryInputs[i].value, 10);
        }

        var xhr = new XMLHttpRequest();

        xhr.onload = function () {
            if (this.responseText === "") {
                window.location.reload();
            } else {
                scavengeModal.dispatchEvent(new CustomEvent("haul.notAdded", {
                    detail: {
                        message: this.responseText
                    }
                }));
            }
        };

        xhr.open("POST", "/" + gameId + "/scavenge/" + haulId);
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.send(JSON.stringify(body));

        scavengeModal.dispatchEvent(new CustomEvent("haul.add"));
    }
};

scavengeModal.querySelector(".js-scavenge-haul").handleHaulCreated = function (e) {
    for (var i = 0; i < e.detail.items.length; i++) {

        var item = e.detail.items[i];

        const datalistId = "scavange-tickmarks-" + item.varietyId;

        const template = document.getElementById("scavange-item-slider").content.cloneNode(true);

        template.querySelector(".tmpl-icon").classList.add("fa-" + item.icon);
        template.querySelector(".tmpl-label").innerText = item.label;

        template.querySelector(".tmpl-quantity").innerText = item.quantity;
        template.querySelector(".tmpl-quantity").item = e.detail.items[i];
        template.querySelector(".tmpl-quantity").handleHaulModified = function (haulModifiedEvent) {
            if (haulModifiedEvent.detail.modifiedItemVarietyId === this.item.varietyId) {
                this.innerHTML = haulModifiedEvent.detail.newQuantity;
            }
        };

        template.querySelector("input[type='range']").setAttribute("list", datalistId);
        template.querySelector("input[type='range']").dataset.varietyId = item.varietyId;
        template.querySelector("input[type='range']").dataset.weight = item.weight;
        template.querySelector("input[type='range']").max = item.quantity;
        template.querySelector("input[type='range']").value = item.quantity;
        template.querySelector("input[type='range']").addEventListener("input", function (inputEvent) {
            e.detail.haul.modifyItemQuantity(inputEvent.target.dataset.varietyId, inputEvent.target.value);
        });

        template.querySelector("datalist").id = datalistId;

        for (var t = 0; t <= item.quantity; t++) {
            var tickmark = document.createElement("option");
            tickmark.value = t;
            template.querySelector("datalist").appendChild(tickmark);
        }

        this.appendChild(template);
    }
};

scavengeModal.querySelector(".js-scavenge-haul").handleHaulAdd = function (e) {
    var previousAlert = this.querySelector(".alert");

    if (previousAlert) {
        previousAlert.remove();
    }
};

scavengeModal.querySelector(".js-scavenge-haul").handleHaulNotAdded = function (e) {
    var alert = document.createElement("div");
    alert.classList.add("alert");
    alert.classList.add("alert-danger");
    alert.innerHTML = e.detail.message;

    this.appendChild(alert);
};

scavengeModal.querySelector(".js-scavenge-haul").findInputs = function () {
    return this.querySelectorAll("input[type='range']");
};

scavengeModal.querySelector(".js-scavenge-inventory").handelHaulCreated = function (e) {
    if (!e.detail.isEmpty) {
        this.style.display = "block";
    }
};

scavengeModal.querySelector(".js-scavenge-inventory-haul-weight").handleHaulCreated = function (e) {
    if (e.detail.weight < 100 && e.detail.weight > 0) {
        this.innerHTML = "+" + (e.detail.weight) + " g";
    } else {
        this.innerHTML = "+" + (e.detail.weight / 1000) + " kg";
    }
};

scavengeModal.querySelector(".js-scavenge-inventory-haul-weight").handleHaulModified = function (e) {
    if (e.detail.newWeight < 100 && e.detail.newWeight > 0) {
        this.innerHTML = "+" + (e.detail.newWeight) + " g";
    } else {
        this.innerHTML = "+" + (e.detail.newWeight / 1000) + " kg";
    }
};

scavengeModal.querySelector(".js-scavenge-inventory-progress").handleInventoryModified = function (e) {
    this.style.width = (e.detail.inventory.weight / e.detail.inventory.capacity * 100) + "%";
};

scavengeModal.querySelector(".js-scavenge-inventory-haul-progress").handleInventoryModified = function (e) {
    if (e.detail.isOverCapacity) {
        this.classList.remove("bg-success");
        this.classList.add("bg-danger");
        this.style.width = (100 - (e.detail.inventory.weight / e.detail.inventory.capacity * 100)) + "%";
    } else {
        this.classList.remove("bg-danger");
        this.classList.add("bg-success");
        this.style.width = (e.detail.haul.getWeight() / e.detail.inventory.capacity * 100) + "%";
    }
};

scavengeModal.querySelector(".js-scavenge-inventory-haul-progress").handleHaulCreated = function (e) {
    if (e.detail.isOverCapacity) {
        this.classList.add("bg-danger");
        this.style.width = (100 - (e.detail.inventory.weight / e.detail.inventory.capacity * 100)) + "%";
    } else {
        this.classList.add("bg-success");
        this.style.width = (e.detail.weight / e.detail.inventory.capacity * 100) + "%";
    }
};

scavengeModal.querySelector(".js-scavenge-inventory-haul-progress").handleHaulModified = function (e) {
    if (e.detail.isOverCapacity) {
        this.classList.remove("bg-success");
        this.classList.add("bg-danger");
        this.style.width = (100 - (e.detail.inventory.weight / e.detail.inventory.capacity * 100)) + "%";
    } else {
        this.classList.remove("bg-danger");
        this.classList.add("bg-success");
        this.style.width = (e.detail.newWeight / e.detail.inventory.capacity * 100) + "%";
    }
};

scavengeModal.querySelector(".js-scavenge-toggle-inventory").onclick = function (e) {
    e.preventDefault();
    if (this.dataset.isShown === "1") {
        scavengeModal.querySelector(".js-scavenge-inventory-items").style.display = "none";
        this.querySelector(".fas").classList.remove("fa-caret-up");
        this.querySelector(".fas").classList.add("fa-caret-down");
        this.dataset.isShown = "0";
    } else {
        scavengeModal.querySelector(".js-scavenge-inventory-items").style.display = "block";
        this.querySelector(".fas").classList.remove("fa-caret-down");
        this.querySelector(".fas").classList.add("fa-caret-up");
        this.dataset.isShown = "1";
    }
};

for (var i = 0; i < scavengeButtons.length; i++) {
    scavengeButtons[i].onclick = function (e) {
        e.preventDefault();
        const button = this;

        const template = document.getElementById("spinner").content.cloneNode(true);
        this.dataset.buttonText = this.innerText;
        this.innerText = "";
        this.appendChild(template);

        var xhr = new XMLHttpRequest();

        xhr.onload = function () {
            var response = JSON.parse(this.response);
            haul = createHaul(response);

            if (haul.isEmpty()) {
                window.location.reload();
                return;
            }

            $("#scavengeModal").modal('show');

            button.innerText = button.dataset.buttonText;
        };

        xhr.open("POST", "/" + gameId + "/scavenge");
        xhr.send();
    }
}

document.querySelector(".js-wait").onclick = function () {
    const template = document.getElementById("spinner").content.cloneNode(true);
    this.innerText = "";
    this.appendChild(template);
};
