<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">
  <link rel="stylesheet" href="/css.php">

    <link href="data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQEAYAAABPYyMiAAAABmJLR0T///////8JWPfcAAAACXBIWXMAAABIAAAASABGyWs+AAAAF0lEQVRIx2NgGAWjYBSMglEwCkbBSAcACBAAAeaR9cIAAAAASUVORK5CYII=" rel="icon" type="image/x-icon">


  <title>Hoarde</title>

</head>
<body style="margin-top: 1rem;">

<div class="container">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb d-flex justify-content-between">
        <div class="text-muted align-self-center">Turn <?=$turnIndex?></div>
        <div class="text-muted align-self-center" style="font-weight: 900; font-size: 0.8rem;">HOARDE</div>
        <div>
          <form method="POST" action="/<?=$gameId?>/restart">
            <button type="submit" class="btn btn-link btn-sm">Restart</button>
          </form>
        </div>
      </ol>
    </nav>



  <div class="row justify-content-center">
        <div class="col-lg-4 mb-3">
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
                    <div class="inventory-actions">
                        <?php foreach ($inventory as $item) : ?>
                            <div class="btn-group d-flex" role="group">

                                <a href="#"
                                   class="btn btn-light btn-block js-use <?=($isIntact ? "" : "disabled")?>"
                                   data-item-id="<?=$item['id']?>"
                                   style="text-align: left;"
                                ><i class="fas fa-fw fa-<?=$item['icon']?>"></i> <?=$item['label']?> (<?=$item['quantity']?>)</a>

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
                      >Scavenge</button>

                      <?php if ($crate) : ?>

                          <button type="button"
                                  class="btn btn-light btn-block js-transfer"
                                  data-toggle="modal"
                                  data-target="#transferModal"
                              <?=($isIntact ? "" : "disabled")?>
                          >Transfer Items</button>

                      <?php endif ?>

                    </div>

                </div>
            </div>
        </div>

        <?php if ($crate) : ?>

          <div class="col-lg-4 mb-3">
            <div class="card">
              <div class="card-body">

                <h5 class="card-title">
                      <i class="fas fa-fw fa-<?=$crate->icon?>"></i>
                    <?=$crate->label?>
                </h5>

                <p><strong>Inventory</strong></p>

                <div>
                  <ul class="list-group">
                  <?php foreach ($crate->inventory->items as $item) : ?>
                    <li class="list-group-item">
                      <i class="fas fa-fw fa-<?=$item->icon?>"></i>
                      <?=$item->label?>
                      (<?=$item->quantity?>)
                    </li>
                  <?php endforeach ?>
                  </ul>
                </div>

                <div class="progress" style="height: 0.5rem; margin-top: 1rem;">
                  <?php $crateWeightProgress = $crate->inventory->weight / $crate->inventory->capacity * 100; ?>
                  <div class="progress-bar <?=$crateWeightProgress >= 100 ? "bg-danger" : "bg-primary"?>" style="width: <?=$crateWeightProgress?>%;"></div>
                </div>

                <hr>

                <div class="actions">

                  <button type="button"
                          class="btn btn-light btn-block js-transfer"
                          data-toggle="modal"
                          data-target="#transferModal"
                      <?=($isIntact ? "" : "disabled")?>
                  >Transfer Items</button>

                </div>

              </div>
            </div>
          </div>

        <?php endif ?>

    </div>

    <?php include __DIR__ . "/Game/modal-drop.php"; ?>
    <?php include __DIR__ . "/Game/modal-scavenge.php"; ?>
    <?php include __DIR__ . "/Game/modal-transfer.php"; ?>

    <input type="hidden" id="gameId" value="<?=$gameId?>" />
    <input type="hidden" id="inventoryItems" value='<?=json_encode($inventory)?>' />

    <?php include __DIR__ . "/Game/template-scavenge-item-slider.php"; ?>
    <?php include __DIR__ . "/Game/template-spinner.php"; ?>

</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<script src="/js.php"></script>

</body>
</html>
