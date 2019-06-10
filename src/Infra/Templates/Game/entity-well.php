<div class="col-lg-4 mb-3">
    <div class="card">
        <div class="card-body">

            <h5 class="card-title">
                <i class="fas fa-fw fa-<?=$entity->icon?>"></i>
                <?=$entity->label?>
                <?php if (!$entity->isConstructed) : ?>
                    <span class="text-muted"
                          style="font-size: 1rem; font-weight: 400;"
                    >Under Construction</span>
                <?php endif ?>
            </h5>

            <?php if (!$entity->isConstructed) : ?>
                <div class="progress construction"
                     style="margin-bottom: 0.6rem;"
                >
                    <?php for ($i = 0; $i < $entity->requiredConstructionSteps - $entity->remainingConstructionSteps; $i++): ?>
                        <div class="progress-bar"
                             style="width: <?=(100 / $entity->requiredConstructionSteps)?>%;"
                        ></div>
                    <?php endfor; ?>
                </div>

                <button type="button"
                        class="btn btn-light btn-block js-construct-continue"
                        data-actor-id="<?=$actor->id?>"
                        data-target-id="<?=$entity->id?>"
                    <?=($isIntact ? "" : "disabled")?>
                >Continue Construction</button>
            <?php endif ?>

            <hr>

            <div class="actions">

                <button type="button"
                        class="btn btn-light btn-block js-fetch"
                        data-entity-id="<?=$actor->id?>"
                        data-well-id="<?=$entity->id?>"
                    <?=($isIntact && $entity->isConstructed ? "" : "disabled")?>
                >Fetch Water</button>

            </div>

        </div>
    </div>
</div>
