
import {EventBus, Form} from "./utility.js";
import {MainController as TransferController, ModalView as TransferModalView} from "./transfer.js";
import {MainController as ConstructController, ModalView as ConstructModalView} from "./construct.js";
import {MainController as SowController, ModalView as SowModalView} from "./sow.js";
import {MainController as HarvestController, ModalView as HarvestModalView} from "./harvest.js";
import {MainController as RepairController, ModalView as RepairModalView} from "./repair.js";
import {MainController as SortController, ModalView as SortModalView} from "./sort.js";
import {ScavengeModal, Haul, Inventory} from "./scavenge.js";

let gameId = document.getElementById("gameId").value;
let entities = JSON.parse(document.getElementById("entities").value);

$(function () {
    $('[data-toggle="popover"]').popover({
        trigger: 'focus',
        html: true,
        boundary: 'viewport'
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
    e.target.querySelector(".js-drop-title").innerHTML = "Discard " + item.label;
    e.target.querySelector(".js-drop-submit").innerHTML = "Discard 0";
    document.getElementById("js-drop-slider").value = 0;
    document.getElementById("js-drop-slider").max = item.quantity;
    document.getElementById("js-drop-tickmarks").innerHTML = "";
    for (var i = 0; i <= item.quantity; i++) {
        var tickmark = document.createElement("option");
        tickmark.value = i;
        document.getElementById("js-drop-tickmarks").appendChild(tickmark);
    }
});

document.getElementById("js-drop-slider").addEventListener("input", function (e) {
    var submit = document.getElementById("dropModal").querySelector(".js-drop-submit");
    submit.innerHTML = "Discard " + e.target.value;
    submit.dataset.itemQuantity = e.target.value;
});

document.getElementById("dropModal").querySelector(".js-drop-submit").onclick = function (e) {
    e.preventDefault();

    Form.post(
        "/" + gameId + "/drop",
        {
            item: document.getElementById("dropModal").dataset.itemId,
            quantity: e.currentTarget.dataset.itemQuantity,
            entityId: document.getElementById("dropModal").dataset.entityId
        }
    );
};

$("#discardIncubationModal").on("show.bs.modal", function (e) {
    let button = e.relatedTarget;

    let entity = entities.find(function (entity) {
        return entity.id === button.dataset.entityId;
    });

    let incubation = entity.incubator.find(function (incubation) {
        return incubation.varietyId === button.dataset.varietyId
            && incubation.construction.remainingSteps === parseInt(button.dataset.remainingSteps, 10);
    });

    e.target.dataset.varietyId = incubation.varietyId;
    e.target.dataset.remainingSteps = incubation.construction.remainingSteps;
    e.target.dataset.entityId = entity.id;
    e.target.querySelector(".js-drop-title").innerHTML = "Discard " + incubation.label;
    e.target.querySelector(".js-drop-submit").innerHTML = "Discard 0";
    e.target.querySelector(".js-drop-slider").value = 0;
    e.target.querySelector(".js-drop-slider").max = incubation.quantity;
    e.target.querySelector(".js-drop-tickmarks").innerHTML = "";
    for (var i = 0; i <= incubation.quantity; i++) {
        var tickmark = document.createElement("option");
        tickmark.value = i;
        e.target.querySelector(".js-drop-tickmarks").appendChild(tickmark);
    }
});

document.getElementById("discardIncubationModal").querySelector(".js-drop-slider").addEventListener("input", function (e) {
    var submit = document.getElementById("discardIncubationModal").querySelector(".js-drop-submit");
    submit.innerHTML = "Discard " + e.target.value;
    submit.dataset.quantity = e.target.value;
});

document.getElementById("discardIncubationModal").querySelector(".js-drop-submit").onclick = function (e) {
    e.preventDefault();

    const discardIncubationModal = document.getElementById("discardIncubationModal");

    Form.post(
        "/" + gameId + "/" + discardIncubationModal.dataset.entityId + "/discard-from-incubator",
        {
            varietyId: discardIncubationModal.dataset.varietyId,
            remainingSteps: discardIncubationModal.dataset.remainingSteps,
            quantity: e.currentTarget.dataset.quantity
        }
    );
};

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
        Form.post(
            "/" + gameId + "/" + entity.id + "/settings",
            {
                label: entity.label
            }
        );
    });
});

document.querySelectorAll(".js-use").forEach(function (button) {
    button.addEventListener("click", function (e) {
        e.preventDefault();

        const template = document.getElementById("spinner").content.cloneNode(true);
        this.innerText = "";
        this.appendChild(template);

        Form.post(
            "/" + gameId + "/" + e.currentTarget.dataset.entityId + "/use",
            {
                actorId: e.currentTarget.dataset.actorId,
                item: e.currentTarget.dataset.itemId,
                actionId: e.currentTarget.dataset.actionId
            }
        );
    });
});

