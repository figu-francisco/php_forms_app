<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $aoe === Aoe::add ? "Add form" : "Edit form" ?></title>
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="css/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <script src="css/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="//fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20,400,0,0&amp;icon_names=arrow_back,cancel,save,skip_next,skip_previous">
</head>
<body class="d-flex flex-column align-items-center pt-5 bg-secondary-subtle" data-bs-theme="dark" > 
    <nav class="navbar bg-body-tertiary fixed-top mx-auto" style="max-width: 700px;">
        <div class="container-fluid">
            <a href=<?= $aoe === Aoe::add ?   "forms" : "form/index/".$form->get_id()."/".$encoded_search ?> >
                <span class="material-symbols-outlined" style="font-size: 32px;" >arrow_back</span>
            </a>
            <form id="addEditForm" action="form/<?= ($aoe === Aoe::add ? "add_form/" : "edit_form/".$form->get_id()."/".$encoded_search ) ?> " method="post">
                <button class="btn btn-link p-0" id="save" type="submit" >
                    <span class="material-symbols-outlined" style="font-size: 32px;" >save</span>
                </button>
            </form>
        </div>
    </nav>
    <main class="card  w-100 m-3" style="max-width: 700px;">
        <div class="card-header">
            <h4 class="text-secondary fw-bold"> <?= ($aoe === Aoe::add ? "Add new form:" : "Edit form:" ) ?> </h4>
        </div>
        <div class="card-body">
            <h4>Title:</h4>
            <input class="form-control mb-4" form="addEditForm" id="title" name="title" type="text" value="<?= $form->get_title() ?>" autofocus>
            <!-- affichage des erreurs de titres -->
            <?php if (isset($errors["title"])): ?>
                <div>
                    <ul>
                        <?php foreach ($errors["title"] as $error): ?>
                            <li class="text-warning" ><?= $error ?> </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <h4>Description:</h4>
            <textarea class="form-control mb-4" form="addEditForm" id="description" name="description" ><?= $form->get_description() ?></textarea>
         <!-- affichage des erreurs de description -->
        <?php if (isset($errors["description"])): ?>
            <div>
                <ul>
                    <?php foreach ($errors["description"] as $error): ?>
                        <li class="text-warning" ><?= $error ?> </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
            <div class="d-flex gap-3 mb-4">
                <h4>Is this form public ?</h4>
                <input class="form-check-input me-1" form="addEditForm" type="checkbox" id="public" name="public" value="public" <?= $form->is_public() ? "checked" : "" ?>>
            </div>
                       
            <h4>Form color categories :</h4>
            <div class="d-flex justify-content-between mb-4">
                <?php foreach (Color::cases() as $color): ?> 
                    <div>
                        <input class="form-check-input" style="color"  <?=in_array($color, $form->get_color()) ? 'checked' : ''?>  type="checkbox" id="<?=$color->value?>" form="addEditForm" name="colors[]" value="<?=$color->value?>">
                        <label for="<?=$color->value?>"   style="color:<?=$color->get_hex()?>;font-weight:bold;"><?=$color->name?></label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</body>

</html>