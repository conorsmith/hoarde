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
                                       data-action-id="<?=\ConorSmith\Hoarde\Infra\Repository\ActionRepositoryConfig::CONSUME?>"
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
                                       data-action-id="<?=\ConorSmith\Hoarde\Infra\Repository\ActionRepositoryConfig::CONSUME?>"
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
