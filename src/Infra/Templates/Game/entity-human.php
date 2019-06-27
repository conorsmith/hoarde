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

            <?=$this->render("Game/resource-needs.php", [
                'entity'   => $entity,
                'isIntact' => $isIntact,
            ])?>

            <div class="actions">

              <div class="btn-group btn-flex">
                <button type="button"
                        class="btn btn-light btn-block js-scavenge"
                        data-length="1"
                        data-entity-id="<?=$entity->id?>"
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
                     data-entity-id="<?=$entity->id?>"
                  >
                    Short Scavenge
                    <span class="badge">1 turn</span>
                  </a>

                  <a href="#"
                     class="dropdown-item d-flex align-items-baseline justify-content-between js-scavenge"
                     data-length="3"
                     data-entity-id="<?=$entity->id?>"
                  >
                    Long Scavenge
                    <span class="badge">3 turns</span>
                  </a>

                </div>
              </div>

              <div class="btn-group w-100">
                <a href="#"
                   class="btn btn-light js-travel"
                   data-direction="west"
                   data-actor-id="<?=$entity->id?>"
                >
                  <i class="fas fa-fw fa-arrow-left"></i>
                </a>
                <a href="#"
                   class="btn btn-light js-travel"
                   data-direction="north"
                   data-actor-id="<?=$entity->id?>"
                >
                  <i class="fas fa-fw fa-arrow-up"></i>
                </a>
                <a href="#"
                   class="btn btn-light js-travel"
                   data-direction="south"
                   data-actor-id="<?=$entity->id?>"
                >
                  <i class="fas fa-fw fa-arrow-down"></i>
                </a>
                <a href="#"
                   class="btn btn-light js-travel"
                   data-direction="east"
                   data-actor-id="<?=$entity->id?>"
                >
                  <i class="fas fa-fw fa-arrow-right"></i>
                </a>
              </div>

            </div>

            <div class="progress" style="height: 0.5rem; margin-top: 1rem;">
                <div class="progress-bar <?=$entity->inventory->isAtCapacity ? "bg-danger" : "bg-primary"?>"
                     style="width: <?=($entity->inventory->weight / $entity->inventory->capacity * 100)?>%;"
                ></div>
            </div>

            <hr>

            <p><strong>Inventory</strong></p>

            <?=$this->render("Game/inventory.php", [
                'entity'   => $entity,
                'isIntact' => $isIntact,
            ])?>

            <hr>

            <div class="actions">

                <?php if ($entity->inventory->initialTransferEntityId) : ?>

                    <button type="button"
                            class="btn btn-light btn-block js-transfer"
                            data-toggle="modal"
                            data-target="#transferModal"
                            data-source-id="<?=$entity->id?>"
                            data-destination-id="<?=$entity->inventory->initialTransferEntityId?>"
                        <?=($isIntact ? "" : "disabled")?>
                    >Transfer Items</button>

                <?php endif ?>

            </div>

        </div>
    </div>
</div>
