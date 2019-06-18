<div class="modal" tabindex="-1" role="dialog" id="harvestModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Harvest</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <div class="d-flex justify-content-between"
                     style="margin-bottom: 0.6rem;"
                >

                    <div class="dropdown">
                        <a href="#"
                           class="transfer-entity dropdown-toggle"
                           data-toggle="dropdown"
                           style="margin-right: 1rem; font-weight: 900; display: block;"
                        >
                            <i class="fas fa-fw js-icon"></i>
                            <span class="js-label"></span>
                        </a>
                        <div class="dropdown-menu js-entity-selector"></div>
                    </div>

                    <div class="align-self-end js-capacity-counter"
                         style="margin-left: 1rem; font-size: 0.8rem; text-align: right;"
                    >
                        <span class="js-inventory-weight"></span>
                        /
                        <span class="js-inventory-capacity"></span> kg
                    </div>
                </div>

                <div style="margin-bottom: 1rem;">
                    <div class="progress js-capacity-bar"
                         style="height: 0.6rem;"
                    >
                        <div class="progress-bar"></div>
                        <div class="progress-bar" style="width: 0;"></div>
                    </div>
                </div>

                <div class="js-item-sliders"></div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary js-harvest-submit w-100">Harvest</button>
            </div>
        </div>
    </div>
</div>
