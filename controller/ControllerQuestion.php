<?php

require_once 'model/User.php';
require_once 'model/Question.php';
require_once 'controller/Aoe.php';
require_once 'model/MyController.php';

class ControllerQuestion extends MyController
{

    public function index(): void
    {
        $this->add_question();
    }

    public function add_question(): void
    {
        $this->add_edit_question(Aoe::add);
    }

    public function edit_question(): void
    {
        $this->add_edit_question(Aoe::edit);
    }

    private function add_edit_question(Aoe $aoe): void
    {

        //Init des varia
        $user = $this->get_user_or_redirect();
        $user_id = $user->get_id();
        $mcq_options = [];

        $errors = false;

        $last_option = false;
        $erros_last_option = false;

        $encoded_search = "";
        //--- Pour le script JS, qui ne doit rien faire dans le cas d'un new add
        $new_add = "true";
        if(isset($_GET['param2'])){
            $encoded_search = $_GET['param2'];
        }

        if (!empty($_POST)) {
            $new_add = "false";
            $question = $this->recup_param_post();

            //get multiple choice questions or empty array
            if($question->is_mcq()){
                $mcq_options = $this->get_mcq_post();
                $last_option = $this->get_last_option_post_or_empty();
            }

            //we try to save only if save_btn was pressed
            if(isset($_POST['save_btn'])){
                $errors = $this->try_save_and_leave($question, $encoded_search, $mcq_options);
            }else{//we do a check but don't save
                if(isset($_POST['add_btn'])){
                    $erros_last_option = QuestionValidator::validate_last_option($mcq_options,$last_option);
                    if($erros_last_option === false){
                        $mcq_options[] = $last_option;
                        $last_option=""; //je rajoute un champ vide pour nouvelle question
                    }
                }
                $errors = $question->validate($mcq_options);
            }
        } else {
            $question = new Question();
            //toujours besoin d'un param qui contient l'id du formulaire(add) ou id question (edit)
            if (isset($_GET['param1'])) {

                if (!is_numeric($_GET['param1']) /* || isset($_GET['param2']) */) {
                    //si pas format numérique /* ou trop de param */
                    $this->dev_error("Wrong param use");
                } else {

                    if ($aoe === Aoe::add) {
                        //en add, j'ai besoin de l'id d'un form auquel ajouté la question
                        $form_id = 0 + $_GET['param1'];
                        $question->set_form($form_id);
                    } else if($aoe === Aoe::edit){
                        $new_add = "false";
                        //en edit, j'ai besoin de l'id de la question a edit
                        $quest_id = 0 + $_GET['param1'];
                        $question = Question::get_question_by_id($quest_id);
                        if($question->is_mcq()){
                            $mcq_options = $question->get_mcq_options();
                            $last_option = "";
                        }
                    }

                    if (!$question->can_edit($user_id)) {
                        $this->dev_error("Unable to add or edit question for this form");
                    }
                }
            } else {
                $this->dev_error("missing param");
            }
        }
        (new View("add_edit_question"))->show(["question" => $question, 
                                               "errors" => $errors,
                                               "last_option" => $last_option,
                                               "erros_last_option" => $erros_last_option,
                                               "aoe" => $aoe,
                                               "encoded_search" => $encoded_search,
                                               "mcq_options" => $mcq_options,
                                                "new_add" => $new_add]);
    }

    private function get_mcq_post(): array{
        //*********** Refactor Audry inspired by Francisco
        $mcq_options = [] ;
        // je lis tout les mcq_option_X qui sont present dans le POST et les rajoutes:
        $q = 0;
        while(isset($_POST['mcq_option_'.$q])){
            //SAUF si c'est une option que je veux retiré via un btn remove,
            if( isset($_POST['remove_btn_'.$q]) ){
                //do nothing
            }else{
                $mcq_options[] = $_POST['mcq_option_'.$q];
            }
            $q++;
        }
        return $mcq_options;
    }
    private function get_last_option_post_or_empty(): string
    {
        if(isset($_POST['last_option'])){
            return $_POST['last_option'];
        }else{
            return "";
        }
    }

