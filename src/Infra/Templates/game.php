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
        <div class="dropdown">
          <a href="#"
             class="btn btn-sm btn-link dropdown-toggle"
             role="button"
             data-toggle="dropdown"
          >
            Menu
          </a>
          <div class="dropdown-menu dropdown-menu-right">
            <a href="#" class="dropdown-item js-sort">Sort Entities</a>
            <a href="#" class="dropdown-item js-restart">Restart Game</a>
          </div>
        </div>
      </ol>
    </nav>

    <div class="alert-container">
      <?php if (!is_null($alert)) : ?>
        <div class="alert alert-<?=$alert->classSuffix?>"><?=$alert->message?></div>
      <?php endif ?>
    </div>

    <div class="card bg-light" style="margin-bottom: 1rem;">
      <div class="card-body d-flex align-items-center justify-content-between"
           style="padding: 0.75rem;"
      >
        <div>
          <a href="#"
             class="btn btn-sm btn-secondary"
             data-toggle="modal"
             data-target="#mapModal"
          >
            <i class="fas fa-fw fa-map-marker-alt"></i> <?=$location->coordinates?>
          </a>
          <small style="color: #666; margin-left: 0.5rem;">
            <i class="fas fa-fw fa-users"></i>
            <?=$location->entitiesCount?>
          </small>
        </div>
        <div class="progress w-50">
          <div class="progress-bar"
               style="width: <?=$location->remainingScavengingLevel?>%"
          ></div>
        </div>
      </div>
    </div>

  <div class="row justify-content-center">

      <?php foreach ($entities as $entity) : ?>
          <?php if ($entity->varietyId == \ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig::HUMAN) : ?>
              <?=$this->render("Game/entity-human.php", [
                  'entity'                => $entity,
                  'isIntact'              => $isIntact,
              ])?>
          <?php elseif ($entity->varietyId == \ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig::WOODEN_CRATE
            || $entity->varietyId == \ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig::TOOLBOX) : ?>
              <?=$this->render("Game/entity-crate.php", [
                  'entity'                => $entity,
                  'isIntact'              => $isIntact,
              ])?>
          <?php elseif ($entity->varietyId == \ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig::WELL) : ?>
              <?=$this->render("Game/entity-well.php", [
                  'entity'   => $entity,
                  'isIntact' => $isIntact,
              ])?>
          <?php elseif ($entity->varietyId == \ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig::GARDEN_PLOT) : ?>
              <?=$this->render("Game/entity-plot.php", [
                  'entity'   => $entity,
                  'isIntact' => $isIntact,
              ])?>
          <?php endif ?>
      <?php endforeach ?>

    </div>

    <?=$this->render("Game/modal-drop.php")?>
    <?=$this->render("Game/modal-discard-incubation.php")?>
    <?=$this->render("Game/modal-settings.php")?>
    <?=$this->render("Game/modal-sow.php")?>
    <?=$this->render("Game/modal-harvest.php")?>
    <?=$this->render("Game/modal-read.php")?>
    <?=$this->render("Game/modal-repair.php")?>
    <?=$this->render("Game/modal-map.php", [
        'map' => $map,
    ])?>

    <?=$this->render("Game/modal-scavenge.php", [
        'entity' => $human,
    ])?>

    <?=$this->render("Game/modal-construct.php", [
        'constructions' => $constructions,
    ])?>

    <?=$this->render("Game/modal-transfer.php")?>

    <?=$this->render("Game/modal-sort.php", [
        'entities' => $entities,
    ])?>

    <input type="hidden" id="gameId" value="<?=$game->id?>" />
    <input type="hidden" id="inventoryItems" value='<?=$this->e(json_encode($human->inventory->items))?>' />
    <input type="hidden" id="entities" value='<?=$this->e(json_encode($entities))?>' />
    <input type="hidden" id="constructions" value='<?=$this->e(json_encode($constructions))?>' />
    <input type="hidden" id="actions" value='<?=$this->e(json_encode($actions))?>' />

    <?=$this->render("Game/template-scavenge-item-slider.php")?>
    <?=$this->render("Game/template-spinner.php")?>
    <?=$this->render("Game/template-transfer-item-slider.php")?>
    <?=$this->render("Game/template-item-popover.php")?>
    <?=$this->render("Game/template-construction-card.php")?>

</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<script src="/js/vendor/html5sortable.min.js"></script>
<script type="module" src="/main.js"></script>

</body>
</html>
