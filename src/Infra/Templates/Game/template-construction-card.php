<template id="construction-card">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title d-flex align-items-center justify-content-between tmpl-collapse-target"
                style="margin-bottom: 0;"
                data-toggle="collapse"
            >
                <div>
                    <i class="fas fa-fw tmpl-icon"></i>
                    <span class="tmpl-label"></span>
                </div>
                <div class="badge"
                     style="font-size: 0.8rem; font-weight: 600;"
                >
                  <span class="tmpl-turns"></span> turns
                </div>
            </h5>
            <div class="collapse tmpl-collapse-id"
                 style="margin-top: 0.75rem;"
            >
                <ul class="list-group list-group-flush">
                    <li class="list-group-item text-muted tmpl-tools" style="font-weight: 600;">
                        Tools
                    </li>
                    <li class="list-group-item text-muted tmpl-materials" style="font-weight: 600; padding-top: 1.25rem;">
                        Materials
                    </li>
                </ul>
                <div style="margin-top: 1rem;">
                    <button class="btn btn-primary btn-block">
                        Start Digging <span class="tmpl-button-label"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
