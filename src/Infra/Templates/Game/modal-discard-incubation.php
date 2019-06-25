<div class="modal" tabindex="-1" role="dialog" id="discardIncubationModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title js-drop-title"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="range" class="js-drop-slider" value="0" min="0" max="0" list="discardIncubationModal-tickmarks" style="width: 100%;" />
                <datalist class="js-drop-tickmarks" id="discardIncubationModal-tickmarks">
                </datalist>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary js-drop-submit w-100"></button>
            </div>
        </div>
    </div>
</div>
