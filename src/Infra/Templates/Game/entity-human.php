<div class="col-lg-4 mb-3">
    <div class="card">
        <div class="card-body">

            <h5 class="card-title">
                <?php if ($isIntact && $entity->isIntact) : ?>
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

              <div class="row">

                <div class="travel-btn-group col">

                  <div class="btn-group w-100">
                    <a href="#"
                       class="btn btn-light js-travel <?=(!$isIntact || !$entity->isIntact ? "disabled" : "")?>"
                       data-direction="north-west"
                       data-actor-id="<?=$entity->id?>"
                    >
                      <i class="fas fa-fw fa-arrow-up"
                         style="transform: rotateY(0deg) rotate(-45deg);"
                      ></i>
                    </a>
                    <a href="#"
                       class="btn btn-light js-travel <?=(!$isIntact || !$entity->isIntact ? "disabled" : "")?>"
                       data-direction="north"
                       data-actor-id="<?=$entity->id?>"
                    >
                      <i class="fas fa-fw fa-arrow-up"></i>
                    </a>
                    <a href="#"
                       class="btn btn-light js-travel <?=(!$isIntact || !$entity->isIntact ? "disabled" : "")?>"
                       data-direction="north-east"
                       data-actor-id="<?=$entity->id?>"
                    >
                      <i class="fas fa-fw fa-arrow-up"
                         style="transform: rotateY(0deg) rotate(45deg);"
                      ></i>
                    </a>
                  </div>

                  <div class="btn-group w-100">
                    <a href="#"
                       class="btn btn-light js-travel <?=(!$isIntact || !$entity->isIntact ? "disabled" : "")?>"
                       data-direction="west"
                       data-actor-id="<?=$entity->id?>"
                    >
                      <i class="fas fa-fw fa-arrow-left"></i>
                    </a>
                    <a href="#"
                       class="btn btn-light js-travel-map <?=(!$isIntact || !$entity->isIntact ? "disabled" : "")?>"
                       data-actor-id="<?=$entity->id?>"
                    >
                      <i class="fas fa-fw fa-map"></i>
                    </a>
                    <a href="#"
                       class="btn btn-light js-travel <?=(!$isIntact || !$entity->isIntact ? "disabled" : "")?>"
                       data-direction="east"
                       data-actor-id="<?=$entity->id?>"
                    >
                      <i class="fas fa-fw fa-arrow-right"></i>
                    </a>
                  </div>

                  <div class="btn-group w-100">
                    <a href="#"
                       class="btn btn-light js-travel <?=(!$isIntact || !$entity->isIntact ? "disabled" : "")?>"
                       data-direction="south-west"
                       data-actor-id="<?=$entity->id?>"
                    >
                      <i class="fas fa-fw fa-arrow-down"
                         style="transform: rotateY(0deg) rotate(45deg);"
                      ></i>
                    </a>
                    <a href="#"
                       class="btn btn-light js-travel <?=(!$isIntact || !$entity->isIntact ? "disabled" : "")?>"
                       data-direction="south"
                       data-actor-id="<?=$entity->id?>"
                    >
                      <i class="fas fa-fw fa-arrow-down"></i>
                    </a>
                    <a href="#"
                       class="btn btn-light js-travel <?=(!$isIntact || !$entity->isIntact ? "disabled" : "")?>"
                       data-direction="south-east"
                       data-actor-id="<?=$entity->id?>"
                    >
                      <i class="fas fa-fw fa-arrow-down"
                         style="transform: rotateY(0deg) rotate(-45deg);"
                      ></i>
                    </a>
                  </div>

                </div>

                <div class="col">

                  <div class="btn-group-vertical w-100">

                    <button type="button"
                            class="btn btn-light btn-block js-scavenge"
                            data-entity-id="<?=$entity->id?>"
                        <?=(!$isIntact || !$entity->isIntact ? "disabled" : "")?>
                    >
                      Scavenge
                    </button>

                    <button type="button"
                            class="btn btn-light btn-block js-wait"
                            data-entity-id="<?=$entity->id?>"
                        <?=(!$isIntact || !$entity->isIntact ? "disabled" : "")?>
                    >
                      Wait
                    </button>

                    <button type="button"
                            class="btn btn-light btn-block js-commands"
                            data-entity-id="<?=$entity->id?>"
                        <?=(!$isIntact || !$entity->isIntact ? "disabled" : "")?>
                    >
                      Commands
                    </button>

                  </div>

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
