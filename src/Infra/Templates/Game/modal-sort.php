<div class="modal" tabindex="-1" role="dialog" id="sortModal">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sort Entities</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <ul class="list-group js-sortable">

                    <?php foreach ($entities as $entity) : ?>
                        <?php if ($entity->isPlaced) : ?>

                            <li
                               class="list-group-item"
                               style="color: #333; cursor: pointer;"
                               data-entity-id="<?=$entity->id?>"
                            >
                                <?php if ($entity->isIntact) : ?>
                                    <i class="fas fa-fw fa-<?=$entity->icon?>"></i>
                                <?php else : ?>
                                    <i class="fas fa-fw fa-skull-crossbones"></i>
                                <?php endif ?>
                                <?=$entity->label?>
                            </li>

                        <?php endif ?>
                    <?php endforeach ?>

                </ul>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary js-sort-submit w-100">Sort</button>
            </div>
        </div>
    </div>
</div>
