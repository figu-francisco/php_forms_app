<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>"<?= $form->get_title() ?>" by <?= $user->get_full_name() ?></title>
        <base href="<?= $web_root ?>">
       
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="//fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=arrow_back,arrow_downward,arrow_upward,delete,delete_history,edit,globe,history,playlist_add,public_off,query_stats,share" >
        <script src="lib/jquery-3.7.1.min.js"></script>
        <script src="lib/jquery-ui.js"></script>
        <link rel="stylesheet" href="css/bootstrap-5.3.3-dist/css/bootstrap.min.css">
        <script src="css/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
        
        <script>

            let questions;
            let tbl_questions ;
            const form_id = <?= $form->get_id() ?> ;
            const form_is_readonly = <?= $form->is_readonly() ? "true" : "false" ?>;

            $(function() {
                //******************* drag and drop ***********************
                if(!form_is_readonly) {
                    $("#questions_list").sortable(
                        {
                            //Update: This event is triggered when the user
                            //    stopped sorting and the DOM position has changed.
                            update: function (event, ui) {
                                //on recup l'id de l'objet deplacer
                                let idx_card_move = $(ui.item).attr("id");
//console.log(idx_card_move);
                                //on creer une liste avec tout les ID dans l'ordre apres modification
                                let list_idx = $("#questions_list").sortable("toArray", {attribute: "id"});
//console.log(list_idx);
                                //on vas rechercher la nouvelle position de la card qu'on a deplacer
                                let posi = 0;
                                let new_idx = 0;
                                for (let idx of list_idx) {
                                    posi++;
                                    if (idx === idx_card_move) {
                                        new_idx = posi;
                                    }
                                }
                                move_question(idx_card_move, new_idx);
                            }
                        }
                    );

                //****************FIN drag and drop ***********************

                    tbl_questions = $('#questions_list');
                    get_questions_service();
                }
            });
            //get questions for initial display
            async function get_questions_service(){
                try {
                    questions = await $.getJSON("form/get_questions_service/"+form_id);
                    displayTable();
                } catch(e) {
                    console.error("AJAX error: ", e.responseText);
                    alert(e.responseText);
                }
            }
            //move question up + get questions + display
            async function up_question(idx){
                try {
                    questions = JSON.parse(await $.post("form/question_up_service/" , {"form_id": form_id , "question_idx": idx }));
                    displayTable();
                } catch(e) {
                    console.error("AJAX error: ", e.responseText);
                    alert(e.responseText);
                }
            }
            //move question down + get questions + display
            async function down_question(idx){
                try {
                    questions = JSON.parse(await $.post("form/question_down_service/" , {"form_id": form_id , "question_idx": idx }));
                    displayTable();
                } catch(e) {
                    console.error("AJAX error: ", e.responseText);
                    alert(e.responseText);
                }
            }

            async function delete_question(id){
                try {
                    questions = JSON.parse(await $.post("form/remove_question_service/" , {"form_id": form_id , "question_id": id }));
                    $('#del_confirm_modal_' + id ).modal('hide');
                    displayTable();
                } catch(e) {
                    console.error("AJAX error: ", e.responseText);
                    alert(e.responseText);
                }
            }

            async function move_question(original_idx, new_idx){
                try {
                    questions = JSON.parse(await $.post("form/question_move_service/" ,
                        {"form_id": form_id , "orginal_idx": original_idx, "new_idx": new_idx }));
                        displayTable();
                } catch(e) {
                    console.error("AJAX error: ", e.responseText);
                    alert(e.responseText);
                }
            }


            function displayTable(){
                let encoded_search = $("#encoded_search").val();
                const nbr_questions = questions.length;

                html = "";
                for (let question of questions) {
                    html +=
                        //bloc pour la modal
                        "<div class='card mb-3' title='Drag & drop to move up/down' id=" + question.idx + ">" +
                            "<div class='modal fade' id='del_confirm_modal_" + question.id + "' tabindex='-1' aria-labelledby='modal_title"+question.id+"' aria-hidden='true'>" +
                            "<div class='modal-dialog'>" +
                            "<div class='modal-content'>" +
                            "<div class='modal-header d-flex flex-column'>" +
                            "<img src='css/icon/delete_forever.png' alt='delete_icon'>" +
                            "<h1 class='text-center modal-title fs-5 text-danger fw-bold' id='modal_title" + question.id + "'>Are you sure ?</h1>" +
                            "</div>" +
                            "<div class='modal-body d-flex flex-column align-items-center'>" +
                            "<p class='text-center'>Do you really want to delete question <span class='fw-bold'>" + question.title + "</span> and all its dependencies?</p>" +
                            "<p class='text-center' >This process can not be undone</p>" +
                            "</div>" +
                            "<div class='modal-footer d-flex justify-content-center'>" +
                            "<button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>" +
                            "<form action='javascript:delete_question(" + question.id + ")' >" +

                                "<input type='text' name='form_id' value='" + form_id + "' hidden>" +
                                "<input type='text' name='question_id' value='" + question.id + "' hidden>" +
                                "<button type='submit' class='btn btn-danger'>Delete</button>" +
                            "</form>" +
                            "</div>" +
                            "</div>" +
                            "</div>" +
                        "</div>" +
                        //fin bloc modal
                        
                            "<div class='card-header d-flex'>" +
                                "<div>" +
                                    "<h5 class='card-title fw-bold'>"+question.title +"</h5>" +
                                    "<p class='fst-italic'> " + question.description + "</p>" +
                                "</div>";

                            //partie pour les fleche, seulement si form est pas readonly
                            if (!form_is_readonly) {
                                html +=
                                    "<div class='d-flex ms-auto align-items-center'>" +
                                    //fleche UP
                                        "<a href='javascript:up_question(" + question.idx + ")' " +
                                        ((question.idx === 1) ? 'hidden' : '' ) + ">" +
                                            "<span class='material-symbols-outlined' style='font-size: 32px;'>arrow_upward</span>" +
                                        "</a>" +
                                    //fleche DOWN
                                        "<a href='javascript:down_question(" + question.idx + ")' " +
                                        ((question.idx === nbr_questions) ? 'hidden' : '' ) + ">" +
                                            "<span class='material-symbols-outlined' style='font-size: 32px;'>arrow_downward</span>" +
                                        "</a>"+
                                    "</div>";
                            }
                            html +=
                            "</div>";

                    //partie pour type, required
                    html +=
                        "<div class='card-body d-flex'>" +
                            "<div class='txt_container'> " +
                                "<p class='text-body-secondary lh-1'> Type : " + question.type + "</p>" +
                                "<p class='text-body-secondary lh-1'> Required : " + (question.required ? "True" : "False") + " </p>" +
                            "</div>";

                    //partie pour bouton d'edition
                    if (!form_is_readonly) {
                        html +=
                            "<div class='d-flex gap-2 ms-auto align-items-center'>" +
                                "<a href='question/edit_question/" + question.id + "/" + encoded_search + "'>" +
                                    "<span class='material-symbols-outlined' style='font-size: 32px;'>edit</span>" +
                                "</a>" +
                                "<button  class='btn btn-link p-0' data-bs-toggle='modal' data-bs-target='#del_confirm_modal_" + question.id + "'>" +
                                    "<span class='material-symbols-outlined' style='font-size: 32px;'>delete</span>" +
                                "</button>" +
                            "</div>";
                    }
                    html +=
                    "</div>" + 
                "</div>";
                }
                if(html === ""){
                    html = "<h3 class='fst-italic'>This form has no questions yet ...</h3>";
                }

                tbl_questions.html(html);
            }

        </script>
    </head>
    <body class="d-flex flex-column align-items-center pt-5 bg-secondary-subtle" data-bs-theme="dark"> 
        <!-- to pass encoded search to script -->
        <input type="text" hidden id="encoded_search" value=<?= $encoded_search ?>>
        <!-- nav -->
        <nav class="navbar bg-body-tertiary fixed-top mx-auto" style="max-width: 700px; ">
            <div class="container-fluid">
                <a href="forms/index/<?= $encoded_search ?>" >
                    <span class="material-symbols-outlined" style="font-size: 32px;" title="Exit form">arrow_back</span>
                </a>
                <div class="d-flex ms-auto gap-3">
                    <!-- show add_question & edit only when not readonly -->
                    <?php if($form->has_complete_instances()): ?>
                        <a href="instance/view_instances/<?= $form->get_id() ?>/<?= $encoded_search ?>">
                            <span class="material-symbols-outlined" style="font-size: 32px;" title="See all previous submissions">history</span>
                        </a>
                    <?php endif; ?>
                    <?php if($form->is_readonly()):?>
                        <a href="instance/delete_all_instances/<?= $form->get_id() ?>/<?= $encoded_search ?>">
                            <span class="material-symbols-outlined" style="font-size: 32px;" title="Delete all previous submissions">delete_history</span>
                        </a>
                    <?php endif; ?>

                    <?php if(!$form->is_readonly()):?>
                    <a href="question/add_question/<?= $form->get_id() ?>/<?= $encoded_search ?>">
                        <span class="material-symbols-outlined" style="font-size: 32px;" title="Add new question">playlist_add</span>
                    </a>
                    <a href="form/edit_form/<?= $form->get_id() ?>/<?= $encoded_search ?>">
                        <span class="material-symbols-outlined" style="font-size: 32px;" title="Edit form information">edit</span>
                    </a>
                    <?php endif; ?>
                    <a href="form/remove_form_confirmation/<?= $form->get_id() ?>/<?= $encoded_search ?>">
                        <span class="material-symbols-outlined" style="font-size: 32px;" title="Delete form">delete</span>
                    </a>
                    <?php if($form->has_complete_instances()): ?>
                    <a href="instance/analyze/<?= $form->get_id() ?>/<?= $encoded_search ?>">
                        <span class="material-symbols-outlined" style="font-size: 32px;" title="See form stats">query_stats</span>
                    </a>
                    <?php endif; ?>
                </div>   
            </div>
        </nav>
        <main class="w-100" style="max-width: 700px">
            <div class="d-flex flex-column align-items-start px-3 mt-2" >
                <div class="d-flex w-100">
                    <div>
                        <h2 class='fw-bold'><?= $form->get_title() ?></h2>
                        <h5 class='text-secondary  fst-italic'>by <?= $user->get_full_name() ?></h5>
                    </div>
                    <!-- checkbox is public -->
                    <div class="d-flex align-items-center ms-auto">
                        <form action="form/toggle_public/<?= $encoded_search ?>" method="post">
                            <?php if($form->is_public() == 1):?>
                                <button type="submit" class="btn btn-link p-0">
                                    <span class="material-symbols-outlined" style="font-size: 32px;" title="This form is public">globe</span>
                                </button>
                            <?php else:?>
                                <button type="submit" class="btn btn-link p-0">
                                    <span class="material-symbols-outlined" style="font-size: 32px;" title="This form is private">public_off</span>
                                </button>
                            <?php endif;?>
                            <input type="number" name="form_id" value=<?= $form->get_id() ?> hidden>
                        </form>
                    </div>
                </div>
                <!-- description uniquement si elle existe -->
                <?php if($form->get_description() != null):?>
                    <p class=""><?= $form->get_description() ?></p>
                    <br>
                <?php endif; ?>
                <!-- show add_aquestion & edit only when not readonly -->
                <?php if($form->is_readonly()){?>
                    <div class="">
                        <p class="text-warning fst-italic">This form is read-only, at least one answer has already been submited.</p>
                    </div>
                <?php
                }?>
            </div>
            <?php if(!empty($form->get_questions())){?>
            <h4 class='text-secondary fw-bold ms-3'>Questions :</h4>
            <!-- question cards -->
            <div id="questions_list" class="mx-3">
                <?php foreach($questions as $question): ?>
                    <div class="card mb-3">
                        <div class="card-header d-flex">
                            <div>
                                <h5 class="card-title fw-bold"><?= $question->get_title() ?></h5>
                                <p class="fst-italic"><?= $question->get_description() ?></p>
                            </div>
                            <?php if(!$form->is_readonly()): ?>
                                <div class="d-flex ms-auto align-items-center">
                                    <?php if($question->get_idx() > 1): ?>
                                        <form action="form/question_up/<?= $encoded_search ?>" method="post">
                                            <input name="form_id" type="text" value=<?=$form->get_id()?> hidden>
                                            <input name="question_idx" type="text" value=<?=$question->get_idx()?> hidden>
                                            <button class="btn btn-link p-0" >
                                                <span class="material-symbols-outlined" style="font-size: 32px;">arrow_upward</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if($question->get_idx() < sizeof($questions)): ?>
                                        <form action="form/question_down/<?= $encoded_search ?>" method="post">
                                            <input name="form_id" type="text" value=<?=$form->get_id()?> hidden>
                                            <input name="question_idx" type="text" value=<?=$question->get_idx()?> hidden>
                                            <button class="btn btn-link p-0" >
                                                <span class="material-symbols-outlined" style="font-size: 32px;">arrow_downward</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>                                    
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body d-flex">
                            <div class="txt_container">
                                <p class="text-body-secondary lh-1">Type : <?= $question->get_type() ?></p>
                                <p class="text-body-secondary lh-1">Required : <?= $question->get_required_tostring() ?></p>
                            </div>
                            <?php if(!$form->is_readonly()): ?>
                            <div class="d-flex gap-2 ms-auto align-items-center">
                                <a href="question/edit_question/<?=$question->get_id()?>/<?= $encoded_search !== "" ? $encoded_search : "" ?>">
                                    <span class="material-symbols-outlined" style="font-size: 32px;">edit</span>
                                </a>
                                <a href="form/remove_question_confirmation/<?=$form->get_id()?>/<?=$question->get_id()?>/<?= $encoded_search !== "" ? $encoded_search : "" ?>">
                                    <span class="material-symbols-outlined" style="font-size: 32px;">delete</span>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                    </div>
                <?php endforeach; ?>
            </div>
            <?php
                }else{?>
                <h3 class="fst-italic m-3">This form has no questions yet ...</h3>
                <?php
                }?>
        </main>
    </body>
</html>