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
