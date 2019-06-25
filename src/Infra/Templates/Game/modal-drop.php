<div class="modal" tabindex="-1" role="dialog" id="dropModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title js-drop-title"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="range" id="js-drop-slider" value="0" min="0" max="0" list="js-drop-tickmarks" style="width: 100%;" />
                <datalist id="js-drop-tickmarks">
                    <?php for ($i = 0; $i <= 10; $i++) : ?>
                    <option value="<?=$i?>">
                        <?php endfor ?>
                </datalist>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary js-drop-submit w-100"></button>
            </div>
        </div>
    </div>
</div>
