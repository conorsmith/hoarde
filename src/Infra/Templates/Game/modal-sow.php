<div class="modal" tabindex="-1" role="dialog" id="sowModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title js-drop-title">Sow Seeds</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <div class="d-flex"
                     style="margin-bottom: 1rem;"
                >
                    <div class="progress  align-self-center w-100 js-capacity-bar"
                    >
                        <div class="progress-bar"></div>
                    </div>
                    <div style="width: 4rem; font-size: 0.8rem; margin-left: 1rem; white-space: nowrap; text-align: right;">
                        <span class="js-capacity-used"></span> / <span class="js-capacity-total"></span>
                    </div>
                </div>

                <div class="js-item-sliders"></div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary js-sow-submit w-100">Sow</button>
            </div>
        </div>
    </div>
</div>
