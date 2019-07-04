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

              <button type="button"
                      class="btn btn-light btn-block js-scavenge"
                      data-entity-id="<?=$entity->id?>"
                  <?=(!$isIntact ? "disabled" : "")?>
              >
                Scavenge
              </button>

              <div class="travel-btn-group">

                <div class="btn-group w-100">
                  <a href="#"
                     class="btn btn-light js-travel"
                     data-direction="north-west"
                     data-actor-id="<?=$entity->id?>"
                  >
                    <i class="fas fa-fw fa-arrow-up"
                       style="transform: rotateY(0deg) rotate(-45deg);"
                    ></i>
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
                     class="btn btn-light js-travel"
                     data-direction="west"
                     data-actor-id="<?=$entity->id?>"
                  >
                    <i class="fas fa-fw fa-arrow-left"></i>
                  </a>
                  <a href="#"
                     class="btn btn-light js-travel-map disabled"
                     data-actor-id="<?=$entity->id?>"
                  >
                    <i class="fas fa-fw fa-map"></i>
                  </a>
                  <a href="#"
                     class="btn btn-light js-travel"
                     data-direction="east"
                     data-actor-id="<?=$entity->id?>"
                  >
                    <i class="fas fa-fw fa-arrow-right"></i>
                  </a>
                </div>

                <div class="btn-group w-100">
                  <a href="#"
                     class="btn btn-light js-travel"
                     data-direction="south-west"
                     data-actor-id="<?=$entity->id?>"
                  >
                    <i class="fas fa-fw fa-arrow-down"
                       style="transform: rotateY(0deg) rotate(45deg);"
                    ></i>
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
                     data-direction="south-east"
                     data-actor-id="<?=$entity->id?>"
                  >
                    <i class="fas fa-fw fa-arrow-down"
                       style="transform: rotateY(0deg) rotate(-45deg);"
                    ></i>
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
