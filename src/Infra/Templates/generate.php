<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">
    <link href="data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQEAYAAABPYyMiAAAABmJLR0T///////8JWPfcAAAACXBIWXMAAABIAAAASABGyWs+AAAAF0lEQVRIx2NgGAWjYBSMglEwCkbBSAcACBAAAeaR9cIAAAAASUVORK5CYII=" rel="icon" type="image/x-icon">

    <title>Hoarde</title>

</head>
<body style="margin-top: 1rem;">

<div class="container">

    <div class="jumbotron jumbotron-fluid">
        <div class="container" style="text-align: center;">
            <h1 class="display-4" style="font-weight: 900;">HOARDE</h1>
                <button type="submit"
                        class="btn btn-primary btn-lg"
                        data-toggle="modal"
                        data-target="#generateModal"
                        style="font-weight: 100;"
                >begin</button>
        </div>
    </div>

</div>

<div class="modal" tabindex="-1" role="dialog" id="generateModal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" style="font-weight: 900;">HOARDE</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <div class="form-group">
            <label>Name Your Hoarder</label>
            <input type="text" name="label" class="form-control">
          </div>
          <div style="text-align: center;" id="hoarderIcon">
            <input type="hidden" name="icon" value="user">
            <div style="margin-bottom: 4px;">
              <button type="button" class="btn btn-lg btn-light active">
                <i class="fas fa-fw fa-user" data-icon="user"></i>
              </button>
              <button type="button" class="btn btn-lg btn-light">
                <i class="fas fa-fw fa-user-graduate" data-icon="user-graduate"></i>
              </button>
              <button type="button" class="btn btn-lg btn-light">
                <i class="fas fa-fw fa-user-secret" data-icon="user-secret"></i>
              </button>
            </div>
            <div style="margin-bottom: 4px;">
              <button type="button" class="btn btn-lg btn-light">
                <i class="fas fa-fw fa-user-injured" data-icon="user-injured"></i>
              </button>
              <button type="button" class="btn btn-lg btn-light">
                <i class="fas fa-fw fa-user-tie" data-icon="user-tie"></i>
              </button>
              <button type="button" class="btn btn-lg btn-light">
                <i class="fas fa-fw fa-user-nurse" data-icon="user-nurse"></i>
              </button>
            </div>
            <div>
              <button type="button" class="btn btn-lg btn-light">
                <i class="fas fa-fw fa-user-ninja" data-icon="user-ninja"></i>
              </button>
              <button type="button" class="btn btn-lg btn-light">
                <i class="fas fa-fw fa-user-md" data-icon="user-md"></i>
              </button>
              <button type="button" class="btn btn-lg btn-light">
                <i class="fas fa-fw fa-user-astronaut" data-icon="user-astronaut"></i>
              </button>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Begin</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>


<script>
    var iconButtons = document.getElementById("hoarderIcon").querySelectorAll("button");

    for (var i = 0; i < iconButtons.length; i++) {
        iconButtons[i].onclick = function (e) {
            for (var i = 0; i < iconButtons.length; i++) {
                iconButtons[i].classList.remove("active");
            }
            e.currentTarget.classList.add("active");
            document.getElementById("hoarderIcon").querySelector("input[name='icon']").value
                = e.currentTarget.querySelector("i").dataset.icon;
        };
    }
</script>

</body>
</html>
