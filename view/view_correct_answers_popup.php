<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Error</title>
    <base href="<?= $web_root ?>" >
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <script src="css/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="container d-flex flex-column align-items-center py-5 bg-secondary-subtle" data-bs-theme="dark" style="max-width:700px" >
    <div class="card text-center  w-100">
        <div class="card-header">
            <h4 >You must correct all errors</h4>
            <h4 >before submiting the form !</h4>
        </div>
        <div class="card-body">
            <a class="" href="instance/resume/<?= $instance_id?>/<?= $question->get_idx() ?>/<?=$encoded_search?>">
                <span class="btn btn-primary" style="width:150px">OK</span>
            </a>
        </div>

        <?php if ($question->get_type() == "short") { ?>
            <form id="answer_form" method="post">
                <input name="value" type="text" hidden <?php if ($answer != null) { ?> value="<?= $answer->get_value() ?>" <?php
                                                                                                                        } ?>>
            </form>
        <?php
        } elseif ($question->get_type() == "long") { ?>
            <form id="answer_form" method="post">
                <textarea name='value' cols='50' rows='3' hidden
                        ><?= ($answer != null) ? $answer->get_value() : "" ?></textarea>
            </form>
        <?php
        } elseif ($question->get_type() == "date") { ?>
            <form id="answer_form" method="post">
                <input name="value" type="date" hidden
                    <?php if ($answer != null) { ?>
                    value="<?= $answer->get_value() ?>"
                    <?php
                    } ?>>
            </form>
        <?php
        } elseif ($question->get_type() == "date"){ ?>
            <form id="answer_form" method="post">
                <input name="value" type="email" hidden <?php if ($answer != null) { ?> value="<?= $answer->get_value() ?> "
                    <?php
                                                        } ?>>
            </form>
        <?php
        } else{?>
            <form id="answer_form" method="post">
                <?php if($mcq_options != null):?>
                    <?php for($i = 1; $i <= count($mcq_options); ++$i):?>
                    <input type="checkbox" hidden id="op_<?= $i ?>" name="answers[]" value="<?= $i ?>" <?php if(in_array($i, $mcq_answers_idx, false)):?> checked <?php endif; ?>>
                    
                    <?php endfor; ?>
                <?php endif; ?>
            </form>
        <?php
        }?>
    </div>
</body>

</html>