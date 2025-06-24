<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Change Password</title>
        <base href="<?= $web_root ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="//fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20,400,0,0&amp;icon_names=arrow_back,cancel,manage_accounts,password,save,skip_next,skip_previous">
        <link rel="stylesheet" href="css/bootstrap-5.3.3-dist/css/bootstrap.min.css">
        <script src="css/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    </head>
    <body class="d-flex flex-column align-items-center pt-5 bg-secondary-subtle" data-bs-theme="dark" >
        <nav class="navbar bg-body-tertiary fixed-top mx-auto" style="max-width:700px">
            <div class="container-fluid">
                <a href="user/index/"  >
                    <span class="material-symbols-outlined" style="font-size: 32px;" >arrow_back</span>
                </a>
                <form id="inputs" class="form" action="user/change_password" method="post">
                    <button class="btn btn-link p-0" id="save" type="submit" >
                        <span class="material-symbols-outlined" style="font-size: 32px;">save</span>
                    </button>
                </form>
            </div>
        </nav>
        <main class="card d-flex-column justify-text-center w-100 mt-3" style="max-width: 700px; ">
            <div class="card-header">
                <h5> Change your password </h5>
            </div>
            <div class="card-body p-4">
                <input form="inputs" class= "form-control mb-3" id="old_pw" name="old_pw" placeholder="Your current password" type="password" value="<?= $old_pw ?>" >
                <?php if ($errors["current_pass"]): ?>
                <div>
                    <ul>
                        <?php foreach ($errors["current_pass"] as $error): ?>
                            <li class = "text-warning"><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
                <input form="inputs" class= "form-control mb-3" id="new_pw" name="new_pw" placeholder="Your new password" type="password" value="<?= $new_pw ?>">
                <input form="inputs" class= "form-control" id="new_pw_confirm" name="new_pw_confirm" placeholder="Confirm your new password" type="password" value="<?= $new_pw_confirm ?>" >
                <?php if ($errors["other_errors"]): ?>
                <div>
                    <ul>
                        <?php foreach ($errors["other_errors"] as $error): ?>
                            <li class = "text-warning"><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>  
            </div>
        </main>
    </body>
</html>