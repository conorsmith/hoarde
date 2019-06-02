<div class="modal" tabindex="-1" role="dialog" id="transferModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Transfer Items</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body js-inventory" data-entity-id="<?=$entity->id?>">

                <div class="alert alert-danger js-error" style="display: none; margin-bottom: 1rem;"></div>

                <div class="d-flex"
                     style="margin-bottom: 1rem;"
                >

                    <div style="margin-right: 1rem; font-weight: 900;">
                        <i class="fas fa-fw fa-<?=$entity->icon?>"></i> <?=$entity->label?>
                    </div>
                    <div class="flex-fill align-self-center">
                        <div class="progress"
                             style="height: 0.8rem;"
                        >
                            <div class="progress-bar
                                        js-scavenge-inventory-progress
                                        bg-primary
                                     "
                                 style="width: <?=$inventoryWeight?>%;"
                            ></div>
                            <div class="progress-bar"></div>
                        </div>
                    </div>
                    <div style="margin-left: 1rem; font-size: 0.8rem;">
                        <span><?=$entity->inventory->weight / 1000?></span> / <?=$entity->inventory->capacity / 1000?> kg
                    </div>
                </div>

                <?php foreach ($entity->inventory->items as $item) : ?>
                    <div class="item-slider d-flex">

                        <div class="align-self-center" style="margin-right: 1rem;">
                            <i class="fas fa-fw fa-<?=$item->icon?>"></i>
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
                                   style="width: 100%"
                            >
                            <datalist id="item-slider-<?=$entity->id?>-<?=$item->varietyId?>">
                                <?php for ($i = 0; $i <= $item->quantity; $i++) : ?>
                                    <option value="<?=$i?>">
                                <?php endfor ?>
                            </datalist>
                        </div>

                        <div class="align-self-center">
                            <span class="js-item-counter"
                                  data-variety-id="<?=$item->varietyId?>"
                                  data-entity-id="<?=$entity->id?>"
                                  style="margin-left: 1rem; text-align: right;"
                            >0</span>
                        </div>

                    </div>
                <?php endforeach ?>

            </div>

            <div class="modal-body js-inventory"
                 style="border-top: 1px solid #dee2e6;"
                 data-entity-id="<?=$crate->id?>"
            >

                <div class="d-flex"
                     style="margin-bottom: 1rem;"
                >

                    <div style="margin-right: 1rem; font-weight: 900;">
                        <i class="fas fa-fw fa-<?=$crate->icon?>"></i> <?=$crate->label?>
                    </div>
                    <div class="flex-fill align-self-center">
                        <div class="progress"
                             style="height: 0.8rem;"
                        >
                            <div class="progress-bar
                                        js-scavenge-inventory-progress
                                        bg-primary
                                     "
                                 style="width: <?=$crate->inventory->weight / $crate->inventory->capacity * 100?>%;"
                            ></div>
                            <div class="progress-bar"></div>
                        </div>
                    </div>
                    <div style="margin-left: 1rem; font-size: 0.8rem;">
                        <span><?=$crate->inventory->weight / 1000?></span> / <?=$crate->inventory->capacity / 1000?> kg
                    </div>
                </div>

                <?php foreach ($crate->inventory->items as $item) : ?>
                    <div class="item-slider d-flex">

                        <div class="align-self-center" style="margin-right: 1rem;">
                            <i class="fas fa-fw fa-<?=$item->icon?>"></i>
                            <?=$item->label?>
                        </div>

                        <div class="flex-fill" style="height: 32px;">
                            <input type="range"
                                   min="0"
                                   max="<?=$item->quantity?>"
                                   value="0"
                                   list="item-slider-<?=$crate->id?>-<?=$item->varietyId?>"
                                   data-variety-id="<?=$item->varietyId?>"
                                   data-entity-id="<?=$crate->id?>"
                                   style="width: 100%"
                            >
                            <datalist id="item-slider-<?=$crate->id?>-<?=$item->varietyId?>">
                                <?php for ($i = 0; $i <= $item->quantity; $i++) : ?>
                                <option value="<?=$i?>">
                                    <?php endfor ?>
                            </datalist>
                        </div>

                        <div class="align-self-center">
                            <span class="js-item-counter"
                                  data-variety-id="<?=$item->varietyId?>"
                                  data-entity-id="<?=$crate->id?>"
                                  style="margin-left: 1rem; text-align: right;"
                            >0</span>
                        </div>

                    </div>
                <?php endforeach ?>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary js-submit">Transfer</button>
            </div>

        </div>
    </div>
</div>
