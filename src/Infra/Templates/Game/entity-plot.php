<div class="col-lg-4 mb-3">
    <div class="card">
        <div class="card-body">

            <h5 class="card-title">
                <i class="fas fa-fw fa-<?=$entity->icon?>"></i>
                <?=$entity->label?>
                <?php if (!$entity->construction->isConstructed) : ?>
                    <span class="text-muted"
                          style="font-size: 1rem; font-weight: 400; white-space: nowrap;"
                    >Under Construction</span>
                <?php endif ?>
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

            <?php if (!$entity->construction->isConstructed) : ?>
                <div class="progress construction"
                     style="margin-bottom: 0.6rem;"
                >
                    <?php for ($i = 0; $i < $entity->construction->requiredSteps - $entity->construction->remainingSteps; $i++): ?>
                        <div class="progress-bar"
                             style="width: <?=(100 / $entity->construction->requiredSteps)?>%;"
                        ></div>
                    <?php endfor; ?>
                </div>

                <?php if (!$entity->construction->actor->hasTools) : ?>
                    <div class="alert alert-warning"
                         style="font-size: 0.8rem;"
                    >
                        To continue construction, <?=$entity->construction->actor->label?> must have: <strong>Shovel</strong>
                    </div>
                <?php endif ?>

                <button type="button"
                        class="btn btn-light btn-block js-construct-continue"
                        data-actor-id="<?=$entity->construction->actor->id?>"
                        data-target-id="<?=$entity->id?>"
                        data-construction-variety-id="<?=$entity->varietyId?>"
                    <?=($isIntact && $entity->construction->actor->hasTools ? "" : "disabled")?>
                >Continue Construction</button>

            <?php endif ?>

            <hr>

            <?php foreach ($entity->incubator as $incubation) : ?>
                <div style="margin-bottom: 0.6rem;">

                    <div class="btn-group inventory-item d-flex justify-content-end"
                         style="border-bottom-left-radius: 0; border-bottom-right-radius: 0;"
                    >

                        <div class="btn btn-block flex-grow-1 d-flex justify-content-between inventory-item-label"
                             style="text-align: left;"
                        >
                            <div style="white-space: nowrap;">
                                <i class="fas fa-fw fa-<?=$incubation->icon?>"></i>
                                <?=$incubation->label?>
                            </div>
                            <div>
                                <span class="badge"><?=$incubation->quantity?></span>
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

                            <h6 class="dropdown-header"><?=$incubation->label?></h6>

                            <?php foreach ($incubation->performableActions as $action) : ?>
                              <a href="#"
                                 class="dropdown-item <?=$action->jsClass?> <?=$action->isDisabled ? "disabled" : ""?>"
                                 data-entity-id="<?=$entity->id?>"
                                 data-actor-id="<?=$entity->construction->actor->id?>"
                                 data-variety-id="<?=$incubation->varietyId?>"
                                 data-action-id="<?=$action->id?>"
                              >
                                <i class="fas fa-fw fa-<?=$action->icon?>"></i>
                                  <?=$action->label?>
                              </a>
                            <?php endforeach ?>

                        </div>

                    </div>
                    <div class="progress w-100"
                         style="height: 0.4rem; border-top-left-radius: 0; border-top-right-radius: 0;"
                    >
                      <div class="progress-bar <?=$incubation->construction->percentage >= 100 ? "bg-success" : "bg-primary"?>"
                           style="width: <?=$incubation->construction->percentage?>%;"
                      ></div>
                    </div>

                </div>
            <?php endforeach ?>

            <hr>

            <div class="actions">

                <button type="button"
                        class="btn btn-light btn-block js-sow"
                        data-actor-id="<?=$entity->construction->actor->id?>"
                        data-entity-id="<?=$entity->id?>"
                        data-capacity-available="<?=50?>"
                        data-capacity-used="<?=$entity->incubatorCapacityUsed?>"
                    <?=($isIntact && $entity->construction->isConstructed ? "" : "disabled")?>
                >Sow Seeds</button>

            </div>

        </div>
    </div>
</div>
