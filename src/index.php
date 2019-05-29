<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">

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
                      Entity
                      <?php if (!$isIntact) : ?>
                        <i class="fas fa-skull-crossbones"></i>
                      <?php endif ?>
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
                                   class="btn btn-light btn-block js-use"
                                   data-item-id="<?=$item['id']?>"
                                   <?=($isIntact ? "" : "disabled")?>
                                ><?=$item['label']?> (<?=$item['quantity']?>)</a>

                                <div class="btn-group" role="group">
                                    <button type="button"
                                            class="btn btn-light dropdown-toggle"
                                            data-toggle="dropdown"></button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a href="#"
                                           class="dropdown-item js-drop"
                                           data-item-id="<?=$item['id']?>"
                                        >Drop 1</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>

                    <hr>

                    <div class="actions">
                      <form method="POST" action="/<?=$gameId?>/scavenge">
                        <button type="submit" class="btn btn-light btn-block" <?=($isIntact ? "" : "disabled")?>>Scavenge</button>
                      </form>
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

    <input type="hidden" id="gameId" value="<?=$gameId?>" />

</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

<script>
    var gameId = document.getElementById("gameId").value;
    var useButtons = document.getElementsByClassName("js-use");
    var dropMenuOptions = document.getElementsByClassName("js-drop");

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

    for (var i = 0; i < dropMenuOptions.length; i++) {
        dropMenuOptions[i].onclick = function (e) {
            e.preventDefault();

            var itemId = e.target.dataset.itemId;

            var form = document.createElement("form");
            form.setAttribute("action", "/" + gameId + "/drop");
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
</script>
</body>
</html>
