<?php
require_once 'model/UserFormAccess.php';
require_once 'model/User.php';
require_once 'model/Question.php';
require_once 'model/Form.php';
require_once 'framework/View.php';
require_once 'framework/Controller.php';
require_once 'model/Form.php';
require_once 'model/User.php';
require_once 'controller/Aoe.php';
require_once 'model/MyController.php';
require_once 'utils/Base64Helper.php';
class ControllerForm extends MyController{

    public function index() : void {
        $this->view_form();
    }

    public function add_form(): void
    {
        $this->add_edit_form(Aoe::add);
    }

    public function edit_form(): void
    {
        $this->add_edit_form(Aoe::edit);
        //recup le numeros de form, check si je peux l'editer puis lit tout les params
    }

    private function add_edit_form(Aoe $aoe) : void {
        //Init des varia
        $user = $this->get_user_or_redirect();
        $user_id = $user->get_id();

        $errors = false;

        $form = $this->try_recup_param_get();

        if(!$form->can_edit($user_id)){
            $this->dev_error("You can't edit this form");
        }

        $encoded_search = "";
            if(isset($_GET['param2'])){
                $encoded_search = $_GET['param2'];
            }

        if (!empty($_POST)) {
            $this->recup_param_post($form);
            $errors = $this->try_save_and_leave($form, $encoded_search);
        }

        (new View("add_edit_form"))->show([
                                            "user" => $user,
                                            "form" => $form,
                                           "aoe"=>$aoe ,
                                           "errors" => $errors,
                                           "encoded_search" => $encoded_search]);
    }

    private function recup_param_post(Form $form): void
    {
        //recupérer variable Post

        $colors = [];

        if(isset($_POST['colors'])){
            $colors = $_POST['colors'];
        }

        if(!isset($_POST['title']) && !isset($_POST['description']) ){
            $this->dev_error("missing post param");
        }
        if(isset($_POST['public'])){
            $public =  true;
        }else{
            $public =  false;
        }

        $form->set_title($_POST['title']);
        $form->set_description($_POST['description']);
        $form->set_is_public($public);
        $form->set_colors($colors);

        //owner pas mis a jour, on ne change pas de proprio
        //comme obj direct mis a jour, pas besoin de le return
    }

    private function try_recup_param_get(): Form|false
    {
        //recupérer variable GET
        if (isset($_GET['param1'])) {
            if (!is_numeric($_GET['param1']) /* || isset($_GET['param2'] )*/) {
                //si pas format numérique ou trop de param
                //trop de param check taken out because interferes with encoded_search
                $this->redirect("form", "edit_form");
            } else {
                $form_id = $_GET['param1'];
                $form = Form::get_form_by_id($form_id);
                if(!$form){
                    $this->dev_error("Form does not exist");
                }
            }
        }else{
            $form = new Form();
            $user = $this->get_user_or_redirect();
            $form->set_owner($user->get_id());
        }

        return $form;
    }

    private function try_save_and_leave(Form $form, string $encoded_search): array
    {
        $errors = $form->validate();

        if (count($errors["title"]) == 0 && count($errors["description"]) == 0) {
            $form->persist();
            $this->redirect("form", "index", $form->get_id(), $encoded_search);
        }

        return $errors;
    }
    
    private function view_form() : void {
        $user = $this->get_user_or_redirect();
        //check if isset, if numeric & if exists
        if ($this->form_id_get_ok()) {
            $form = Form::get_form_by_id($_GET['param1']);
            if(!$form->has_edit_access($user)){
                $this->dev_error("You can't edit this form");
            }
            //if encoded_search is altered, doesn't keep it for next screen
            $encoded_search = "";
            if(isset($_GET['param2']) /* && Base64Helper::isBase64($_GET['param2']) */){
                $encoded_search = $_GET['param2'];
            }
            $questions = $form->get_questions();
            // Afficher la vue avec les questions et l'utilisateur
            (new View("form"))->show(["user" => $user,
                                    "form" => $form,
                                    "user" => $user,
                                    "questions" => $questions,
                                    "encoded_search" => $encoded_search]);
        } else {
        $this->dev_error("wrong or missing param");
        }
    }

    //gère la suppression d'un form
    public function delete() : void {
        $user = $this->get_user_or_redirect();
        //check if isset, if numeric & if exists
        if ($this->form_id_post_ok()){
            $form = Form::get_form_by_id($_POST['form_id']);
            $encoded_search = "";
            if(isset($_GET['param1'])){
                $encoded_search = $_GET['param1'];
            }
            //if user has edit access (owner)
            if($form->has_edit_access($user)){
                if($form->delete($user)){
                    $this->redirect("forms", "index", $encoded_search);
                }
            }
            $this->dev_error("Wrong/missing ID or action no permited");
        }
    }

    public function question_up() : void{
        $user = $this->get_user_or_redirect();
        $question_up = true;
        $this->question_up_down_try_no_script($user, $question_up);
    }

    public function question_down(): void{
        $user = $this->get_user_or_redirect();
        $question_up = false;
        $this->question_up_down_try_no_script($user, $question_up);
    }

