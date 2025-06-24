<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <base href="<?= $web_root ?>" >
    <link rel="stylesheet" href="//fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20,400,0,0&amp;icon_names=arrow_back,cancel,manage_accounts,password,save,skip_next,skip_previous">
    <link rel="stylesheet" href="css/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <script src="css/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="d-flex flex-column align-items-center pt-5 bg-secondary-subtle mx-auto" data-bs-theme="dark" >
    <!-- nav -->
    <nav class="navbar bg-body-tertiary fixed-top mx-auto" style="max-width:700px">
        <div class="container-fluid">
            <a href="Forms"  >
                <span class="material-symbols-outlined" style="font-size: 32px;" >arrow_back</span>
            </a>
        </div>
    </nav> 
    <main class="card d-flex-column justify-text-center m-3 w-100 " style="max-width: 700px; ">
        <div class="card-header">
            <h5 class="card-title">Hey <span class="italic_txt yellow_txt" ><?= $user->get_full_name() ?></span> !</h5>
        </div>
        <div class="card-body p-4">
            <h2><a class="btn btn-secondary w-100 mb-3" href="user/edit_profile"> <span class="material-symbols-outlined">manage_accounts</span> Edit Profil</a></h2>
            <h2><a class="btn btn-secondary w-100" href="user/change_password"><span class="material-symbols-outlined">password</span> Change password</a></h2>
        </div>
    </main>
</body>
</html>