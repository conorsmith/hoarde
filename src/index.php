<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">
    <link href="data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQEAYAAABPYyMiAAAABmJLR0T///////8JWPfcAAAACXBIWXMAAABIAAAASABGyWs+AAAAF0lEQVRIx2NgGAWjYBSMglEwCkbBSAcACBAAAeaR9cIAAAAASUVORK5CYII=" rel="icon" type="image/x-icon">


  <title>Hoarde</title>

    <style>

        .progress.resource .progress-bar {
            border-left: 1px solid #e9ecef;
            box-sizing: border-box;
        }

        .progress.resource .progress-bar:first-child {
            border-left: none;
        }

        .progress.resource .progress-bar:only-child {
            background-color: #dc3545;
        }

        .actions form button,
        .actions .btn-group {
            margin-bottom: 1rem;
        }

        .actions form:last-child button,
        .actions .btn-group:last-child {
            margin-bottom: 0;
        }
    </style>
</head>
<body style="margin-top: 1rem;">

<div class="container">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">Turn <?=$turnIndex?></li>
      </ol>
    </nav>

    <div class="row">
        <div class="col-sm-4 offset-sm-4">
            <div class="card">
                <div class="card-body">

                    <h5 class="card-title">
                      <?php if ($isIntact) : ?>
                        <i class="fas fa-fw fa-<?=$entity->icon?>"></i>
                      <?php else : ?>
                        <i class="fas fa-fw fa-skull-crossbones"></i>
                      <?php endif ?>
                      <?=$entity->label?>
                    </h5>

                    <?php if (!is_null($danger)) : ?>
                        <div class="alert alert-danger"><?=$danger?></div>
                    <?php elseif (!is_null($warning)) : ?>
                      <div class="alert alert-warning"><?=$warning?></div>
                    <?php elseif (!is_null($success)) : ?>
                      <div class="alert alert-success"><?=$success?></div>
                    <?php elseif (!is_null($info)) : ?>
                      <div class="alert alert-info"><?=$info?></div>
                    <?php endif ?>

                    <?php foreach ($resources as $resource) : ?>
                        <p>
                            <strong><?=$resource['label']?></strong>
                            <div class="progress resource">
                                <?php for ($i = 0; $i < $resource['level']; $i++): ?>
                                    <div class="progress-bar" style="width: <?=$resource['segmentWidth']?>%;"></div>
                                <?php endfor; ?>
                            </div>
                        </p>
                    <?php endforeach ?>

                    <p><strong>Inventory</strong></p>
                    <div class="actions">
                        <?php foreach ($inventory as $item) : ?>
                            <div class="btn-group d-flex" role="group">

                                <a href="#"
                                   class="btn btn-light btn-block js-use <?=($isIntact ? "" : "disabled")?>"
                                   data-item-id="<?=$item['id']?>"
                                ><?=$item['label']?> (<?=$item['quantity']?>)</a>

                                <div class="btn-group" role="group">
                                    <button type="button"
                                            class="btn btn-light dropdown-toggle"
                                            data-toggle="dropdown"
                                            <?=($isIntact ? "" : "disabled")?>></button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a href="#"
                                           class="dropdown-item"
                                           data-toggle="modal"
                                           data-target="#dropModal"
                                           data-item-id="<?=$item['id']?>"
                                           data-item-label="<?=$item['label']?>"
                                           data-item-quantity="<?=$item['quantity']?>"
                                        >Drop</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                    <div class="progress" style="height: 0.5rem; margin-top: 1rem;">
                          <div class="progress-bar <?=$entityOverencumbered ? "bg-danger" : "bg-primary"?>" style="width: <?=$inventoryWeight?>%;"></div>
                    </div>

                    <hr>

                    <div class="actions">

                      <button type="button"
                              class="btn btn-light btn-block js-scavenge"
                              <?=($isIntact ? "" : "disabled")?>
                              style="margin-bottom: 1rem;"
                      >Scavenge</button>

                      <form method="POST" action="/<?=$gameId?>/wait">
                        <button type="submit" class="btn btn-light btn-block" <?=($isIntact ? "" : "disabled")?>>Wait</button>
                      </form>

                    </div>

                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="/<?=$gameId?>/restart" style="float: right;">
      <button type="submit" class="btn btn-link">Restart</button>
    </form>

    <?php /* DROP MODAL */ ?>

    <div class="modal" tabindex="-1" role="dialog" id="dropModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title js-drop-title"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="range" id="js-drop-slider" value="0" min="0" max="0" list="js-drop-tickmarks" style="width: 100%;" />
                    <datalist id="js-drop-tickmarks">
                      <?php for ($i = 0; $i <= 10; $i++) : ?>
                        <option value="<?=$i?>">
                      <?php endfor ?>
                    </datalist>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary js-drop-submit">Drop 0</button>
                </div>
            </div>
        </div>
    </div>

    <?php /* SCAVENGE MODAL */ ?>

    <div class="modal" tabindex="-1" role="dialog" id="scavengeModal" data-backdrop="static" data-keyboard="false">
      <div class="modal-dialog" role="document">
        <div class="modal-content">

          <div class="modal-header">
            <h5 class="modal-title">Scavenging Haul</h5>
          </div>

          <div class="modal-body">
            <div class="js-scavenge-haul"></div>
          </div>

          <div class="modal-body js-scavenge-inventory"
               style="display: none; border-top: 1px solid #dee2e6;"
               data-inventory-weight="<?=$entity->inventory->weight?>"
               data-inventory-capacity="<?=$entity->inventory->capacity?>"
          >
            <div style="font-size: 0.8rem; font-weight: 600;">Inventory Capacity</div>
            <div class="d-flex" style="margin-top: 0.2rem;">
              <div style="margin-right: 1rem;">
                  <?=$entity->inventory->weight / 1000?> / <?=$entity->inventory->capacity / 1000?> kg
              </div>
              <div class="flex-fill  align-self-center">
                <div class="progress">
                  <div class="progress-bar <?=$entityOverencumbered ? "bg-danger" : "bg-primary"?>"
                       style="width: <?=$inventoryWeight?>%;"
                  ></div>
                  <div class="progress-bar js-scavenge-inventory-haul-progress"></div>
                </div>
              </div>
              <div class="js-scavenge-inventory-haul-weight"
                   style="margin-left: 1rem; width: 3.6rem; text-align: right;"
              ></div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button"
                    class="btn btn-primary btn-block js-scavenge-submit"
            >Add to Inventory</button>
          </div>

        </div>
      </div>
    </div>

    <input type="hidden" id="gameId" value="<?=$gameId?>" />

