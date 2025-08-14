<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>welcome to Francisco's Forms</title>
        <base href="<?= $web_root ?>">
        <link rel="stylesheet" href="css/bootstrap-5.3.3-dist/css/bootstrap.min.css">
        <script src="css/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    </head>
    <body class="container d-flex flex-column align-items-center py-5 bg-secondary-subtle" data-bs-theme="dark" style="max-width:700px" >
        <div class="card text-center">
            <div class="card-header">
                <h4 class="card-title fw-bold">Welcome to Forms!</h4>
                <P class="card-subtitle text-warning">
                    gThis is a demo app. Feel free to explore, please use dummy data. 
                    You can reset the database anytime using the button below.
                </p>
            </div>
            
            <div class="card-body my-3 w-100">
                <h2>Sign gtin</h2>
                <hr>
                <div>
                    <form class="w-100" action="main/login" method="post">
                        <input class= "form-control mb-3" id="email" name="email" placeholder="your email" type="text" value="<?= $email ?>">
                        <input class= "form-control mb-3" id="password" name="password" placeholder="your password" type="password" value="<?= $password ?>">
                        <input class= "btn btn-primary w-100 mb-3 " type="submit" value="Log In">
                    </form>
                    <?php if (count($errors) != 0): ?>
                        <div>
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li class="text-warning"><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
                <div>
                    <form  action="main/login" method='post' >
                        <input name="email" type="text" value="<?= Configuration::get("default.guest.mail") ?>" hidden>
                        <input name="password" type="text" value="<?= Configuration::get("default.password") ?>" hidden>
                        <input class= "btn btn-secondary mb-3 w-100" type="submit" value="Continue as guest">
                    </form>
                </div>
                <a class="link_click_to_subscribe" href="main/signup">New here ? Click here to subscribe !</a>
                <hr>
                <?php if(Configuration::is_dev()):?>
                    <div>
                        <h4 class="yellow_txt">Dummy accounts</h4>
                        <form class="link" action="main/login" method='post' >
                            <input name="email" type="text" value="<?= Configuration::get("default.user.joe.doe") ?>" hidden>
                            <input name="password" type="text" value="<?= Configuration::get("default.password") ?>" hidden>
                            <input class="btn btn-outline-secondary w-100 mb-2" type='submit' value='Login as joe.doe'>
                        </form>
                        <form class="link" action="main/login" method='post' >
                            <input name="email" type="text" value="<?= Configuration::get("default.user.jane.smith") ?>" hidden>
                            <input name="password" type="text" value="<?= Configuration::get("default.password") ?>" hidden>
                            <input class="btn btn-outline-secondary w-100 mb-2" type='submit' value='Login as jane.smith'>
                        </form>
                        <form class="link" action="main/login" method='post' >
                            <input name="email" type="text" value="<?= Configuration::get("default.user.john.roe") ?>" hidden>
                            <input name="password" type="text" value="<?= Configuration::get("default.password") ?>" hidden>
                            <input class="btn btn-outline-secondary w-100 mb-2" type='submit' value='Login as john.roe'>
                        </form>
                        <form class="link" action="main/login" method='post' >
                            <input name="email" type="text" value="<?= Configuration::get("default.user.mary.major") ?>" hidden>
                            <input name="password" type="text" value="<?= Configuration::get("default.password") ?>" hidden>
                            <input class="btn btn-outline-secondary w-100" type='submit' value='Login as mary.major'>
                        </form>
                        <hr>
                        <a href ="Setup/install" > <span class= "btn btn-outline-primary w-100">Reset DB</span></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </body>
</html>