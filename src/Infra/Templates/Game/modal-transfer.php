<div class="modal" tabindex="-1" role="dialog" id="transferModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Transfer Items</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <?php $entities = [$entity, $crate]; ?>

            <?php foreach ($entities as $entity) : ?>

              <div class="modal-body js-inventory"
                   data-entity-id="<?=$entity->id?>">

                  <div class="alert alert-danger js-error" style="display: none; margin-bottom: 1rem;"></div>

                  <div class="d-flex justify-content-between"
                       style="margin-bottom: 0.6rem;"
                  >

                      <div style="margin-right: 1rem; font-weight: 900;">
                          <i class="fas fa-fw fa-<?=$entity->icon?>"></i> <?=$entity->label?>
                      </div>
                      <div class="align-self-end"
                           style="margin-left: 1rem; font-size: 0.8rem; text-align: right;"
                      >
                          <span class="js-inventory-weight"><?=$entity->inventory->weight / 1000?></span> / <?=$entity->inventory->capacity / 1000?> kg
                      </div>
                  </div>

                  <div style="margin-bottom: 1rem;">
                      <div class="progress js-capacity-bar"
                           style="height: 0.6rem;"
                           data-entity-id="<?=$entity->id?>"
                           data-weight="<?=$entity->inventory->weight?>"
                           data-capacity="<?=$entity->inventory->capacity?>"
                      >
                          <div class="progress-bar
                                              <?php if ($entity->inventory->weight < $entity->inventory->capacity) : ?>
                                                  bg-primary
                                              <?php else : ?>
                                                  bg-danger
                                              <?php endif ?>
                                         "
                               style="width: <?=$entity->inventory->weight / $entity->inventory->capacity * 100?>%;"
                          ></div>
                          <div class="progress-bar" style="width: 0;"></div>
                      </div>
                  </div>

                  <?php foreach ($entity->inventory->items as $item) : ?>
                      <div class="item-slider d-flex">

                          <div class="align-self-center"
                               style="margin-right: 1rem;"
                          >
                              <i class="fas fa-fw fa-<?=$item->icon?>"
                                 data-toggle="popover"
                                 title="<?=$item->label?>"
                                 data-content='
                                    <p><?=$item->description?></p>
                                    <div><span class="popover-label">Weight:</span> <?=$item->weight >= 1000
                                           ? ($item->weight / 1000)." kg"
                                           : $item->weight . " g" ?>
                                    </div>
                                    <div><span class="popover-label">Resource:</span> <?=$item->resourceLabel?></div>
                                 '
                                 data-placement="top"
                                 tabindex="0"
                              ></i>
                              <?=$item->label?>
                          </div>

                          <div class="flex-fill" style="height: 32px;">
                              <input type="range"
                                     min="0"
                                     max="<?=$item->quantity?>"
                                     value="0"
                                     list="item-slider-<?=$entity->id?>-<?=$item->varietyId?>"
                                     data-variety-id="<?=$item->varietyId?>"
                                     data-entity-id="<?=$entity->id?>"
                                     data-weight="<?=$item->weight?>"
                                     style="width: 100%"
                              >
                              <datalist id="item-slider-<?=$entity->id?>-<?=$item->varietyId?>">
                                  <?php for ($i = 0; $i <= $item->quantity; $i++) : ?>
                                      <option value="<?=$i?>">
                                  <?php endfor ?>
                              </datalist>
                          </div>

                          <div class="align-self-center"
                               style="margin-left: 1rem; text-align: right; width: 1.4rem;"
                          >
                              <span class="js-item-counter"
                                    data-variety-id="<?=$item->varietyId?>"
                                    data-entity-id="<?=$entity->id?>"
                              >0</span>
                          </div>

                      </div>
                  <?php endforeach ?>

              </div>

            <?php endforeach ?>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary js-submit">Transfer</button>
            </div>

        </div>
    </div>
</div>
