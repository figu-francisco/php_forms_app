<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>view instances</title>
    <base href="<?= $web_root ?>" >
    <link rel="stylesheet" href="//fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20,400,0,0&amp;icon_names=arrow_back,cancel,save,skip_next,skip_previous">
    <link rel="stylesheet" href="css/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <script src="css/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="d-flex flex-column pt-5 bg-secondary-subtle mx-auto" data-bs-theme="dark" style="max-width:700px"> 
    <nav class="navbar bg-body-tertiary fixed-top mx-auto" style="max-width: 700px; ">
        <div class="container-fluid">
            <a href="form/index/<?= $form->get_id() ?>/<?=$encoded_search?>" >
                <span class="icons_header material-symbols-outlined" style="font-size: 32px;">arrow_back</span>
            </a>
        </div>
    </nav>
    <main class="w-100">
        <h2 class='fw-bold m-3 text-secondary lh-0'> Submitted instance(s) : </h2>
        <h2 class='fw-bold m-3 lh-0'> <?= $form->get_title() ?></h2>
        <div class="mx-3">
            <?php if ($push_delete_button):?>
                <a class="red_txt error_list italic_txt align_left" > You must select instances to delete them </a>
            <?php endif; ?>
            <?php foreach ($submitted_intances as $instance): ?>
                <div class="card mb-3">
                    <div class="card-header d-flex align-items-center">
                        <h6 class=""> <?= $instance->get_completed() ?> </h6>
                        <input class="form-check ms-auto" type="checkbox" form="delete_intances" id="id<?= $instance->get_id() ?>" name="id<?= $instance->get_id() ?>" value="<?= $instance->get_id() ?>">
                    </div>
                    <div class="card-body">
                        <h6 class="fst-italic" > Completed by <?= User::get_user_by_id($instance->get_user())->get_full_name() ?></h6>
                        <h6><a href="instance/readonly_from_instances/<?= $instance->get_id() ?>/1/<?=$encoded_search?>"> Review </a></h6>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="d-flex justify-content-center mb-4">
            <form id="delete_intances" action="instance/delete_instances/<?= $form->get_id() ?>/<?=$encoded_search?>" method="post">
                <button class="btn btn-warning" id="delete" type="submit" value="Delete selected"> Delete selected </button>
            </form>
        </div>
    </main>
</body>
</html>