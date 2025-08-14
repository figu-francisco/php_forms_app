<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forms</title>
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="//fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20,400,0,0&icon_names=globe,poker_chip,public_off,search" >
    <script src="lib/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <script src="css/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script src="scripts/search_forms.js"></script>
</head>

<body class="d-flex flex-column align-items-center pt-5 bg-secondary-subtle" data-bs-theme="dark" >
    
     <!-- nav -->
    <nav class="navbar bg-body-tertiary fixed-top mx-auto" style="max-width:700px">
        <div class="container-fluid ms-3 px-md-5 mx-md-5">
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
        <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar" style="width: 220px">
            <div class="offcanvas-header">
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body">
                <?php if($user->is_guest()):?>
                <ul class="navbar-nav justify-content-start flex-grow-1 pe-3">
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="main/signup/guest">Join us</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="main/logout">Log Out</a></li>
                </ul>
                <?php else:?>
                <ul class="navbar-nav justify-content-start flex-grow-1 pe-3">
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="forms/index">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="form/add_form">Add a new form</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="user/index">Settings</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="main/logout">Log Out</a></li>                    
                </ul>
                <?php endif;?>
            </div>
        </div> 
    </nav> 

    <!-- search input & color categories -->
    <div class="w-100 d-flex justify-content-center my-4" style="max-width: 700px">
         <div id="search_bar_div" class="w-100 mx-3">
            <input class="form-control"  form="search_form" type="text" id="input_search" 
             name="input_search" placeholder="search forms..." value="<?=$text_search ?>" >
        </div>
        <form id="search_form" action="forms/index/" method="post">
            <button class="btn btn-secondary p-0 me-3" id="search_form_btn" type="submit" style="width: 50px">
                <span class="material-symbols-outlined" style="font-size: 32px;">search</span>
            </button>
        </form>
    </div>
    <div id="search_color_div" class="d-flex gap-2">
        <?php foreach (Color::cases() as $color_name):?>
            <input form="search_form" type="checkbox" name="search_color[]" value="<?= $color_name->value ?>"
                <?= in_array($color_name->value, $color_search) ? 'checked' : '' ?> />
                <span style="color:<?=$color_name->get_hex()?>;font-weight:bold;">
                    <?= $color_name->name ?>
                </span>
        <?php endforeach; ?>
    </div>
    <div class="text-start text-secondary fs-5 fw-semibold fst-italic m-3">Hello 
        <span class="text-start text-secondary fs-5 fw-semibold fst-italic"><?= $user->get_full_name() ?></span> !
    </div> 

     <!-- form cards -->
    <div class="container w-100" style="max-width: 700px" id="cards_container">
        <?php $forms = $user->get_user_forms_filtered($text_search,$color_search);
        foreach ($forms as $form):?>
        <div class="card mb-3" id="<?= $form->get_id() ?>" >
            <div class="card-header" id="pocker_html_<?= $form->get_id() ?>">
                <div class="d-flex justify-content-between">
                    <h5 class="card-title fw-bold"><?= $form->get_title() ?></h5>
                    <div class="material-symbols-outlined ms-3" 
                         title='<?= $form->is_public() ? "This form is public" : "This form is private" ?>'>
                            <?= $form->is_public() ? 'globe' : 'public_off' ?>
                    </div>
                </div>
                <div>
                    <?php foreach (Color::cases() as $color): ?>
                        <?php if(in_array($color, $form->get_color())):?>
                            <div class="material-symbols-outlined" id="<?=$color->value.'_'.$form->get_id()?>" style="color:<?=$color->get_hex()?>;">poker_chip</div>
                        <?php else:?>
                            <div  hidden id="<?=$color->value.'_'.$form->get_id()?>" style="color:<?=$color->get_hex()?>;">poker_chip</div>
                        <?php endif;?>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if($form->get_description() != null){?>
                    <p class="card-subtitle mb-2 text-body"><?= $form->get_description() ?></p>
                <?php
                }?>
                <p class="text-body-secondary fst-italic lh-1">By <?= $form->get_owner_fullname() ?></p>
                <!-- if user is guest get_most_recent_instance returns null, don't show dates -->
                <?php if($form->get_most_recent_instance($user) != null ): 
                    $most_recent_instance = $form->get_most_recent_instance($user)?>
                    <p class="text-body-secondary lh-1">Started <?=$most_recent_instance->get_time_inteval_since_started()?></p>
                    <?php if($most_recent_instance->is_completed()):?>
                        <p class="text-body-secondary lh-1">Completed <?=$most_recent_instance->get_time_inteval_since_completed()?></p>
                    <?php else :?>
                        <p class="text-body-secondary fst-italic lh-1">in progress...</p>
                    <?php endif;?>
                <?php endif;?> 
                <div class="d-flex gap-2">    
                    <?php if(!empty($form->get_questions())):?>
                    <!-- each button has its own id depending on the form -->
                    <form action="instance/index/<?= $encoded_search !== "" ? $encoded_search : "" ?>"
                        method="post" id="openbtn_<?= $form->get_id() ?>">
                        <input type="number" name="form_id" value=<?=$form->get_id()?> hidden>    
                        <button type="submit" class="btn btn-outline-primary">Open</button>
                    </form>
                    <?php endif; ?>
                    <?php if($form->has_edit_access($user)):?>
                        <!-- to pass base url to jquery function -->
                        <input type="text" hidden id="base_url_managebtn_<?=$form->get_id()?>" value="form/index/<?=$form->get_id()?>/">
                        <a href="form/index/<?= $form->get_id() ?>/<?= $encoded_search !== "" ? $encoded_search : "" ?>"
                        id="managebtn_<?=$form->get_id()?>">
                            <span class="btn btn-outline-secondary">Manage</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- pass values to jquery functions -->
        <input type="text" id="encoded_search_input" hidden value="<?=$encoded_search?>">
        <input type="text" hidden id="base_url_openbtn" value="instance/index/">
    </div>
</body>
</html>