    private function question_up_down_try_no_script(User $user, bool $question_up): void{
        //try peut contenir ID du form ou false
        $encoded_search = "";
        if(isset($_GET['param1'])){
            $encoded_search = $_GET['param1'];
        }
        $try = $this->question_up_down($user, $question_up);
        if($try){
            $this->redirect("form", "index", $try, $encoded_search);
        }else{
            $this->dev_error("wrong or missing param");
        }
    }

    private function question_up_down(User $user, bool $question_up): int|false{
        //check if params set, numeric and if form exists
        //using POST because the change of idx it's done on DB
        if ($this->form_id_post_ok() && isset($_POST['question_idx']) && 
            is_numeric($_POST['question_idx'])){
            $form = Form::get_form_by_id($_POST['form_id']);
            $idx = $_POST['question_idx'];
            //check if idx exists in form
            if($form->has_question_idx($idx) &&
            //check if there is a question to swap with, up or down
                $question_up ? $form->has_question_idx($idx-1) : $form->has_question_idx($idx+1)){
                if($form->has_edit_access($user)){
                    $form->question_move_up_down($idx, $question_up);
                    return $form->get_id();
                }
            }
        }
        return false;
    }

    public function remove_question_confirmation(): void{
        $user = $this->get_user_or_redirect();
        if ($this->form_id_get_ok() && isset($_GET['param2']) && is_numeric($_GET['param2'])) {
            $form = Form::get_form_by_id($_GET['param1']);
            if ($this->params_ok_for_del_question($user, $form, $_GET['param2'])) {
                $question = Question::get_question_by_id($_GET['param2']);
                $encoded_search = "";
                if(isset($_GET['param3'])){
                    $encoded_search = $_GET['param3'];
                }
                (new View("remove_popup"))->show(["form" => $form,
                                                        "question" => $question,
                                                        "encoded_search" => $encoded_search]);
            }
        }else{
            $this->dev_error("wrong or missing param");
        }
    }

    public function remove_question(): void{
        $user = $this->get_user_or_redirect();
        if ($this->form_id_post_ok() && isset($_POST['question_id']) && is_numeric($_POST['question_id'])) {
            $form = Form::get_form_by_id($_POST['form_id']);
            if($this->params_ok_for_del_question($user, $form, $_POST['question_id'])){
                $question_id = $_POST['question_id'];
                $form->remove_question($question_id);
                $encoded_search = "";
                if(isset($_GET['param1'])){
                    $encoded_search = $_GET['param1'];
                }
                $this->redirect("form", "index", $form->get_id(), $encoded_search);
            }
        }else{
            $this->dev_error("wrong or missing param");
        }
    }

    private function params_ok_for_del_question(User $user, Form $form, int $question_id) : bool {
        //check question exists in form
        if(!$form->check_form_has_question($question_id)){
            $this->dev_error("question id doesn't exists in this form");
        }
        //check user has edit access 
        if(!$form->has_edit_access($user)){
            $this->dev_error("no user access");
        }
        //check form isn't readonly 
        if($form->is_readonly()){
            $this->dev_error("this form is readonly");
        }
        return true;
    }

    public function remove_form_confirmation(): void{
        $user = $this->get_user_or_redirect();
        //check if set, if numeric & if exists
        if ($this->form_id_get_ok()){
            $form_id = ($_GET['param1']);
            $form = Form::get_form_by_id($form_id);
            $encoded_search = "";
            if(isset($_GET['param2'])){
                $encoded_search = $_GET['param2'];
            }
            if($form->has_edit_access($user)){
                (new View("remove_popup"))->show(["form" => $form, "encoded_search" => $encoded_search]);
            }else{
                $this->dev_error("no access user");
            }  
        }else{
            $this->dev_error("wrong or missing param");
        }
    }

    public function toggle_public(): void{
        $user = $this->get_user_or_redirect();
        //check if set & if exists
        if ($this->form_id_post_ok()){
            $form_id = $_POST['form_id'];
            $form = Form::get_form_by_id($form_id);
            $encoded_search = "";
            if(isset($_GET['param1'])){
                $encoded_search = $_GET['param1'];
            }
            if($form->has_edit_access($user)){
                $form->toggle_public();
                $this->redirect("form", "index", $form_id, $encoded_search);
            }
            $this->dev_error("wrong or missing param or no access user");
        }else{
            $this->dev_error("wrong or missing param or no access user");
        }
    }

