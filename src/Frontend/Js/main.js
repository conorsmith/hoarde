
var gameId = document.getElementById("gameId").value;
var useButtons = document.getElementsByClassName("js-use");
var consumeButtons = document.getElementsByClassName("js-consume");

$("#dropModal").on("show.bs.modal", function (e) {
    var button = e.relatedTarget;
    e.target.dataset.itemId = button.dataset.itemId;
    e.target.dataset.entityId = button.dataset.entityId;
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
    var entityId = document.getElementById("dropModal").dataset.entityId;
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

    var itemInput = document.createElement("input");
    itemInput.setAttribute("type", "hidden");
    itemInput.setAttribute("name", "entityId");
    itemInput.setAttribute("value", entityId);
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
        var entityId = e.target.dataset.entityId;

        var form = document.createElement("form");
        form.setAttribute("action", "/" + gameId + "/use");
        form.setAttribute("method", "POST");
        form.setAttribute("hidden", true);

        var itemInput = document.createElement("input");
        itemInput.setAttribute("type", "hidden");
        itemInput.setAttribute("name", "item");
        itemInput.setAttribute("value", itemId);
        form.appendChild(itemInput);

        var entityIdInput = document.createElement("input");
        entityIdInput.setAttribute("type", "hidden");
        entityIdInput.setAttribute("name", "entityId");
        entityIdInput.setAttribute("value", entityId);
        form.appendChild(entityIdInput);

        document.body.appendChild(form);

        form.submit();
    }
}

for (var i = 0; i < consumeButtons.length; i++) {
    consumeButtons[i].onclick = function (e) {
        e.preventDefault();

        const template = document.getElementById("spinner").content.cloneNode(true);
        this.innerText = "";
        this.appendChild(template);

        var resourceId = e.target.dataset.resourceId;
        var entityId = e.target.dataset.entityId;

        var form = document.createElement("form");
        form.setAttribute("action", "/" + gameId + "/consume");
        form.setAttribute("method", "POST");
        form.setAttribute("hidden", true);

        var resourceIdInput = document.createElement("input");
        resourceIdInput.setAttribute("type", "hidden");
        resourceIdInput.setAttribute("name", "resourceId");
        resourceIdInput.setAttribute("value", resourceId);
        form.appendChild(resourceIdInput);

        var entityIdInput = document.createElement("input");
        entityIdInput.setAttribute("type", "hidden");
        entityIdInput.setAttribute("name", "entityId");
        entityIdInput.setAttribute("value", entityId);
        form.appendChild(entityIdInput);

        document.body.appendChild(form);

        form.submit();
    }
}

var eventBus = new EventBus();

new TransferController(
    eventBus,
    new TransferModal(
        document.getElementById("transferModal")
    )
);

var scavengeModal = document.getElementById("scavengeModal");
var scavengeButtons = document.getElementsByClassName("js-scavenge");

var inventory = new Inventory(
    JSON.parse(document.getElementById("inventoryItems").value),
    parseInt(scavengeModal.querySelector(".js-scavenge-inventory").dataset.inventoryCapacity, 10)
);

var scavengeModalView = new ScavengeModal(
    scavengeModal,
    inventory,
    document.getElementById("scavange-item-slider")
);

var haul;

scavengeModal.querySelector(".js-scavenge-discard").onclick = function (e) {
    e.preventDefault();

    var haulInputs = scavengeModalView.findHaulInputs();
    var inventoryInputs = scavengeModalView.findInventoryInputs();

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

    var haulInputs = scavengeModalView.findHaulInputs();
    var inventoryInputs = scavengeModalView.findInventoryInputs();

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

            haul = new Haul(response.haul.id, response.haul.items, inventory);

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
