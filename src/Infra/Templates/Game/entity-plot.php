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

                            <a href="#"
                               class="dropdown-item disabled"
                            >
                              <i class="fas fa-fw fa-seedling"></i>
                              Harvest
                            </a>

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
                    <?=($isIntact && $entity->construction->isConstructed ? "" : "disabled")?>
                >Sow Seeds</button>

            </div>

        </div>
    </div>
</div>
