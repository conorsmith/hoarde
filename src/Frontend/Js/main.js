
var gameId = document.getElementById("gameId").value;
var entities = JSON.parse(document.getElementById("entities").value);
var constructions = JSON.parse(document.getElementById("constructions").value);

var useButtons = document.getElementsByClassName("js-use");
var consumeButtons = document.getElementsByClassName("js-consume");
var infoButtons = document.getElementsByClassName("js-info");
var fetchButtons = document.getElementsByClassName("js-fetch");
var constructButtons = document.getElementsByClassName("js-construct");
var constructContinueButtons = document.getElementsByClassName("js-construct-continue");
var sowButtons = document.getElementsByClassName("js-sow");

$(function () {
    $('[data-toggle="popover"]').popover({
        trigger: 'focus',
        html: true
    });

    document.body.addEventListener("click", function (e) {
        document.getElementById("transferModal").querySelectorAll(".dropdown-toggle").forEach(function (dropdownToggle) {
            $(dropdownToggle).dropdown('hide');
        });
    });

    document.getElementById("transferModal").querySelectorAll(".dropdown-toggle").forEach(function (dropdownToggle) {
        dropdownToggle.addEventListener("click", function (e) {
            $(this).dropdown('show');
        });
    });
});

$("#dropModal").on("show.bs.modal", function (e) {
    let button = e.relatedTarget;

    let entity = entities.find(function (entity) {
        return entity.id === button.dataset.entityId;
    });

    let item = entity.inventory.items.find(function (item) {
        return item.varietyId === button.dataset.itemId;
    });

    e.target.dataset.itemId = item.varietyId;
    e.target.dataset.entityId = entity.id;
    e.target.querySelector(".js-drop-title").innerHTML = "Drop " + item.label;
    e.target.querySelector(".js-drop-submit").innerHTML = "Drop 0";
    document.getElementById("js-drop-slider").value = 0;
    document.getElementById("js-drop-slider").max = item.quantity;
    document.getElementById("js-drop-tickmarks").innerHTML = "";
    for (var i = 0; i <= item.quantity; i++) {
        var tickmark = document.createElement("option");
        tickmark.value = i;
        document.getElementById("js-drop-tickmarks").appendChild(tickmark);
    }
});