document.querySelectorAll(".js-consume").forEach(function (button) {
    button.addEventListener("click", function (e) {
        e.preventDefault();

        const template = document.getElementById("spinner").content.cloneNode(true);
        this.innerText = "";
        this.appendChild(template);

        Form.post(
            "/" + gameId + "/consume",
            {
                resourceId: e.currentTarget.dataset.resourceId,
                entityId: e.currentTarget.dataset.entityId,
                actorId: e.currentTarget.dataset.actorId
            }
        );
    });
});

document.querySelectorAll(".js-info").forEach(function (button) {
    button.addEventListener("click", function (e) {
        e.preventDefault();
        this.parentNode.parentNode.querySelector("i[data-toggle='popover']").focus();
    });
});

document.querySelectorAll(".js-fetch").forEach(function (button) {
    button.addEventListener("click", function (e) {
        e.preventDefault();

        const template = document.getElementById("spinner").content.cloneNode(true);
        this.innerText = "";
        this.appendChild(template);

        Form.post(
            "/" + gameId + "/" + e.currentTarget.dataset.entityId + "/fetch-water",
            {
                wellId: e.currentTarget.dataset.wellId
            }
        );
    });
});

document.querySelectorAll(".js-construct").forEach(function (button) {
    button.addEventListener("click", function (e) {
        e.preventDefault();

        document.getElementById("constructModal").dataset.entityId = e.currentTarget.dataset.entityId;
        document.getElementById("constructModal").dataset.toolVarietyId = e.currentTarget.dataset.itemId;
        document.getElementById("constructModal").dataset.actionId = e.currentTarget.dataset.actionId;

        $("#constructModal").modal();
    });
});

document.querySelectorAll(".js-repair").forEach(function (button) {
    button.addEventListener("click", function (e) {
        e.preventDefault();

        document.getElementById("repairModal").dataset.actorId = e.currentTarget.dataset.actorId;
        document.getElementById("repairModal").dataset.entityId = e.currentTarget.dataset.entityId;

        $("#repairModal").modal();
    });
});

document.querySelectorAll(".js-construct-continue").forEach(function (button) {
    button.addEventListener("click", function (e) {
        e.preventDefault();

        const template = document.getElementById("spinner").content.cloneNode(true);
        this.innerText = "";
        this.appendChild(template);

        Form.post(
            "/" + gameId + "/" + e.currentTarget.dataset.actorId + "/construct/" + e.currentTarget.dataset.targetId,
            {
                constructionVarietyId: e.currentTarget.dataset.constructionVarietyId
            }
        );
    });
});

document.querySelectorAll(".js-sow").forEach(function (button) {
    button.addEventListener("click", function (e) {
        e.preventDefault();

        document.getElementById("sowModal").dataset.entityId = e.currentTarget.dataset.entityId;
        document.getElementById("sowModal").dataset.actorId = e.currentTarget.dataset.actorId;
        document.getElementById("sowModal").dataset.capacityAvailable = e.currentTarget.dataset.capacityAvailable;
        document.getElementById("sowModal").dataset.capacityUsed = e.currentTarget.dataset.capacityUsed;

        $("#sowModal").modal();
    });
});

document.querySelectorAll(".js-harvest").forEach(function (button) {
    button.addEventListener("click", function (e) {
        e.preventDefault();

        document.getElementById("harvestModal").dataset.entityId = e.currentTarget.dataset.entityId;
        document.getElementById("harvestModal").dataset.actorId = e.currentTarget.dataset.actorId;
        document.getElementById("harvestModal").dataset.varietyId = e.currentTarget.dataset.varietyId;

        $("#harvestModal").modal();
    });
});

document.querySelectorAll(".js-sort").forEach(function (button) {
    button.addEventListener("click", function (e) {
        e.preventDefault();

        $("#sortModal").modal();
    });
});

document.querySelectorAll(".js-restart").forEach(function (button) {
    button.addEventListener("click", function (e) {
        e.preventDefault();

        Form.post(
            "/" + gameId + "/restart",
            {}
        );
    });
});

document.querySelectorAll(".js-travel").forEach(function (button) {
    button.addEventListener("click", function (e) {
        e.preventDefault();

        Form.post(
            "/" + gameId + "/" + this.dataset.actorId + "/travel",
            {
                direction: this.dataset.direction
            }
        );
    });
});

