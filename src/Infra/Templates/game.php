<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">
  <link rel="stylesheet" href="/main.css">

    <link href="data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQEAYAAABPYyMiAAAABmJLR0T///////8JWPfcAAAACXBIWXMAAABIAAAASABGyWs+AAAAF0lEQVRIx2NgGAWjYBSMglEwCkbBSAcACBAAAeaR9cIAAAAASUVORK5CYII=" rel="icon" type="image/x-icon">


  <title>Hoarde</title>

</head>
<body style="margin-top: 1rem;">

<div class="container">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb d-flex justify-content-between">
        <div class="text-muted align-self-center">Turn <?=$game->turnIndex?></div>
        <div class="text-muted align-self-center" style="font-weight: 900; font-size: 0.8rem;">HOARDE</div>
        <div>
          <form method="POST" action="/<?=$game->id?>/restart">
            <button type="submit" class="btn btn-link btn-sm">Restart</button>
          </form>
        </div>
      </ol>
    </nav>

    <?php if (!is_null($alert)) : ?>
      <div class="alert alert-<?=$alert->classSuffix?>"><?=$alert->message?></div>
    <?php endif ?>

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

                    <div style="margin-bottom: 1.6rem;">
                      <?php foreach ($entity->resourceNeeds as $resource) : ?>
                          <div style="margin-bottom: 1rem;">

                            <div style="margin-bottom: 0.4rem;">
                                <strong><?=$resource->label?></strong>
                            </div>

                            <div class="progress resource"
                                 style="margin-bottom: 0.6rem;"
                            >
                                <?php for ($i = 0; $i < $resource->level; $i++): ?>
                                    <div class="progress-bar" style="width: <?=$resource->segmentWidth?>%;"></div>
                                <?php endfor; ?>
                            </div>

                            <div class="btn-group btn-flex">
                              <button type="button"
                                      class="btn btn-light btn-block js-consume"
                                      data-entity-id="<?=$entity->id?>"
                                      data-resource-id="<?=$resource->id?>"
                                      <?=(!$isIntact || $resource->noItems ? "disabled" : "")?>
                              >
                                Consume <?=$resource->label?>
                              </button>

                              <button type="button"
                                      class="btn btn-light dropdown-toggle"
                                      data-toggle="dropdown"
                                      <?=(!$isIntact || $resource->noItems ? "disabled" : "")?>
                              ></button>

                              <div class="dropdown-menu dropdown-menu-right w-100">

                                <?php if ($resource->lastConsumedItem) : ?>
                                  <a href="#"
                                     class="dropdown-item d-flex align-items-baseline justify-content-between js-use"
                                     data-entity-id="<?=$entity->id?>"
                                     data-item-id="<?=$resource->lastConsumedItem->id?>"
                                  >
                                    <div>
                                      <i class="fas fa-history"></i>
                                      Consume <?=$resource->lastConsumedItem->label?>
                                    </div>
                                    <span class="badge"><?=$resource->lastConsumedItem->quantity?></span>
                                  </a>
                                <?php endif ?>

                                <?php if ($resource->lastConsumedItem && count($resource->items)) : ?>
                                  <div class="dropdown-divider"></div>
                                <?php endif ?>

                                <?php foreach ($resource->items as $item) : ?>
                                  <a href="#"
                                     class="dropdown-item d-flex align-items-baseline justify-content-between js-use"
                                     data-entity-id="<?=$entity->id?>"
                                     data-item-id="<?=$item->id?>"
                                  >
                                    Consume <?=$item->label?>
                                    <span class="badge"><?=$item->quantity?></span>
                                  </a>
                                <?php endforeach ?>

                              </div>
                            </div>

                          </div>
                      <?php endforeach ?>
                    </div>

                    <div class="actions">

                      <div class="btn-group btn-flex">
                        <button type="button"
                                class="btn btn-light btn-block js-scavenge"
                                data-length="1"
                            <?=(!$isIntact ? "disabled" : "")?>
                        >
                          Scavenge
                        </button>

                        <button type="button"
                                class="btn btn-light dropdown-toggle"
                                data-toggle="dropdown"
                            <?=(!$isIntact ? "disabled" : "")?>
                        ></button>

                        <div class="dropdown-menu dropdown-menu-right w-100">

                          <a href="#"
                             class="dropdown-item d-flex align-items-baseline justify-content-between js-scavenge"
                             data-length="1"
                          >
                            Short Scavenge
                            <span class="badge">1 turn</span>
                          </a>

                          <a href="#"
                             class="dropdown-item d-flex align-items-baseline justify-content-between js-scavenge"
                             data-length="3"
                          >
                            Long Scavenge
                            <span class="badge">3 turns</span>
                          </a>

                        </div>
                      </div>

                    </div>

                    <div class="progress" style="height: 0.5rem; margin-top: 1rem;">
                      <div class="progress-bar <?=$entity->inventory->isAtCapacity ? "bg-danger" : "bg-primary"?>"
                           style="width: <?=($entity->inventory->weight / $entity->inventory->capacity * 100)?>%;"
                      ></div>
                    </div>

                    <hr>

                    <p><strong>Inventory</strong></p>

                    <?=$this->renderTemplate("Game/inventory.php", [
                        'entity'   => $entity,
                        'isIntact' => $isIntact,
                    ])?>

                    <hr>

                    <div class="actions">

                      <?php if (count($crates) > 0) : ?>

                          <button type="button"
                                  class="btn btn-light btn-block js-transfer"
                                  data-toggle="modal"
                                  data-target="#transferModal"
                                  data-source-id="<?=$entity->id?>"
                                  data-destination-id="<?=$crates[0]->id?>"
                              <?=($isIntact ? "" : "disabled")?>
                          >Transfer Items</button>

                      <?php endif ?>

                    </div>

                </div>
            </div>
        </div>

        <?php foreach ($crates as $crate) : ?>

          <div class="col-lg-4 mb-3">
            <div class="card">
              <div class="card-body">

                <h5 class="card-title d-flex justify-content-between">
                    <div>
                      <i class="fas fa-fw fa-<?=$crate->icon?>"></i>
                      <?=$crate->label?>
                    </div>
                    <a href="#"
                       style="display: block; font-size: 1rem; color: #888;"
                       data-toggle="modal"
                       data-target="#settingsModal"
                       data-entity-id="<?=$crate->id?>"
                    >
                      <i class="fas fa-cog"></i>
                    </a>
                </h5>

                <p><strong>Inventory</strong></p>

                <?=$this->renderTemplate("Game/inventory.php", [
                    'entity'   => $crate,
                    'isIntact' => $isIntact,
                ])?>

                <hr>

                <div class="actions">

                  <button type="button"
                          class="btn btn-light btn-block js-transfer"
                          data-toggle="modal"
                          data-target="#transferModal"
                          data-source-id="<?=$crate->id?>"
                          data-destination-id="<?=$entity->id?>"
                      <?=($isIntact ? "" : "disabled")?>
                  >Transfer Items</button>

                </div>

              </div>
            </div>
          </div>

        <?php endforeach ?>

        <?php if ($well) : ?>

          <?=$this->renderTemplate("Game/entity-well.php", [
              'entity'   => $well,
              'actor'    => $entity,
              'isIntact' => $isIntact,
          ])?>

        <?php endif ?>

    </div>

    <?=$this->renderTemplate("Game/modal-drop.php")?>
    <?=$this->renderTemplate("Game/modal-settings.php")?>

    <?=$this->renderTemplate("Game/modal-scavenge.php", [
        'entity' => $entity,
    ])?>

    <?php if (count($crates) > 0) : ?>
      <?=$this->renderTemplate("Game/modal-transfer.php", [
          'entities' => [
              $entity,
              $crates[0],
          ],
      ])?>
    <?php endif ?>

    <input type="hidden" id="gameId" value="<?=$game->id?>" />
    <input type="hidden" id="inventoryItems" value='<?=json_encode($entity->inventory->items)?>' />
    <input type="hidden" id="entities" value='<?=$encodedEntities?>' />

    <?=$this->renderTemplate("Game/template-scavenge-item-slider.php")?>
    <?=$this->renderTemplate("Game/template-spinner.php")?>
    <?=$this->renderTemplate("Game/template-transfer-item-slider.php")?>
    <?=$this->renderTemplate("Game/template-item-popover.php")?>

</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<script src="/main.js"></script>

</body>
</html>
