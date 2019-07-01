<div class="modal" tabindex="-1" role="dialog" id="mapModal">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Map</h5>
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
                                       class="btn btn-sm <?=$location->isKnown ? "btn-outline-secondary" : "btn-secondary"?>"
                                       data-x="<?=$location->x?>"
                                       data-y="<?=$location->y?>"
                                       style="font-size: 0.6rem; line-height: 2.1; padding: 0.15rem 0.4rem;"
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
                <button type="button" class="btn btn-secondary w-100" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