document.querySelectorAll(".js-view-map").forEach(function (button) {
    button.addEventListener("click", function (e) {
        e.preventDefault();

        document.getElementById("mapModal").dataset.isViewing = 1;
        document.getElementById("mapModal").dataset.title = "Map";
        document.getElementById("mapModal").dataset.dismissLabel = "Close";

        $("#mapModal").modal();
    });
});

document.querySelectorAll(".js-travel-map").forEach(function (button) {
    button.addEventListener("click", function (e) {
        e.preventDefault();

        document.getElementById("mapModal").dataset.actorId = this.dataset.actorId;
        document.getElementById("mapModal").dataset.isTravelling = 1;
        document.getElementById("mapModal").dataset.title = "Travel";
        document.getElementById("mapModal").dataset.dismissLabel = "Cancel";

        $("#mapModal").modal();
    });
});

$("#mapModal").on("show.bs.modal", function (e) {
    let modal = this;

    this.querySelector(".modal-title").innerText = this.dataset.title;
    this.querySelector(".modal-footer button").innerText = this.dataset.dismissLabel;

    if (this.dataset.isViewing === "1") {
        this.querySelectorAll(".map a").forEach(function (el) {
            if (el.dataset.isKnown) {
                el.href = "/" + gameId + "/" + el.dataset.locationId;
            }
        });
    }

    if (this.dataset.isTravelling === "1") {
        this.querySelectorAll(".map a").forEach(function (el) {
            if (el.dataset.isKnown) {
                el.href = "/" + gameId + "/" + modal.dataset.actorId + "/travel/" + el.dataset.locationId;
            }
        });
    }
});

document.querySelectorAll(".js-read").forEach(function (button) {
    button.addEventListener("click", function (e) {
        e.preventDefault();

        document.getElementById("readModal").dataset.actorId = e.currentTarget.dataset.actorId;
        document.getElementById("readModal").dataset.itemId = e.currentTarget.dataset.itemId;

        $("#readModal").modal();
    });
});

$("#readModal").on("show.bs.modal", function (e) {
    let itemId = this.dataset.itemId;

    let item = JSON.parse(document.getElementById("inventoryItems").value).find(function (item) {
        return itemId === item.id;
    });

    this.querySelector(".modal-title").innerText = item.label;
    this.querySelector(".js-read-description").innerText = item.description;

    let xhr = new XMLHttpRequest();
    let readBody = this.querySelector(".js-read-body");

    xhr.onload = function () {
        let response = JSON.parse(this.response);

        if (response.message !== undefined) {
            let container = document.querySelector(".alert-container");
            container.innerHTML = "";

            let alert = document.createElement("div");
            alert.classList.add("alert", "alert-danger");
            alert.innerText = response.message;

            container.appendChild(alert);

            return;
        }

        readBody.innerHTML = response.body;
    };

    xhr.open("POST", "/" + gameId + "/" + this.dataset.actorId + "/read/" + itemId);
    xhr.send();
});

var eventBus = new EventBus();

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

new HarvestController(
    eventBus,
    new HarvestModalView(
        document.getElementById("harvestModal"),
        document.getElementById("transfer-item-slider")
    ),
    JSON.parse(document.getElementById("entities").value),
    gameId
);

new RepairController(
    eventBus,
    new RepairModalView(
        document.getElementById("repairModal"),
        document.getElementById("construction-card")
    ),
    JSON.parse(document.getElementById("entities").value),
    JSON.parse(document.getElementById("constructions").value),
    gameId
);

new SortController(
    eventBus,
    new SortModalView(
        document.getElementById("sortModal"),
    ),
    gameId
);

var scavengeModal = document.getElementById("scavengeModal");
var scavengeButtons = document.getElementsByClassName("js-scavenge");

var inventory = new Inventory(
    document.getElementById("inventoryItems")
        ? JSON.parse(document.getElementById("inventoryItems").value)
        : [],
    scavengeModal
        ? parseInt(scavengeModal.querySelector(".js-scavenge-inventory").dataset.inventoryCapacity, 10)
        : 0,
    scavengeModal
);

if (scavengeModal) {

    var scavengeModalView = new ScavengeModal(
        scavengeModal,
        inventory,
        document.getElementById("scavange-item-slider"),
        document.getElementById("item-popover")
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

                if (response.message !== undefined) {
                    let container = document.querySelector(".alert-container");
                    container.innerHTML = "";

                    let alert = document.createElement("div");
                    alert.classList.add("alert", "alert-danger");
                    alert.innerText = response.message;

                    container.appendChild(alert);

                    button.innerText = button.dataset.buttonText;

                    return;
                }

                haul = new Haul(response.haul.id, response.haul.items, inventory, scavengeModalView);

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
            xhr.send();
        }
    }
}