    private function recup_param_post(): Question
    {
        //recupérer variable Post
        //verification que les variable existe bien
        if(!isset($_POST['title']) || !isset($_POST['description']) || !isset($_POST['type_question'])
            && !isset($_POST['id']) || !isset($_POST['idx']) || !isset($_POST['id_form']) ) {
            $this->dev_error("missing post param");
        }

        //verification que le format est cohérent
        if(
            !empty($_POST['id']) && !is_numeric($_POST['id']) ||
            !empty($_POST['id']) && !is_numeric($_POST['idx']) ||
            !is_numeric($_POST['id_form']) ) {
            $this->dev_error("wrong post param");
        }

        if( !empty($_POST['type_question']) && Type::tryFrom($_POST['type_question'] ) == null){
            $this->dev_error("wrong post type for question_type");
        }


        $title = $_POST['title'];
        $description = $_POST['description'];
        $type_question = $_POST['type_question'];

        if (isset($_POST['required'])) {
            $required =  true;
        } else {
            $required =  false;
        }

        if ($_POST['id'] !== "") {
            $id = 0 + $_POST['id'];
        } else {
            $id = null;
        }

        if ($_POST['idx'] !== "") {
            $idx = 0 + $_POST['idx'];
        } else {
            $idx = null;
        }

        $id_form = $_POST['id_form'];

        $question = new Question($id, $id_form, $idx, $title, $type_question, $required, $description);
        return $question;
    }

    private function try_save_and_leave(Question $question, string $encoded_search, array $mcq_options): array
    {
        $errors = $question->validate($mcq_options);
        if (!$errors) {
            $question->persist($mcq_options);
            $this->redirect("form", "index", $question->get_form(), $encoded_search);
        }
        return $errors;
    }


//****************************************************************************// 
//                                                                            //
//                              AJAX SERVICES                                 //
//                                                                            //  
//****************************************************************************// 

    public function validate_title_service(): void{
        $user = $this->get_user_or_http_error();
        if(!$user) return;

        $title = "";
        $errors = [];
        $errors_available_title = [];
        if(isset($_POST['title_val']) && isset($_POST['form_id']) && is_numeric($_POST['form_id'])) {
            $title = $_POST['title_val'];
            $form_id = $_POST['form_id'];
            if(isset($_POST['question_id'])){
                if(!is_numeric($_POST['question_id'])){
                    $this->dev_error_for_service("validate_description_service : wrong or missing param");
                    return;
                }
                $question_id = $_POST['question_id'];
                $errors_available_title =
                    QuestionValidator::is_valid_available_title($title, $question_id, $form_id);
                //$errors[] = "for edit question";
            }else{
                $errors_available_title =
                    QuestionValidator::is_valid_available_title_for_nw_quest_service($title, $form_id);
            } 
            $errors = array_merge(QuestionValidator::validate_title($title), $errors_available_title);
        }else{
            $this->dev_error_for_service("validate_description_service : wrong or missing param");
            return;
        }
        echo json_encode($errors);
    }

    public function validate_description_service(): void{
        $user = $this->get_user_or_http_error();
        if(!$user) return;

        $description = "";
        $errors = [];
        if(isset($_POST['description'])){
            $description = $_POST['description'];
            $errors = QuestionValidator::validate_description($description);
        }else{
            $this->dev_error_for_service("validate_description_service : wrong or missing param");
            return;
        }
        echo json_encode($errors);
    }

//****************************************************************************// 
//                                                                            //
//                              FIN AJAX SERVICES                             //
//                                                                            //  
//****************************************************************************// 

    

    //debug service
    /* public function test_trim(): void{
        $title = $_POST['title_val'];
        var_dump($title);
    } */
}
