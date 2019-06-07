<div class="col-lg-4 mb-3">
    <div class="card">
        <div class="card-body">

            <h5 class="card-title d-flex justify-content-between">
                <div>
                    <i class="fas fa-fw fa-<?=$entity->icon?>"></i>
                    <?=$entity->label?>
                </div>
                <a href="#"
                   style="display: block; font-size: 1rem; color: #888;"
                   data-toggle="modal"
                   data-target="#settingsModal"
                   data-entity-id="<?=$entity->id?>"
                >
                    <i class="fas fa-cog"></i>
                </a>
            </h5>

            <p><strong>Inventory</strong></p>

            <?=$this->renderTemplate("Game/inventory.php", [
                'entity'   => $entity,
                'isIntact' => $isIntact,
            ])?>

            <hr>

            <div class="actions">

                <button type="button"
                        class="btn btn-light btn-block js-transfer"
                        data-toggle="modal"
                        data-target="#transferModal"
                        data-source-id="<?=$entity->id?>"
                        data-destination-id="<?=$entity->inventory->initialTransferEntityId?>"
                    <?=($isIntact ? "" : "disabled")?>
                >Transfer Items</button>

            </div>

        </div>
    </div>
</div>