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

    var scavengeButtons = document.getElementsByClassName("js-scavenge");

    for (var i = 0; i < scavengeButtons.length; i++) {
        scavengeButtons[i].onclick = function (e) {
            e.preventDefault();

            var xhr = new XMLHttpRequest();

            xhr.onload = function () {
                var response = JSON.parse(this.response);

                console.log(response.haul);

                if (response.haul.items.length === 0) {
                    var alert = document.createElement("div");
                    alert.classList.add("alert");
                    alert.classList.add("alert-warning");
                    alert.innerHTML = "Failed to scavenge anything.";
                    document.getElementById("scavengeModal").querySelector(".js-scavenge-haul").appendChild(alert);
                    document.getElementById("scavengeModal").querySelector(".js-scavenge-submit").innerHTML = "Oh well";
                } else {
                    var inventory = document.getElementById("scavengeModal").querySelector(".js-scavenge-inventory");

                    document.getElementById("scavengeModal").querySelector(".js-scavenge-submit").dataset.haulId
                        = response.haul.id;

                    var haulTooLarge = parseInt(inventory.dataset.inventoryWeight, 10) + response.haul.weight > parseInt(inventory.dataset.inventoryCapacity, 10);

                    if (haulTooLarge) {
                        document.getElementById("scavengeModal").querySelector(".js-scavenge-submit").setAttribute("disabled", true);
                    }

                    for (var i = 0; i < response.haul.items.length; i++) {
                        var item = response.haul.items[i];

                        var container = document.createElement("div");

                        var labelQuantity = document.createElement("span");
                        labelQuantity.classList.add("js-scavange-quantity");
                        labelQuantity.innerHTML = item.quantity;

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

                        slider.addEventListener("input", function (e) {

                            labelQuantity.innerHTML = e.target.value;

                            var sliders = document.getElementById("scavengeModal")
                                .querySelectorAll(".js-scavenge-haul input[type='range']");
                            var runningHaulWeight = 0;

                            for (var j = 0; j < sliders.length; j++) {
                                runningHaulWeight += sliders[j].value * sliders[j].dataset.weight;
                            }

                            haulTooLarge = parseInt(inventory.dataset.inventoryWeight, 10) + runningHaulWeight > parseInt(inventory.dataset.inventoryCapacity, 10);

                            console.log(runningHaulWeight, haulTooLarge);

                            if (haulTooLarge) {
                                var haulProgress = 100 - (parseInt(inventory.dataset.inventoryWeight, 10) / inventory.dataset.inventoryCapacity * 100);
                                inventory.querySelector(".js-scavenge-inventory-haul-progress")
                                    .classList.remove("bg-success");
                                inventory.querySelector(".js-scavenge-inventory-haul-progress")
                                    .classList.add("bg-danger");
                                document.getElementById("scavengeModal").querySelector(".js-scavenge-submit")
                                    .setAttribute("disabled", true);
                            } else {
                                var haulProgress = (runningHaulWeight / inventory.dataset.inventoryCapacity * 100);
                                inventory.querySelector(".js-scavenge-inventory-haul-progress")
                                    .classList.remove("bg-danger");
                                inventory.querySelector(".js-scavenge-inventory-haul-progress")
                                    .classList.add("bg-success");
                                document.getElementById("scavengeModal").querySelector(".js-scavenge-submit")
                                    .removeAttribute("disabled");
                            }

                            if (runningHaulWeight === 0) {
                                document.getElementById("scavengeModal").querySelector(".js-scavenge-submit").innerHTML
                                    = "Discard Haul";
                            } else {
                                document.getElementById("scavengeModal").querySelector(".js-scavenge-submit").innerHTML
                                    = "Add to Inventory";
                            }

                            inventory.querySelector(".js-scavenge-inventory-haul-progress").style.width
                                = haulProgress + "%";
                            inventory.querySelector(".js-scavenge-inventory-haul-weight").innerHTML
                                = "+" + (runningHaulWeight / 1000) + " kg";
                        });

                        document.getElementById("scavengeModal").querySelector(".js-scavenge-haul").appendChild(container);
                    }

                    var haulWeight = document.createElement("div");
                    haulWeight.classList.add("progress-bar");
                    haulWeight.classList.add("js-scavenge-inventory-haul-progress");
                    if (haulTooLarge) {
                        haulWeight.classList.add("bg-danger");
                        haulWeight.style.width
                            = (100 - (inventory.dataset.inventoryWeight / inventory.dataset.inventoryCapacity * 100)) + "%";
                    } else {
                        haulWeight.classList.add("bg-success");
                        haulWeight.style.width = (response.haul.weight / inventory.dataset.inventoryCapacity * 100) + "%";
                    }

                    inventory.style.display = "block";
                    inventory.querySelector(".js-scavenge-inventory-haul-weight").innerHTML
                        = "+" + (response.haul.weight / 1000) + " kg";
                    document.getElementById("scavengeModal")
                        .querySelector(".js-scavenge-inventory .progress")
                        .appendChild(haulWeight);
                }

                $("#scavengeModal").modal('show');
            };

            xhr.open("POST", "/" + gameId + "/scavenge");
            xhr.send();
        }
    }

    document.getElementById("scavengeModal").querySelector(".js-scavenge-submit").onclick = function (e) {
        if (this.dataset.haulId === undefined) {
            window.location.reload();
            return;
        }

        var previousAlert = document.getElementById("scavengeModal").querySelector(".js-scavenge-haul .alert");

        if (previousAlert) {
            previousAlert.remove();
        }

        var body = {};
        body.selectedItems = {};

        var inputs = document.getElementById("scavengeModal").querySelectorAll(".js-scavenge-haul input[type='range']");

        for (var i = 0; i < inputs.length; i++) {
            body.selectedItems[inputs[i].dataset.varietyId] = parseInt(inputs[i].value, 10);
        }

        var xhr = new XMLHttpRequest();

        xhr.onload = function () {
            if (this.response === "") {
                window.location.reload();
            } else {
                var alert = document.createElement("div");
                alert.classList.add("alert");
                alert.classList.add("alert-danger");
                alert.innerHTML = this.response;

                document.getElementById("scavengeModal").querySelector(".js-scavenge-haul").appendChild(alert);
            }
        };

        xhr.open("POST", "/" + gameId + "/scavenge/" + this.dataset.haulId);
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.send(JSON.stringify(body));
    }

</script>
</body>
</html>
