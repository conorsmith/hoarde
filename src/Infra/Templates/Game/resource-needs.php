
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
                        data-actor-id="<?=$resource->actorId?>"
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
                           data-actor-id="<?=$resource->actorId?>"
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
                           data-actor-id="<?=$resource->actorId?>"
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
