<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirmation</title>
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <script src="css/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="container d-flex flex-column align-items-center py-5 bg-secondary-subtle" data-bs-theme="dark" style="max-width:700px" >
    <div class="card text-center  w-100">
        <div class="card-header">
            <h2 class="">Are you sure ?</h2>
        </div>
        <div class="card-body">
        <?php if(isset($question)): ?>
            <p class="">Do you really want to delete question "<?= $question->get_title()?>" and all its dependencies ?</p>
        <?php elseif(isset($instance_id)): ?>
            <p class="">Do you really want to cancel your submission ?</p>
        <?php elseif(isset($instances_to_delete)): ?>
            <p class="">Do you really want to delete instance(s) and all its dependencies ?</p>
        <?php else: ?>
            <p class="">Do you really want to delete form "<?= $form->get_title()?>" and all its dependencies ?</p>
        <?php endif; ?>   
            <p class="">This process cannot be undone.</p>
        </div>
        <div class="d-flex justify-content-center gap-3 mb-3 flex-wrap">
            <!-- Gestion du bouton cancel -->
            <?php if(isset($instance_id)): ?>
                <a href="instance/resume/<?= $instance_id?>/<?= $idx?>/<?=$encoded_search?>"><span  style="width:120px" class="btn btn-secondary ">Cancel</span></a>
            <?php elseif(isset($instances_to_delete) ): ?>
                <a class=" " href= <?= (isset($return_to_form) ? "form/index/" :
                                                            "instance/view_instances/").$form->get_id()."/".$encoded_search?>>
                        <span style="width:120px" class="btn btn-secondary ">Cancel</span>
                </a>
            <?php else: ?>
                <a href="form/index/<?= $form->get_id()?>/<?= $encoded_search?>"><span class="btn btn-secondary">Cancel</span></a>
            <?php endif; ?>
            <!-- delete question -->
            <?php if(isset($question)): ?>
            <form action='form/remove_question/<?= $encoded_search ?>' method='post' >
                <input type='text' name='form_id' value='<?= $form->get_id()?>' hidden>
                <input type='text' name='question_id' value='<?= $question->get_id()?>' hidden>
                <button class="btn btn-danger"  style="width:120px" type='submit'>Confirm</button>
            </form>
            <?php elseif(isset($instances_to_delete)): ?>
            <form action="instance/delete_instances_confirmed/<?= $form->get_id()?>/<?= $encoded_search ?>" method="post">
                <!-- creer une serie d'input caché qui contiennent les id des instances a effacer, seront renvoyé en post -->
                <?php foreach ($instances_to_delete as $id_to_delete): ?>
                    <input name="id<?= $id_to_delete ?>" value="<?= $id_to_delete ?>" hidden>
                <?php endforeach; ?>
                <button class="btn btn-danger"  style="width:120px" type='submit'>Confirm</button>
            </form>
            <!-- delete instance -->
            <?php elseif(isset($instance_id)): ?>
                <form action='instance/delete/<?= $encoded_search ?>' method='post' >
                    <input type='text' name='instance_id' value='<?= $instance_id?>' hidden>
                    <button class="btn btn-danger"  style="width:120px" type='submit'>Confirm</button>
                </form>
            <?php else: ?>
            <!-- delete form -->
            <form action='form/delete/<?= $encoded_search ?>' method='post' >
                <input type='text' name='form_id' value='<?= $form->get_id() ?>' hidden>
                <button class="btn btn-danger"  style="width:120px" type='submit'>Confirm</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>