    private function form_id_post_ok() : bool{
        return (isset($_POST['form_id']) && is_numeric($_POST['form_id']) &&
        Form::get_form_by_id($_POST['form_id']));
    }
    private function form_id_get_ok() : bool{
        return (isset($_GET['param1']) && is_numeric($_GET['param1']) &&
        Form::get_form_by_id($_GET['param1']));
    }

//****************************************************************************// 
//                                                                            //
//                              AJAX SERVICES                                 //
//                                                                            //  
//****************************************************************************// 

//services for manupulating questions

public function question_up_service() : void{
    $user = $this->get_user_or_http_error();
    if(!$user) return;

    $question_up = true;
    $questions_json = $this->question_up_down_try_for_services($user, $question_up);
    echo $questions_json;
}
public function question_down_service(): void{
    $user = $this->get_user_or_http_error();
    if(!$user) return;

    $question_up = false;
    $questions_json = $this->question_up_down_try_for_services($user, $question_up);
    echo $questions_json;
}

private function question_up_down_try_for_services(User $user, bool $question_up): string{
    //try peut contenir ID du form ou False
    $try = $this->question_up_down($user, $question_up);
    if(!$try){
        $this->dev_error_for_service("wrong or missing param");
        return '';
    }
    //if ID recovered correctly, then return questions as json (string)
    return $this->get_questions_for_services();
}

private function get_questions_for_services() : string { 
    $user = $this->get_user_or_http_error();
    if(!$user) return '';
    
    $questions_json = '';
    if ($this->form_id_post_ok()) {
        $form = Form::get_form_by_id($_POST['form_id']);
        if(!$form->has_edit_access($user)){
            $this->dev_error_for_service("get_questions_for_services : You can't edit this form");
            return $questions_json;
        }
        $questions_json = $form->get_questions_as_json();
    } else {
        $this->dev_error_for_service("get_questions_for_services : wrong or missing param");
    }
    return $questions_json;
}

public function get_questions_service() : void {
    $user = $this->get_user_or_http_error();
    if(!$user) return;

    if ($this->form_id_get_ok()) {
        $form = Form::get_form_by_id($_GET['param1']);
        if(!$form->has_edit_access($user)){
            $this->dev_error_for_service("get_questions_service : You can't edit this form");
            return;
        }
        $questions_json = $form->get_questions_as_json();
        // Afficher la vue avec les questions et l'utilisateur
        echo $questions_json;
    } else {
        $this->dev_error_for_service("get_questions_service : wrong or missing param");
    }
}

public function question_move_service(): void{
    $user = $this->get_user_or_http_error();
    if(!$user) return;

    if ($this->form_id_post_ok() &&
        ( isset($_POST['orginal_idx']) && is_numeric($_POST['orginal_idx']) ) &&
        ( isset($_POST['new_idx']) && is_numeric($_POST['new_idx']) ) ) {
            //recup info utile
            $form = Form::get_form_by_id($_POST['form_id']);
            $orginal_idx = $_POST['orginal_idx'];
            $new_idx = $_POST['new_idx'];

            //check if idx exists in form
            if($form->has_question_idx($orginal_idx) && $form->has_question_idx($new_idx) ){

                if($form->has_edit_access($user)){
                    $form->question_move_to_new_idx($orginal_idx, $new_idx);
                    $questions_json = $this->get_questions_for_services();
                    echo $questions_json;
                }else{
                    $this->dev_error_for_service("question_move_service : You can't edit this form");
                    return;
                }

            }else{
                $this->dev_error_for_service("question_move_service : idx don't exist");
                return;
            }

    }else{
        $this->dev_error_for_service("question_move_service : wrong or missing param");
    }
}

public function remove_question_service(): void{
    $user = $this->get_user_or_http_error();
    if(!$user) return;

    if ($this->form_id_post_ok() && isset($_POST['question_id']) && is_numeric($_POST['question_id'])) {
        $form = Form::get_form_by_id($_POST['form_id']);
        if($this->params_ok_for_del_question($user, $form, $_POST['question_id'])){
            $question_id = $_POST['question_id'];
            $form->remove_question($question_id);
            $questions_json = $this->get_questions_for_services();
            echo $questions_json;
        }
    }else{
        $this->dev_error_for_service("remove_question_service : wrong or missing param");
    }
}


// services for ecoded search

public function get_ids_filtered_ajax() : void {
    $user = $this->get_user_or_http_error();
    if(!$user) return;

    if (!isset($_POST['filter'])) {
        $this->dev_error_for_service("get_ids_filtered_ajax : wrong or missing param");
        return;
    }
    $filter = $_POST['filter'];

    $choice_color = [];
    if (isset($_POST['choice'])) { //pas de poste "choice" si pas de checkbox selection pour couleur
        $choice_color = $_POST['choice'];
    }

    $array = $user->get_sorted_ids_json($filter, $choice_color);
    $array[] = MyTools::merge_search_and_encode_ajax( $filter, $choice_color);

    //$array contient dans l'ordre:
    //  - les id OK pour la recherche
    //  - un tableau avec en 1er l'encodage du filtre texte
    //                       2ieme les couleurs choisi (peut-etre vide)
    echo json_encode($array);
}

public function decode_search() : void{
    $user = $this->get_user_or_http_error();
    if(!$user) return;

    $encoded_search = $_GET["param1"];
    $res = Base64Helper::url_safe_decode($encoded_search);
    $json_data = json_encode($res);
    echo $json_data;
}

//****************************************************************************// 
//                                                                            //
//                              FIN AJAX SERVICES                             //
//                                                                            //  
//****************************************************************************// 
}