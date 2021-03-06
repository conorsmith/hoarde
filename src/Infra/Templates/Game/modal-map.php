<div class="modal" tabindex="-1" role="dialog" id="mapModal">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <div class="map" style="text-align: center;">
                    <?php foreach ($map->rows as $row): ?>
                        <div class="map-row">
                            <div class="btn-group">
                                <?php foreach ($row as $location): ?>
                                    <a href="#"
                                       class="btn <?=$location->class?>"
                                       data-x="<?=$location->x?>"
                                       data-y="<?=$location->y?>"
                                       data-location-id="<?=$location->id?>"
                                       data-is-known="<?=$location->isKnown?>"
                                    >
                                        <i class="fas fa-fw <?=$location->icon?>"></i>
                                    </a>
                                <?php endforeach ?>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary w-100" data-dismiss="modal"></button>
            </div>
        </div>
    </div>
</div>
