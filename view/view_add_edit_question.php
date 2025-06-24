<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $aoe === aoe::add ? "Add question" : "Edit question" ?></title>
    <base href="<?= $web_root ?>" >
    <script src="lib/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="//fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=add,arrow_back,cancel,refresh,remove,save,skip_next,skip_previous">
    <link rel="stylesheet" href="css/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <script src="css/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let title_val = "";
        let errors, save, title, description, question_id, form_id, title_ok, description_ok, option_ok, aoe, diverr, option;
        let new_add = <?= $new_add ?>;
            $(function() {
                aoe = $("#aoe").val();
                save = $("#save");
                title = $("#title");
                title.css("border-width" , "2px" );
                title_val = title.val();
                description = $("#description");
                description_val = description.val();
                form_id = $("#id_form").val();
                option = $("#type_question");

                //if edit question, get question id
                if(aoe === "edit"){
                    question_id = $("#id").val();
                }

                //set up values for enable/disable save btn
                if(new_add){
                    title_ok = false;
                    description_ok = true;
                    option_ok = false;
                    update_save_btn();
                }else{
                    check_field();
                }

                //manage title 
                title.on("input", function() {
                    title_val = title.val();
                    check_title_service(title_val, question_id, form_id);
                });

                //manage description 
                description.on("input", function() {
                    description_val = description.val();
                    check_description_service(description_val);
                });

                //manage option list
                option.on('change' , function() {
                    check_option();
                })
            })

            async function check_title_service(title_val, question_id, form_id){
                try {
                    errors = await $.post("question/validate_title_service/" , {"title_val": title_val, "question_id": question_id, "form_id": form_id});
                    update_visual_title(JSON.parse(errors));
                } catch(e) {
                    console.error("AJAX error: ", e.responseText);
                    alert(e.responseText);
                }
            }

            async function check_description_service(description){
                try {
                    errors = await $.post("question/validate_description_service/" , {"description": description});
                    update_visual_description(JSON.parse(errors));
                } catch(e) {
                    console.error("AJAX error: ", e.responseText);
                    alert(e.responseText);
                }
            }

            function update_visual_title(decoded_data){
                //update border color & title_ok
                let html = "";
                if(decoded_data.length > 0){
                    title_ok = false;
                    apply_red(title);
                    //error list
                    html = "<ul>";
                    for (let error of decoded_data){
                        html += "<li class='text-warning' >" + error + "</li>";
                    }
                    html += "</ul>"
                }else{
                    title_ok = true;
                    apply_green(title);
                }

                //update error text
                let diverr = $("#show_errors");
                diverr.html(html);
                //update save btn
                update_save_btn();
            }

            function update_visual_description(decoded_data){
                //update border color & description_ok
                let html = "";
                if(decoded_data.length > 0){
                    description_ok = false;
                    apply_red(description);
                    //error list
                    html = "<ul>";
                    for (let error of decoded_data){
                        html += "<li class='text-warning' >" + error + "</li>";
                    }
                    html += "</ul>"
                }else{
                    description_ok = true;
                    apply_green(description);
                }
                //update error text
                let diverr = $("#show_errors_description");
                diverr.html(html);
                //update save btn
                update_save_btn();
            }
            
            function check_option(){
                //update error text & border color & option_ok
                diverr = $("#show_errors_options");
                let html = "";
                if(option.val() == ""){
                    html = "<ul><li class='text-warning' >You need to select a type of question</li></ul>";
                    option_ok = false;
                    apply_red(option);
                }else{
                    option_ok = true;
                    apply_green(option);
                }
                diverr.html(html);
                //update save btn
                update_save_btn();
            }  

            function update_save_btn(){              
                if(option_ok && title_ok && description_ok){
                    save.prop("disabled", false);
                }else{
                    save.prop("disabled", true);
                }
            }

            function apply_red($elem){
                $elem.css({'border-color' : "red", 'outline-color' : "red" });
            }
            function apply_green($elem){
                $elem.css({'border-color' : "green", 'outline-color' : "green" });
            }


        function check_field(){
            //check title
            let error_on_title = $('#show_errors').find('li');
            if(error_on_title.length){
                apply_red(title);
                title_ok = false;
            }else{
                apply_green(title);
                title_ok = true;
            }
            //check description
            let error_on_description = $('#show_errors_description').find('li');
            if(error_on_description.length){
                apply_red(description);
                description_ok = false;
            }else{
                apply_green(description);
                description_ok = true;
            }
            //check type option
            check_option(); //note: check_option fait un update du save_btn

            //check type option
            let all_option = $('[name*="mcq_option"]');
            all_option.each(function() {
                let name = $(this).attr('name');
                let error_name = name.replace('mcq_option_','mcq_option_error_');
                let error = $('#'+error_name); //je recherche s'il y a un elem avec le nom de l'erreur
                if(error.length){ //et j'adapte si j'en trouve une
                    apply_red($(this));
                }else{
                    apply_green($(this));
                }
            });

            //last option
            let error_last_option = $("#erros_last_option");
            if(error_last_option.length){
                apply_red($('[name="last_option"]'));
            }
        }
            
    </script>
</head>