</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

<script>
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

    var createHaul = function (response) {
        var haul = {
            items: response.haul.items,
            inventory: {
                weight: parseInt(scavengeModal.querySelector(".js-scavenge-inventory").dataset.inventoryWeight, 10),
                capacity: parseInt(scavengeModal.querySelector(".js-scavenge-inventory").dataset.inventoryCapacity, 10)
            },
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
                        inventory: this.inventory
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

    scavengeModal.querySelector(".js-scavenge-submit").onclick = function (e) {
        if (this.dataset.isEmpty === "true") {
            window.location.reload();
            return;
        }

        var body = {};
        body.selectedItems = {};

        var inputs = scavengeModal.querySelector(".js-scavenge-haul").findInputs();

        for (var i = 0; i < inputs.length; i++) {
            body.selectedItems[inputs[i].dataset.varietyId] = parseInt(inputs[i].value, 10);
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

        xhr.open("POST", "/" + gameId + "/scavenge/" + this.dataset.haulId);
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.send(JSON.stringify(body));

        scavengeModal.dispatchEvent(new CustomEvent("haul.add"));
    };

    scavengeModal.querySelector(".js-scavenge-haul").handleHaulCreated = function (e) {
        if (e.detail.isEmpty) {
            var alert = document.createElement("div");
            alert.classList.add("alert");
            alert.classList.add("alert-warning");
            alert.innerHTML = "Failed to scavenge anything.";

            this.appendChild(alert);
        } else {
            for (var i = 0; i < e.detail.items.length; i++) {
                var item = e.detail.items[i];

                var container = document.createElement("div");

                var labelQuantity = document.createElement("span");
                labelQuantity.classList.add("js-scavange-quantity");
                labelQuantity.innerHTML = item.quantity;
                labelQuantity.handleHaulModified = function (e) {
                    this.innerHTML = e.detail.newQuantity;
                };

                var label = document.createElement("p");
                label.innerHTML = item.label + " &times; ";
                label.appendChild(labelQuantity);

                var slider = document.createElement("input");
                slider.type = "range";
                slider.setAttribute("list", "js-scavange-tickmarks-" + item.varietyId);
                slider.dataset.varietyId = item.varietyId;
                slider.dataset.weight = item.weight;
                slider.min = 0;
                slider.max = item.quantity;
                slider.value = item.quantity;
                slider.style.width = "100%";

                var datalist = document.createElement("datalist");
                datalist.id = "js-scavange-tickmarks-" + item.varietyId;

                for (var t = 0; t <= item.quantity; t++) {
                    var tickmark = document.createElement("option");
                    tickmark.value = t;
                    datalist.appendChild(tickmark);
                }

                container.appendChild(label);
                container.appendChild(slider);
                container.appendChild(datalist);

                slider.addEventListener("input", function (inputEvent) {
                    e.detail.haul.modifyItemQuantity(inputEvent.target.dataset.varietyId, inputEvent.target.value);
                });

                this.appendChild(container);
            }
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
        this.innerHTML = "+" + (e.detail.weight / 1000) + " kg";
    };

    scavengeModal.querySelector(".js-scavenge-inventory-haul-weight").handleHaulModified = function (e) {
        this.innerHTML = "+" + (e.detail.newWeight / 1000) + " kg";
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

    for (var i = 0; i < scavengeButtons.length; i++) {
        scavengeButtons[i].onclick = function (e) {
            e.preventDefault();

            var xhr = new XMLHttpRequest();

            xhr.onload = function () {
                var response = JSON.parse(this.response);
                createHaul(response);
                $("#scavengeModal").modal('show');
            };

            xhr.open("POST", "/" + gameId + "/scavenge");
            xhr.send();
        }
    }

</script>
</body>
</html>
