class Transfer {
    constructor() {

    }

    execute() {
        var body = [];
        var error = document.getElementById("transferModal").querySelector(".js-error");

        error.style.display = "none";

        document.getElementById("transferModal").querySelectorAll(".js-inventory").forEach(function (inventory) {
            var transfer = {
                entityId: inventory.dataset.entityId,
                items: []
            };
            inventory.querySelectorAll("input[type='range']").forEach(function (input) {
                transfer.items.push({
                    varietyId: input.dataset.varietyId,
                    quantity: input.value
                })
            });
            body.push(transfer)
        });

        var xhr = new XMLHttpRequest();

        xhr.onload = function () {
            if (this.responseText === "") {
                window.location.reload();
                return;
            }

            error.innerText = this.responseText;
            error.style.display = "block";
        };

        xhr.open("POST", "/" + gameId + "/transfer");
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.send(JSON.stringify(body));
    }
}