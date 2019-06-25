<div>
    <?php foreach ($entity->inventory->items as $item) : ?>
        <div class="btn-group inventory-item d-flex justify-content-end">

            <div class="btn btn-block flex-grow-1 d-flex align-items-baseline justify-content-between inventory-item-label"
                 style="text-align: left;"
            >
                <div>
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
                <div>
                    <span class="badge"><?=$item->quantity?></span>
                </div>
            </div>

            <button type="button"
                    class="btn btn-light dropdown-toggle"
                    data-toggle="dropdown"
                    style="border-top-left-radius: 0;
                                     border-bottom-left-radius: 0;
                                     padding-top: 0.7rem;
                                     padding-bottom: 0.5rem;"
                    <?=($isIntact ? "" : "disabled")?>
            ></button>
            <div class="dropdown-menu dropdown-menu-right w-100">

                <h6 class="dropdown-header"><?=$item->label?></h6>

                <?php foreach ($item->performableActions as $action) : ?>
                    <a href="#"
                       class="dropdown-item <?=$action->jsClass?>"
                       data-entity-id="<?=$entity->id?>"
                       data-actor-id="<?=$action->actorId?>"
                       data-item-id="<?=$item->varietyId?>"
                       data-action-id="<?=$action->id?>"
                    >
                        <i class="fas fa-fw fa-<?=$action->icon?>"></i>
                        <?=$action->label?>
                    </a>
                <?php endforeach ?>

                <a href="#"
                   class="dropdown-item"
                   data-toggle="modal"
                   data-target="#dropModal"
                   data-entity-id="<?=$entity->id?>"
                   data-item-id="<?=$item->varietyId?>"
                >
                    <i class="fas fa-fw fa-trash"></i>
                    Discard
                </a>

                <a href="#"
                   class="dropdown-item js-info"
                   data-item-id="<?=$item->varietyId?>"
                >
                    <i class="fas fa-fw fa-info-circle"></i>
                    Info
                </a>

            </div>

        </div>
    <?php endforeach ?>
</div>

<div class="progress"
     style="height: 0.5rem; margin-top: 1rem;"
>
    <div class="progress-bar <?=$entity->inventory->isAtCapacity ? "bg-danger" : "bg-primary"?>"
         style="width: <?=($entity->inventory->weight / $entity->inventory->capacity * 100)?>%;"
    ></div>
</div>
