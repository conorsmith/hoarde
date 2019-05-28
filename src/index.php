<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <title>Hoarde</title>

    <style>

        .progress.resource .progress-bar {
            width: 20%;
            border-left: 1px solid #e9ecef;
            box-sizing: border-box;
        }

        .progress.resource .progress-bar:first-child {
            border-left: none;
        }
    </style>
</head>
<body style="margin-top: 1rem;">

<div class="container">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">Turn <?=$turnIndex?></li>
      </ol>
    </nav>

    <div class="row">
        <div class="col-sm-4 offset-sm-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Entity</h5>
                    <?php foreach ($resourceLevels as $resourceLevel) : ?>
                        <p>
                            <strong>Resource</strong>
                            <div class="progress resource">
                                <?php for ($i = 0; $i < $resourceLevel; $i++): ?>
                                    <div class="progress-bar"></div>
                                <?php endfor; ?>
                            </div>
                        </p>
                    <?php endforeach ?>
                  <form method="POST">
                    <input type="hidden" name="action" value="wait" />
                    <button type="submit" class="btn btn-light btn-block" style="margin-bottom: 1rem;">Wait</button>
                  </form>
                  <form method="POST">
                    <input type="hidden" name="action" value="gather" />
                    <button type="submit" class="btn btn-light btn-block">Gather Resource</button>
                  </form>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" style="float: right;">
      <input type="hidden" name="action" value="restart" />
      <button type="submit" class="btn btn-link">Restart</button>
    </form>

</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>
