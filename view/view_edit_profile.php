<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit profile</title>
    <base href="<?= $web_root ?>" >
    <link rel="stylesheet" href="//fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20,400,0,0&amp;icon_names=arrow_back,cancel,save,skip_next,skip_previous">
    <link rel="stylesheet" href="css/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <script src="css/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="d-flex flex-column align-items-center pt-5 bg-secondary-subtle" data-bs-theme="dark" >
    <nav class="navbar bg-body-tertiary fixed-top mx-auto" style="max-width:700px">
        <div class="container-fluid">
            <a href="user/index/"  >
                <span class="material-symbols-outlined" style="font-size: 32px;" >arrow_back</span>
            </a>
            <form id="editProfile" action="user/edit_profile/<?= $user->get_id() ?>" method="post">
                <button class="btn btn-link p-0" id="save" type="submit" >
                    <span class="material-symbols-outlined" style="font-size: 32px;">save</span>
                </button>
            </form>
        </div>
    </nav>     
    <main class="card d-flex-column justify-text-center w-100 mt-3" style="max-width: 700px; ">
        <div class="card-header">
            <h5> Edit your profile : </h5>
        </div>
        <div class="card-body p-4">
            <div class="mb-3">
                <label>Change your name :</label>    
                <input class="form-control" form="editProfile" id="name" name="name" type="text" value="<?= $user->get_full_name() ?>" autofocus>
            </div>
            <!-- affichage des erreurs de name -->
            <?php if (!empty($errors["name"])): ?>
                <div>
                    <ul>
                        <?php foreach ($errors["name"] as $error): ?>
                            <li class="text-warning" ><?= $error ?> </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div>
                <label>Change your email :</label>
                <input class="form-control" form="editProfile" id="email" name="email" type="text" value="<?= $user->get_email() ?>">
            </div>
            <!-- affichage des erreurs de description -->
            <?php if (!empty($errors["email"])): ?>
                <div>
                    <ul>
                        <?php foreach ($errors["email"] as $error): ?>
                            <li class="text-warning" ><?= $error ?> </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>