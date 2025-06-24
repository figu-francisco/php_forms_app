<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instance Read-Only</title>
    <base href="<?= $web_root ?>">
    <link rel="stylesheet" href="css/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <script src="css/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="//fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=arrow_back,globe,public_off,skip_next,skip_previous" >
</head>
<body class="d-flex flex-column pt-5 bg-secondary-subtle align-items-center" data-bs-theme="dark"> 
    <nav class="navbar bg-body-tertiary fixed-top mx-auto"  style="max-width:700px">
    <!-- exit left arrow -->
        <div class="container-fluid">
            <a href=<?= ($from_instances ? "instance/view_instances/".$form->get_id()."/" : "forms/index/").$encoded_search?>>
                <span class="material-symbols-outlined" style="font-size: 32px;">arrow_back</span>
            </a>
            <div class="d-flex ms-auto gap-3">
                <!-- previous and next buttons -->
                <?php if($idx > 1): ?>
                    <a href=<?= ($from_instances ? "instance/readonly_from_instances/" : "instance/readonly/").$instance->get_id()."/".($idx - 1)."/".$encoded_search ?> >
                        <span class="material-symbols-outlined" style="font-size: 32px;">skip_previous</span>
                    </a>
                <?php endif; ?>
                <?php if($idx < count($form->get_questions())): ?>
                    <a href=<?= ($from_instances ? "instance/readonly_from_instances/" : "instance/readonly/").$instance->get_id()."/".($idx + 1)."/".$encoded_search ?> >
                        <span class="material-symbols-outlined" style="font-size: 32px;">skip_next</span>
                    </a>
                <?php endif;?>
            </div>
        </div>
    </nav>
    <main class="card d-flex-column justify-text-center w-100 m-3" style="max-width: 700px; ">
        <div class="card-header">
            <h4 class="text-secondary fw-bold">View form</h4>
            <ul class="list-group">
                <li class="list-group-item ">Title: <?=$form->get_title()?> </li>
                <?php if($form->get_description()): ?>
                    <li class="list-group-item">Description: <?=$form->get_description()?></li>
                <?php else: ?>
                    <li class="list-group-item text-secondary fst-italic">No Description</li>
                <?php endif; ?>
                <li class="list-group-item text-body-secondary">Started: <?=$instance->get_started()?></li>
                <li class="list-group-item text-body-secondary">Completed: <?=$instance->get_completed()?></li>
                <?php if($from_instances == true): ?>
                    <li class="list-group-item text-body-secondary">Answered by: <?=User::get_user_by_id($instance->get_user())->get_full_name()?></li>                   
                <?php endif; ?>
            </ul>
        </div>  
        <div class="card-body">
            <h4 class="text-secondary fw-bold">Question <?=$idx?>/<?=count($form->get_questions())?></h4>
            <div class="d-flex gap-2 m-2">
                <h4><?=$question->get_title()?></h4><h4 class="text-warning"><?= $question->get_required() ? '(*)' : ''?></h4>
            </div>
            <p class="text-body-secondary fst-italic m-0"><?=$question->get_description()?></p>
            <?php if($question->is_mcq()):?>
                <div class="mcq_form">
                        <?php if($mcq_options != null):?>
                            <?php for($i = 1; $i <= count($mcq_options); ++$i):?>
                                <div>
                                    <input type="checkbox" id="op_<?= $i ?>" name="answers[]" value="<?= $i ?>" <?php if(in_array($i, $mcq_answers_idx, false)):?> checked <?php endif; ?>disabled>
                                    <label  for="op_<?= $i ?>"><?= $mcq_options[$i-1] ?></label><br>
                                </div>
                            <?php endfor; ?>
                        <?php endif; ?>
                </div>  
            <?php else: ?>
                <input type="text" readonly class="form-control" value="<?= $answer->get_value()?> ">
            <?php endif; ?>
        </div>
    </main>
</body>
</html>