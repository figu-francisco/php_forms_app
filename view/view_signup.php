<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Signup</title>
        <base href="<?= $web_root ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/bootstrap-5.3.3-dist/css/bootstrap.min.css">
        <script src="css/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    </head>
    <body class="container d-flex flex-column align-items-center py-5 bg-secondary-subtle" data-bs-theme="dark" style="max-width:700px" >
        <div class="card  w-100">
            <div class="card-header text-center">
                <h4 class="card-title fw-bold">Welcome to Forms!</h4>
                <P class="card-subtitle text-warning">
                    This is a demo app. Feel free to explore, please use dummy data.
                </p>
            </div>
            <div class="card-body m-3 d-flex flex-column align-items-center form-group">
                <h2 class="card-text mb-3">Signup</h2>
                <input class= "form-control mb-3" form="sign_up" id="email" name="email" placeholder="Email" type="email" value="<?= $user->get_email() ?>">
                <?php if (isset($errors["email"])): ?>
                    <div>
                        <ul>
                            <?php foreach ($errors["email"] as $error): ?>
                                <li class="error_list" ><?= $error ?> </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <input class= "form-control mb-3" form="sign_up" id="full_name" name="full_name" placeholder="Full Name" type="text" value="<?= $user->get_full_name() ?>">
                
                <?php if (isset($errors["name"])): ?>
                    <div>
                        <ul>
                            <?php foreach ($errors["name"] as $error): ?>
                                <li class="text-warning" ><?= $error ?> </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>       
                <input class= "form-control mb-3" form="sign_up" id="password" name="password" placeholder="Password" type="password" value="<?= $password ?>">
                <input class= "form-control mb-3" form="sign_up" id="password_confirm" name="password_confirm" placeholder="Confirm your password" type="password" value="<?= $password_confirm ?>">
                <?php if (isset($errors["password"])): ?>
                    <div>
                        <ul>
                            <?php foreach ($errors["password"] as $error): ?>
                                <li class="text-warning" ><?= $error ?> </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <hr>
                <br>
                <form id="sign_up" class="w-100" action="main/signup" method="post">         
                        <input class= "btn btn-primary w-100 mb-3" type="submit" value="Sign up">
                </form>  
                <a class="w-100" href="main">
                    <span class="btn btn-secondary w-100 mb-3">Cancel</span>
                </a>                
            </div>
        </div>
    </body>
</html>