<body class="d-flex flex-column align-items-center pt-5 bg-secondary-subtle" data-bs-theme="dark">
    <nav class="navbar bg-body-tertiary fixed-top mx-auto" style="max-width: 700px; ">
        <div class="container-fluid">
            <a href="form/index/<?=$question->get_form()?>/<?=$encoded_search?>" >
                <span class="material-symbols-outlined" style="font-size: 32px;">arrow_back</span>
            </a>
            <form id="addEditQuest" action="question/<?= $aoe === aoe::add ? "add_question/".$question->get_form()."/".$encoded_search
                                                                            : "edit_question/".$question->get_id()."/".$encoded_search ?>" method="post">
                <button class="btn btn-link p-0" name="save_btn" id="save" type="submit" >
                    <span class="material-symbols-outlined" style="font-size: 32px;">save</span>
                </button>
            </form>
        </div>
    </nav>
    <main class="card d-flex-column justify-text-center w-100 mt-3" style="max-width: 700px; ">
        <div class="card-header">
        <h4 class="text-secondary fw-bold"> <?= ($aoe === aoe::add ? "Add new question:" : "Edit question:" ) ?> </h4>
        </div>  
        <div class="card-body">
            <h4>Title:</h4>
            <div><input class="form-control mb-4" form="addEditQuest" id="title" name="title" type="text" value="<?= $question->get_title() ?>" autofocus></div>
            <!-- affichage des erreurs de titres -->
            <div id="show_errors">
            <?php if (isset($errors["title"])): ?>
                    <ul>
                        <?php foreach ($errors["title"] as $error): ?>
                            <li class="text-warning" ><?= $error ?> </li>
                        <?php endforeach; ?>
                    </ul>
            <?php endif; ?>
            </div>
            <h4>Description:</h4>
            <div><textarea class="form-control mb-4" form="addEditQuest" id="description" name="description" ><?= $question->get_description() ?></textarea></div>
            <!-- affichage des erreurs de description -->
            <div id="show_errors_description">
            <?php if (isset($errors["description"])): ?>
                    <ul>
                        <?php foreach ($errors["description"] as $error): ?>
                            <li class="text-warning" ><?= $error ?> </li>
                        <?php endforeach; ?>
                    </ul>
            <?php endif; ?>
        </div>
        <div class="d-flex gap-3 mb-4">
            <h4>Is this question required ?</h4>
            <input class="form-check-input md me-1" form="addEditQuest" type="checkbox" id="required" name="required" value="required" <?= $question->get_required() ? "checked" : "" ?> >
        </div>

        <div class="d-flex gap-3 mb-3">
            <select  class="form-select" form="addEditQuest" name="type_question" id="type_question" >
            <option id="option" value="" <?= $question->have_type() ? "" : "selected"  ?> >--- Select a type ---</option>
                <?php foreach (Type::cases() as $type): ?>
                    <option value=<?= $type->value ?> <?= $type->value == $question->get_type() ? "selected" : "" ?> > <?= $type->get_text() ?> </option>
                <?php endforeach; ?>
            </select>
            <button name="refresh_btn" class="btn btn-link p-0" form="addEditQuest" type="submit">
                <span class="material-symbols-outlined" style="font-size: 32px;">refresh</span>
            </button>
        </div>
        <div id="show_errors_options">
        <?php if (isset($errors["type"])): ?>
                <ul>
                    <?php foreach ($errors["type"] as $error): ?>
                        <li class="text-warning" ><?= $error ?> </li>
                    <?php endforeach; ?>
                </ul>
        <?php endif; ?>
        </div>
        <?php if ($question->is_mcq()): ?>
        <!-- si j'ai une question mcq, j'ai toujours au moin 1 box (vide si je viens de choisir le type) -->

            <?php for ($q = 0; $q < count($mcq_options); $q++): ?>
                <div class="d-flex gap-3 mb-3">
                    <input class="form-control" form="addEditQuest"  name="mcq_option_<?= $q ?>" type="text" value="<?= $mcq_options[$q] ?>">
                        <button name="remove_btn_<?= $q ?>" class="btn btn-link p-0" form="addEditQuest" type="submit">
                            <span class="material-symbols-outlined" style="font-size: 32px;">remove</span>
                        </button>
                </div>
                <?php if (isset($errors["on_option"][$q])): ?>
                    <ul>
                        <li class="text-warning" id="mcq_option_error_<?= $q ?>" ><?= $errors["on_option"][$q] ?> </li>
                    </ul>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($last_option !== false): ?>
                <div class="d-flex gap-3 mb-3">
                    <input class="form-control" form="addEditQuest"  name="last_option" type="text" value="<?= $last_option ?>">
                        <button name="add_btn" class="btn btn-link p-0" form="addEditQuest" type="submit">
                            <span class="material-symbols-outlined" style="font-size: 32px;">add</span>
                        </button>
                </div>
            <?php endif; ?>

            <?php if ($erros_last_option): ?>
                <ul>
                    <li class="text-warning" id="erros_last_option"><?= $erros_last_option ?> </li>
                </ul>
            <?php endif; ?>


        <?php if (isset($errors["options"])): ?>
            <div>
                <ul>
                    <?php foreach ($errors["options"] as $error): ?>
                        <li class="text-warning" ><?= $error ?> </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php endif; ?>

        <input id="aoe" name="aoe" value="<?= ($aoe === aoe::add ? "add" : "edit" ) ?>" hidden>
        <input form="addEditQuest" id="id" name="id" value="<?= $question->get_id() ?>" hidden>
        <input form="addEditQuest" id="idx" name="idx" value="<?= $question->get_idx() ?>" hidden>
        <input form="addEditQuest" id="id_form" name="id_form" value="<?= $question->get_form() ?>" hidden>
        </div>
    </main>
</body>

</html>