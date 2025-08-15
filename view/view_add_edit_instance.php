<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Answer the form</title>
        <base href="<?= $web_root ?>">
        <link rel="stylesheet" href="css/bootstrap-5.3.3-dist/css/bootstrap.min.css">
        <script src="css/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" href="//fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=arrow_back,cancel,save,skip_next,skip_previous" >
    </head>
    <body class="d-flex flex-column align-items-center pt-5 bg-secondary-subtle" data-bs-theme="dark"> 
        <nav class="navbar bg-body-tertiary fixed-top mx-auto" style="max-width:700px">
            <div class="container-fluid">
                <!-- arrow back -->
                <button class="btn btn-link p-0" type="submit" form="answer_form" 
                        formaction="instance/<?=$user->is_guest() ? 'quit' : 'exit'?>/<?=$instance->get_id()?>/<?=$idx?>/<?=$encoded_search?>">
                    <span class="material-symbols-outlined" style="font-size: 32px;" title="Exit form">arrow_back</span>
                </button>
                <div class="d-flex ms-auto gap-3">
                    <!-- cancel button -->
                    <?php if(!$user->is_guest()): ?>
                        <button class="btn btn-link p-0" type="submit" form="answer_form" 
                                formaction="instance/quit/<?=$instance->get_id()?>/<?=$idx?>/<?=$encoded_search?>">
                            <span class="material-symbols-outlined" style="font-size: 32px;" title="Quit form">cancel</span>
                        </button>
                    <?php endif; ?>
                    <!-- previous and next buttons -->
                    <?php if($idx > 1):?>
                        <button class="btn btn-link p-0" type="submit" form="answer_form" 
                                formaction="instance/previous/<?=$instance->get_id()?>/<?=$idx?>/<?=$encoded_search?>">
                            <span class="material-symbols-outlined" style="font-size: 32px;" title="Previous question">skip_previous</span>
                        </button>
                    <?php endif; ?>
                    <?php if($idx < count($form->get_questions())):?>
                        <button class="btn btn-link p-0" type="submit" form="answer_form" 
                                formaction="instance/next/<?=$instance->get_id()?>/<?=$idx?>/<?=$encoded_search?>">
                            <span class="material-symbols-outlined" style="font-size: 32px;" title="Next question">skip_next</span>
                        </button>
                    <?php endif;?>
                    <!-- save -->
                    <?php if($idx == count($form->get_questions())): ?>
                        <button class="btn btn-link p-0" type="submit" form="answer_form" 
                                formaction="instance/save/<?=$instance->get_id()?>/<?=$idx?>/<?=$encoded_search?>">
                            <span class="material-symbols-outlined" style="font-size: 32px;" title="Save form">save</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
        <main class="card d-flex-column justify-text-center w-100 m-3" style="max-width: 700px; ">
            <div class="card-header">
                <h4 class="text-secondary fw-bold">Answer the form</h4>
                <ul class="list-group">
                    <li class="list-group-item ">Title: <?=$form->get_title()?> </li>
                    <?php if($form->get_description()): ?>
                        <li class="list-group-item">Description: <?=$form->get_description()?></li>
                    <?php endif; ?>
                    <li class="list-group-item text-body-secondary">Started: <?=$instance->get_started()?></li>
                    <li class="list-group-item text-body-secondary  fst-italic">In progres...</li>
                </ul>
            </div>
            <div class="card-body">
                <h4 class="text-secondary fw-bold">Question <?=$idx?>/<?=count($form->get_questions())?></h4>
                <div class="d-flex gap-2 m-2">
                    <h4><?=$question->get_title()?></h4><h4 class="text-warning"><?= $question->get_required() ? '(*)' : ''?></h4>
                </div>
                <p class="text-body-secondary fst-italic m-0"><?=$question->get_description()?></p>
                <!-- form will show depending on question type -->
                <?php if($question->get_type() == "short"):?>
                    <form id="answer_form" method="post">
                        <input class="form-control" name="value" type="text" <?php if($answer != null) :?> value="<?= $answer->get_value() ?>" <?php endif; ?>>
                    </form>
                <?php elseif($question->get_type() == "long"):?>
                    <form id="answer_form" method="post">
                        <textarea class="form-control" name='value' cols='400' rows='6'
                                ><?= ($answer != null) ? $answer->get_value() : "" ?></textarea>
                    </form>
                <?php elseif($question->get_type() == "date"):?>
                    <form id="answer_form" method="post">
                        <input class="form-control" name="value" type="date" <?php if($answer != null) :?> value="<?= $answer->get_value() ?>" <?php endif; ?>>
                    </form>
                <?php elseif($question->get_type() == "email"):?>
                    <form id="answer_form" method="post">
                        <input class="form-control" name="value" type="email" <?php if($answer != null):?> value="<?= $answer->get_value() ?>" <?php endif; ?>>
                    </form>
              
                <?php else:?> 
                    <form id="answer_form" method="post" class="mcq_form">
                        <?php if($mcq_options != null):?>
                            <?php for($i = 1; $i <= count($mcq_options); ++$i):?>
                                <div>
                                    <input type="checkbox" id="op_<?= $i ?>" name="answers[]" value="<?= $i ?>" <?php if(in_array($i, $mcq_answers_idx, false)):?> checked <?php endif; ?>>
                                    <label  for="op_<?= $i ?>"><?= $mcq_options[$i-1] ?></label><br> 
                                </div>
                            <?php endfor; ?>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>

                <!-- errors list -->
                <?php if(($errors)):?>
                    <ul>
                    <?php foreach($errors as $error){?>
                            <li class="text-warning"><?=$error?></li>
                    <?php } ?>
                    </ul>
                <?php endif; ?>
            </div>
        </main>
    </body>
</html>