$("#settingsModal").on("show.bs.modal", function (e) {
    let entity;
    let labelInput = e.target.querySelector("input[name='label']");

    entities.forEach(function (potentialEntity) {
        if (potentialEntity.id === e.relatedTarget.dataset.entityId) {
            entity = potentialEntity;
        }
    });

    e.target.querySelector(".js-settings-title").innerHTML = entity.label + " Settings";
    labelInput.value = entity.label;

    e.target.querySelector(".js-settings-submit").addEventListener("click", function (e) {
        var form = document.createElement("form");
        form.setAttribute("action", "/" + gameId + "/" + entity.id + "/settings");
        form.setAttribute("method", "POST");
        form.setAttribute("hidden", true);

        form.appendChild(labelInput);

        document.body.appendChild(form);

        form.submit();
    });
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
    var itemQuantity = e.currentTarget.dataset.itemQuantity;

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

        var itemId = e.currentTarget.dataset.itemId;
        var entityId = e.currentTarget.dataset.entityId;
        var actionId = e.currentTarget.dataset.actionId;

        var form = document.createElement("form");
        form.setAttribute("action", "/" + gameId + "/" + entityId + "/use");
        form.setAttribute("method", "POST");
        form.setAttribute("hidden", true);

        var itemInput = document.createElement("input");
        itemInput.setAttribute("type", "hidden");
        itemInput.setAttribute("name", "item");
        itemInput.setAttribute("value", itemId);
        form.appendChild(itemInput);

        var actionIdInput = document.createElement("input");
        actionIdInput.setAttribute("type", "hidden");
        actionIdInput.setAttribute("name", "actionId");
        actionIdInput.setAttribute("value", actionId);
        form.appendChild(actionIdInput);

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

        var resourceId = e.currentTarget.dataset.resourceId;
        var entityId = e.currentTarget.dataset.entityId;

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

for (var i = 0; i < infoButtons.length; i++) {
    infoButtons[i].onclick = function (e) {
        e.preventDefault();
        this.parentNode.parentNode.querySelector("i[data-toggle='popover']").focus();
    }
}

for (var i = 0; i < fetchButtons.length; i++) {
    fetchButtons[i].onclick = function (e) {
        e.preventDefault();

        const template = document.getElementById("spinner").content.cloneNode(true);
        this.innerText = "";
        this.appendChild(template);

        var wellId = e.currentTarget.dataset.wellId;
        var entityId = e.currentTarget.dataset.entityId;

        var form = document.createElement("form");
        form.setAttribute("action", "/" + gameId + "/" + entityId + "/fetch-water");
        form.setAttribute("method", "POST");
        form.setAttribute("hidden", true);

        var wellIdInput = document.createElement("input");
        wellIdInput.setAttribute("type", "hidden");
        wellIdInput.setAttribute("name", "wellId");
        wellIdInput.setAttribute("value", wellId);
        form.appendChild(wellIdInput);

        document.body.appendChild(form);

        form.submit();
    }
}

for (var i = 0; i < constructButtons.length; i++) {
    constructButtons[i].onclick = function (e) {
        e.preventDefault();

        document.getElementById("constructModal").dataset.entityId = e.currentTarget.dataset.entityId;
        document.getElementById("constructModal").dataset.toolVarietyId = e.currentTarget.dataset.itemId;
        document.getElementById("constructModal").dataset.actionId = e.currentTarget.dataset.actionId;

        $("#constructModal").modal();
    }
}

for (var i = 0; i < constructContinueButtons.length; i++) {
    constructContinueButtons[i].onclick = function (e) {
        e.preventDefault();

        const template = document.getElementById("spinner").content.cloneNode(true);
        this.innerText = "";
        this.appendChild(template);

        var targetId = e.currentTarget.dataset.targetId;
        var actorId = e.currentTarget.dataset.actorId;
        var constructionVarietyId = e.currentTarget.dataset.constructionVarietyId;

        var form = document.createElement("form");
        form.setAttribute("action", "/" + gameId + "/" + actorId + "/construct/" + targetId);
        form.setAttribute("method", "POST");
        form.setAttribute("hidden", true);

        var constructionVarietyIdInput = document.createElement("input");
        constructionVarietyIdInput.setAttribute("type", "hidden");
        constructionVarietyIdInput.setAttribute("name", "constructionVarietyId");
        constructionVarietyIdInput.setAttribute("value", constructionVarietyId);
        form.appendChild(constructionVarietyIdInput);

        document.body.appendChild(form);

        form.submit();
    }
}

for (var i = 0; i < sowButtons.length; i++) {
    sowButtons[i].onclick = function (e) {
        e.preventDefault();

        document.getElementById("sowModal").dataset.entityId = e.currentTarget.dataset.entityId;
        document.getElementById("sowModal").dataset.actorId = e.currentTarget.dataset.actorId;

        $("#sowModal").modal();
    }
}

var eventBus = new EventBus();

import {TransferController, TransferModalView} from "./transfer.js";
import {MainController as ConstructController, ModalView as ConstructModalView} from "./construct.js";
import {MainController as SowController, ModalView as SowModalView} from "./sow.js";

new TransferController(
    eventBus,
    new TransferModalView(
        document.getElementById("transferModal"),
        document.getElementById("transfer-item-slider"),
        document.getElementById("item-popover")
    ),
    JSON.parse(document.getElementById("entities").value),
    gameId
);

new ConstructController(
    eventBus,
    new ConstructModalView(
        document.getElementById("constructModal"),
        document.getElementById("construction-card")
    ),
    JSON.parse(document.getElementById("entities").value),
    JSON.parse(document.getElementById("constructions").value),
    JSON.parse(document.getElementById("actions").value),
    gameId
);

new SowController(
    eventBus,
    new SowModalView(
        document.getElementById("sowModal"),
        document.getElementById("transfer-item-slider")
    ),
    JSON.parse(document.getElementById("entities").value),
    gameId
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

    api.addHaul(
        scavengeModal.querySelector(".js-scavenge-submit").dataset.entityId,
        scavengeModal.querySelector(".js-scavenge-submit").dataset.haulId,
        haulInputs,
        inventoryInputs
    );
};

scavengeModal.querySelector(".js-scavenge-submit").onclick = function (e) {
    if (this.dataset.isEmpty === "true") {
        window.location.reload();
        return;
    }

    const template = document.getElementById("spinner").content.cloneNode(true);
    this.innerText = "";
    this.appendChild(template);

    var haulInputs = scavengeModalView.findHaulInputs();
    var inventoryInputs = scavengeModalView.findInventoryInputs();

    api.addHaul(this.dataset.entityId, this.dataset.haulId, haulInputs, inventoryInputs);
};

var api = {
    addHaul: function (entityId, haulId, haulInputs, inventoryInputs) {
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

        xhr.open("POST", "/" + gameId + "/" + entityId + "/scavenge/" + haulId);
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

            document.getElementById("scavengeModal")
                .querySelector(".js-scavenge-submit").dataset.entityId = button.dataset.entityId;

            $("#scavengeModal").modal('show');

            button.innerText = button.dataset.buttonText;
        };

        xhr.open("POST", "/" + gameId + "/" + button.dataset.entityId + "/scavenge");
        xhr.send(JSON.stringify({
            length: button.dataset.length
        }));
    }
}
