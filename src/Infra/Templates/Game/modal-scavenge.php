<div class="modal" tabindex="-1" role="dialog" id="scavengeModal" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Scavenging Haul</h5>
            </div>

            <div class="modal-body">
                <div class="js-scavenge-error"></div>
                <div class="js-scavenge-haul"></div>
            </div>

            <div class="modal-body bg-light js-scavenge-inventory"
                 style="border-top: 1px solid #dee2e6;"
                 data-inventory-weight="<?=$entity->inventory->weight?>"
                 data-inventory-capacity="<?=$entity->inventory->capacity?>"
            >

                <div class="d-flex justify-content-between"
                     style="font-size: 0.8rem;"
                >
                    <div style="font-weight: 600;">
                        Inventory Capacity
                    </div>
                    <div class="align-self-end">
                        <a href="#"
                           class="js-scavenge-toggle-inventory"
                           data-is-shown="0"
                        >
                            <i class="fas fa-caret-down"></i> Manage
                        </a>
                    </div>
                </div>

                <div class="d-flex"
                     style="margin-top: 0.5rem; font-size: 0.8rem;"
                >
                    <div style="margin-right: 1rem;">
                        <span class="js-scavenge-inventory-weight"><?=$entity->inventory->weight / 1000?></span> / <?=$entity->inventory->capacity / 1000?> kg
                    </div>
                    <div class="flex-fill align-self-center">
                        <div class="progress"
                             style="height: 0.8rem;"
                        >
                            <div class="progress-bar
                                        js-scavenge-inventory-progress
                                        <?=$entity->inventory->isAtCapacity ? "bg-danger" : "bg-primary"?>
                                 "
                                 style="width: <?=$entity->inventory->weightPercentage?>%;"
                            ></div>
                            <div class="progress-bar js-scavenge-inventory-haul-progress"></div>
                        </div>
                    </div>
                    <div class="js-scavenge-inventory-haul-weight"
                         style="margin-left: 1rem; width: 3.4rem; text-align: right;"
                    ></div>
                </div>

                <div class="js-scavenge-inventory-items" style="display: none; margin-top: 1.6rem;">
                    <?php foreach ($entity->inventory->items as $item) : ?>
                        <div class="item-slider d-flex">
                            <div class="align-self-center" style="margin-right: 1rem; white-space: nowrap;">
                                <i class="fas fa-fw fa-<?=$item->icon?>"
                                   data-toggle="popover"
                                   title="<?=$item->label?>"
                                   data-content='<?=$this->e($this->renderHtml5Template("Game/template-item-popover.php", [
                                       'description' => $item->description,
                                       'weight'      => $item->weight >= 1000
                                           ? ($item->weight / 1000) . " kg"
                                           : $item->weight . " g",
                                       'resources'   => $item->resourceLabel,
                                   ]))?>'
                                   data-placement="top"
                                   tabindex="0"
                                ></i>
                                <?=$item->label?>
                            </div>
                            <div class="flex-fill" style="height: 32px;">
                                <input type="range"
                                       min="0"
                                       max="<?=$item->quantity?>"
                                       value="<?=$item->quantity?>"
                                       list="scavenge-tickmarks-<?=$item->id?>"
                                       style="width: 100%"
                                       data-variety-id="<?=$item->id?>"
                                >
                                <datalist id="scavenge-tickmarks-<?=$item->id?>">
                                    <?php for ($i = 0; $i <= $item->quantity; $i++) : ?>
                                    <option value="<?=$i?>">
                                        <?php endfor ?>
                                </datalist>
                            </div>
                            <div class="align-self-center" style="width: 1rem; margin-left: 1rem;">
                                <div class="js-scavenge-inventory-quantity"
                                     data-variety-id="<?=$item->id?>"
                                     style="text-align: right;"
                                ><?=$item->quantity?></div>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>

            </div>

            <div class="modal-footer">
                <div class="btn-group d-flex" style="width: 100%;">

                    <button type="button"
                            class="btn btn-light btn-block js-scavenge-submit"
                            style="border-right: 1px solid #fff;"
                    >Add to Inventory</button>

                    <button type="button"
                            class="btn btn-light js-scavenge-discard"
                            style="border-left: 1px solid #fff;"
                    ><i class="fas fa-trash"></i></button>

                </div>
            </div>

        </div>
    </div>
</div>
