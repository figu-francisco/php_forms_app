<?php
require_once 'model/Form.php';
require_once 'model/Answer.php';
require_once 'model/AnswerValidator.php';
require_once 'framework/View.php';
require_once 'framework/Controller.php';
require_once 'framework/Tools.php';
require_once 'model/MyController.php';

enum ComeFrom
{
    case view ;
    case delete;
    case delete_all ;
}

class ControllerInstance extends MyController{
    public function index() : void {
        $this->add_edit_instance();
    }

    private function add_edit_instance() : void {
        if (isset($_POST['form_id']) && is_numeric($_POST['form_id']) && $this->params_nw_form_ok($_POST['form_id'])) {
            $form = Form::get_form_by_id($_POST['form_id']);
            //check if encoded search
            $encoded_search = "";
            if(isset($_GET['param1'])){
                $encoded_search = $_GET['param1'];
            }
            $user = $this->get_user_or_redirect();
            $instance = $form->get_most_recent_instance($user); //if user guest:returns null
            if($instance == null){
                //new instance
                $this->create_new_instance($form->get_id(), $encoded_search);
             }else if($instance->is_completed()){
                //sends to popup confirmation : submit again or view last
                $this->redirect("instance", "show_add_edit_instance_popup", $form->get_id(), $encoded_search);
             }else{
                //resumes an already started instance
                $instance_id = $instance->get_id();
                $this->redirect("instance", "resume", $instance_id, 1, $encoded_search);
             }
        }else{
            $this->dev_error("wrong or missing param");
        }
    }

    public function show_add_edit_instance_popup() : void {
        if (isset($_GET['param1']) && is_numeric($_GET['param1']) && $this->params_nw_form_ok($_GET['param1'])) {
            $form_id = $_GET['param1'];
            $form = Form::get_form_by_id($form_id);
            $user = $this->get_user_or_redirect();
            $instance = $form->get_most_recent_instance($user);
            $encoded_search = "";
            if(isset($_GET['param2'])){
                $encoded_search = $_GET['param2'];
            }
            (new View("add_edit_instance_popup"))->show(["form" => $form, 
                                                         "instance" => $instance, 
                                                         "encoded_search" => $encoded_search]);
        }else{
            $this->dev_error("wrong or missing param");
        }
    }

    private function params_nw_form_ok(int $param) : bool {
        $user = $this->get_user_or_redirect();
        $bool = false;
        //form has to exist
        if(Form::get_form_by_id($param)){
            $form =  Form::get_form_by_id($param);
            //if form owner or if form is public => OK
            if(($form->has_edit_access($user) || $form->is_public()) &&
            //there has to be at least one question in the form 
            $form->get_questions() != null){
                $bool = true;
            } 
        }
        return $bool;    
    }
    
    //creates a new instance & redirects
    private function create_new_instance(int $form_id, string $encoded_search) : void {
        $form = Form::get_form_by_id($form_id); 
        $user = $this->get_user_or_redirect();
        $date_now = date_create("now");
        $result = $date_now->format('Y-m-d-H-i-s');
        $instance = new Instance($form->get_id(), $user->get_id(), $result, null, null);
        $instance = $instance->persist(); //now instance has id
        $idx = 1; //start from question #1
            
        $this->redirect("instance", "resume", $instance->get_id(), $idx, $encoded_search);
    }

    public function show_instance() : void {
        if (isset($_GET['param1']) && is_numeric($_GET['param1'])){
            $instance_id = $_GET['param1'];
            $instance = Instance::get_instance_by_id($instance_id);
            $user = $this->get_user_or_redirect();
            $form_id = $instance->get_form_id();
            $form = Form::get_form_by_id($form_id);
            $idx = 1; //start from question #1
            $question = $form->get_question_by_idx($idx);
            $mcq_options = $question->get_mcq_options();
            $mcq_answers_idx = Answer::get_mcq_answers_idx_by_key($instance_id, $question->get_id());
            $answer = null;
            $errors = [];
            (new View("add_edit_instance"))->show(["user" => $user, // maybe I don't need the user...
                                                    "form" => $form, 
                                                    "instance" => $instance, 
                                                    "question" => $question, 
                                                    "idx" => $idx,
                                                    "answer" => $answer,
                                                    "errors" => $errors,
                                                    "mcq_options" => $mcq_options,
                                                    "mcq_answers_idx" => $mcq_answers_idx]);
        }else{
            $this->dev_error("show_instance : invalid param");
        }
        
    }

     //entry methods
    public function submit_new(): void{//from popup confirmation : submit againt or view last
        if (isset($_POST['form_id']) && is_numeric($_POST['form_id']) && $this->params_nw_form_ok($_POST['form_id'])) {
            $form_id = $_POST['form_id'];
            $encoded_search = "";
            if(isset($_GET['param1'])){
                $encoded_search = $_GET['param1'];
            }
            $this->create_new_instance($form_id, $encoded_search);
        }else{
            $this->dev_error("wrong or missing param");
        }
    }
     public function resume(): void{
        $this->get_params("resume");
    }
    public function next(): void{
        $this->get_params("next");
    }
    public function previous(): void{
        $this->get_params("previous");
    }
    public function save(): void{
        $this->get_params("errors_or_success");
    }
    public function exit(): void{
        $this->get_params("exit");
    }
    public function quit(): void{
        $this->get_params("quit_confirmation");
    }
    public function readonly(): void{
        $this->get_params("readonly");
    }
    public function readonly_from_instances(): void{
        $this->get_params("readonly_from_instances");
    }

    //get params, check params & redirects
    private function get_params(string $from): void{
        if ($this->params_ok($from)) {
            $instance_id = $_GET['param1'];
            $idx = $_GET['param2'];
            $encoded_search = "";
            if(isset($_GET['param3'])){
                $encoded_search = $_GET['param3'];
            };
            if($from == "readonly" && empty($_POST)){
                $this->show_readonly($instance_id, $idx, false, $encoded_search);
            }elseif($from == "readonly_from_instances" && empty($_POST)){
                $this->show_readonly($instance_id, $idx, true, $encoded_search);
            }elseif(($from == "resume") && empty($_POST)){
                $this->show_question($instance_id, $idx, $encoded_search);
            }else{
                $this->persist_redirect($instance_id, $idx, $from, $encoded_search);
            }
        }else{
            $this->dev_error("wrong or missing param");
        }
    }

    //check parameters
    private function params_ok(string $from) : bool {
        $user = $this->get_user_or_redirect();
        $bool = false;
        if(isset($_GET['param1']) && isset($_GET['param2']) && 
            is_numeric($_GET['param1']) && is_numeric($_GET['param2']) &&
            //if instance exists
            Instance::get_instance_by_id($_GET['param1'])){
            //get instance and form
            $instance = Instance::get_instance_by_id($_GET['param1']);
            $form = Form::get_form_by_id($instance->get_form_id());
            $idx = $_GET['param2'];
            //if question idx exists in the form
            if($idx > 0 && $idx <= sizeof($form->get_questions())) {
                    if($this->from_readonly_ok($from, $form, $user, $instance) ||
                        $this->not_from_readonly_ok($from, $form, $user, $instance) ||
                        $this->from_resume_ok($from, $form, $user, $instance)){ 
                            $bool = true;
                }
            }
        }
        return $bool;
    }
    private function from_readonly_ok(string $from, Form $form, User $user, Instance $instance) : bool {
        //EITHER from readonly_from_instances, user is form owner, not guest, instance is completed 
        //OR from readonly, user is instance owner, not guest, instance is completed 
        return !$user->is_guest() && 
                $instance->is_completed() &&    
                (($from == "readonly_from_instances" && $form->has_edit_access($user))
                    || ($from == "readonly" && $user->get_id() == $instance->get_user()));
    }
    private function not_from_readonly_ok(string $from, Form $form, User $user, Instance $instance) : bool {
        //not from readonly nor readonly_from_instances, user is instance owner, instance isn't completed, 
        //there is POST : (next/previous/save/exit/quit_confirmation)
        return $from != "readonly" && 
                $from != "readonly_from_instances" && 
                $user->get_id() === $instance->get_user() && 
                !$instance->is_completed() /* && !empty($_POST) */; //the last check isn't necessary
    }
    private function from_resume_ok(string $from, Form $form, User $user, Instance $instance) : bool {
        //from resume, user is instance owner, instance isn't completed
        // (next&previous after redirect / resume / cancel)
        return $from == "resume" && 
                $user->get_id() === $instance->get_user() && 
                !$instance->is_completed();
    }
    //saves answers, redirects
    private function persist_redirect(int $instance_id, int $idx, string $from, string $encoded_search) : void {
        //$user = $this->get_user_or_redirect();
        $value = "";
        //if there is an answer (does not apply to mcq) 
        if(isset($_POST['value']) && $_POST['value'] != ""){
            $value = $_POST["value"];
        }
        //saves the answer value (or values mcq)
        $this->persist_value($instance_id, $idx, $value);
        //redirects 
        if($from == "next"){
            $this->redirect("instance", "resume", $instance_id, ++$idx, $encoded_search);
        }else if($from == "previous"){
            $this->redirect("instance", "resume",$instance_id, --$idx, $encoded_search);
        }else if($from == "errors_or_success"){
            $this->show_errors_or_success($instance_id, $idx, $encoded_search);
        }else if($from == "exit"){
            $this->redirect("forms", "index", $encoded_search);
        }else{
            $this->redirect("instance", "show_remove_popup", $instance_id, $idx, $encoded_search);
        }
    }
    //saves answers
    private function persist_value(int $instance_id, int $idx, string $value) : void{
        $instance = Instance::get_instance_by_id($instance_id);
        $form = Form::get_form_by_id($instance->get_form_id());
        $question = $form->get_question_by_idx($idx);
        $selected_indexes = [];
        //for mcq
        if($question->is_mcq()){
            //if it's a single mcq, put in in an array
            //if($question->get_type() == "mcq_single"){
            //    if(isset($_POST['answer'])){
            //        $selected_indexes[] = $_POST['answer']; 
            //    }  
            //}else{//if it's a multiple mcq, it already comes in an array
                if(isset($_POST['answers'])){
                    $selected_indexes = $_POST['answers']; 
                }
            //}
            $mcq_options = $question->get_mcq_options();
            $array_size = count($mcq_options);
            //iterate and save answers to all mcq options
            //if option selected, value gets true
            for($i = 0; $i < $array_size; ++$i){
                $value = "false";
                $current_idx = $i+1;
                //if the index matches the index of the answers sent, then value gets true
                if(in_array($current_idx, $selected_indexes, false)){
                    $value = "true";
                }
                $answer = new Answer($instance_id, $question->get_id(), $value, $current_idx);
                $answer->persist();   
            }
        }else{//for non mcq 
            $answer = new Answer($instance_id, $question->get_id(), $value);
            $answer->persist();
        }
    }

    //check answers, returns an array with errors or empty
    private function check_answer(int $instance_id, int $idx, string $value) : array {
        $errors = [];
        $instance = Instance::get_instance_by_id($instance_id);
        $form = Form::get_form_by_id($instance->get_form_id());
        $question = $form->get_question_by_idx($idx);
        $answer = new Answer($instance_id, $question->get_id(), $value);
        if($answer != null && $answer->get_value() != ""){
            $errors = $answer->validate_answer();//returns array with errors or empty
        } 
        if($question->get_required()){
            if($answer == null || empty($answer->get_value())){
                $errors[] = "question required";
            }
        }
        return $errors;
    }

    //check mcq answers, returns an array with errors or empty
    private function check_mcq_answer(int $instance_id, int $idx) : array {
        $errors = [];
        $instance = Instance::get_instance_by_id($instance_id);
        $form = Form::get_form_by_id($instance->get_form_id());
        $question = $form->get_question_by_idx($idx);
        $mcq_answers = Answer::get_mcq_answers_idx_by_key($instance_id, $question->get_id());
        if($question->get_required() && $mcq_answers == null){
            $errors[] = "Question required";
        }
        if($question->is_mcq_single() && count($mcq_answers) > 1){
            $errors[] = "Only one option can be selected";
        }
        return $errors;
    }

    //creates the view of a question
    private function show_question(int $instance_id, int $idx, string $encoded_search): void{
        $user = $this->get_user_or_redirect();
        $instance = Instance::get_instance_by_id($instance_id);
        $form = Form::get_form_by_id($instance->get_form_id());
        //next/previous question and answer
        $question = $form->get_question_by_idx($idx);
        $mcq_options = $question->get_mcq_options();
        $mcq_answers_idx = Answer::get_mcq_answers_idx_by_key($instance_id, $question->get_id());//if no answers then empty
        $answer = Answer::get_answer_by_key($instance_id, $question->get_id());//if no answer then null
        //gets errors in array
        $errors = [];
        //only search of errors if an answer has already been saved on db
        if(!$question->is_mcq() && Answer::get_answer_by_key($instance_id, $question->get_id()) != null){
            $errors = $this->check_answer($instance_id, $idx, $answer->get_value());
        }else if($question->is_mcq() && !Answer::first_time_in_question($instance_id, $question->get_id())){
            $errors = $this->check_mcq_answer($instance_id, $idx);
        }
        (new View("add_edit_instance"))->show(["user" => $user, 
                                                "form" => $form, 
                                                "instance" => $instance, 
                                                "question" => $question, 
                                                "idx" => $idx,
                                                "answer" => $answer,
                                                "errors" => $errors,
                                                "encoded_search" => $encoded_search,
                                                "mcq_options" => $mcq_options,
                                                "mcq_answers_idx" => $mcq_answers_idx]);
    }

    

    public function show_remove_popup(): void{
        if(!$this->params_ok("resume")){
            $this->dev_error("show_remove_popup : wrong params");
        }
        $instance_id = $_GET['param1'];
        $idx = $_GET['param2'];
        $encoded_search = "";
            if(isset($_GET['param3'])){
                $encoded_search = $_GET['param3'];
            };
        (new View("remove_popup"))->show([
                            "instance_id" => $instance_id,
                            "idx" => $idx,
                            "encoded_search" => $encoded_search]);
    } 

    //redirects to first answer with error or to successfully submited popup
    private function show_errors_or_success(int $instance_id, int $idx, string $encoded_search): void{
        $instance = Instance::get_instance_by_id($instance_id);
        $form = Form::get_form_by_id($instance->get_form_id());
        //$question = $form->get_question_by_idx($idx);
        //search for the first question with error
        $questions = $form->get_questions();
        $errors = [];
        foreach($questions as $question){
            if($question->is_mcq()){
                $errors = $this->check_mcq_answer($instance_id, $question->get_idx());
            }else{
                $answer = Answer::get_answer_by_key($instance_id, $question->get_id());
                $errors = $this->check_answer($instance_id, $question->get_idx(), $answer->get_value());
            }
            if(!empty($errors)){
                //redirects to view of first question with error
                $this->redirect("instance",
                    "show_rectify_answer_popup",
                    $instance_id,
                    $question->get_idx(),
                    $encoded_search);
                break;
            }
        }
        if(empty($errors)){
            $date_now = date_create("now");
            $result = $date_now->format('Y-m-d-H-i-s');
            $instance->set_completed($result);
            $instance->persist_completed();
            $this->redirect("instance", "show_successfully_submited_popup", $encoded_search);
        }
    }

    public function show_successfully_submited_popup() : void{
        $this->get_user_or_redirect();
        $msg = "The form has been successfully submitted.";
        $encoded_search = "";
        if(isset($_GET['param1'])){
            $encoded_search = $_GET['param1'];
        };
        (new View("successfully_submited_popup"))->show(["msg" => $msg, "encoded_search" => $encoded_search]);
    }

    public function show_rectify_answer_popup() : void{
        if(!$this->params_ok("resume")){
            $this->dev_error("show_rectify_answer_popup : wrong params ");
        }
        $user = $this->get_user_or_redirect();
        $instance_id = $_GET['param1'];
        $idx = $_GET['param2'];
        $instance = Instance::get_instance_by_id($instance_id);
        $form = Form::get_form_by_id($instance->get_form_id());
        $question = $form->get_question_by_idx($idx);
        $mcq_options = $question->get_mcq_options();
        $mcq_answers_idx = Answer::get_mcq_answers_idx_by_key($instance_id, $question->get_id());//if no answers then empty
        $answer = Answer::get_answer_by_key($instance_id, $question->get_id());
        $encoded_search = "";
            if(isset($_GET['param3'])){
                $encoded_search = $_GET['param3'];
            };
        (new View("correct_answers_popup"))->show(["answer" => $answer, 
                                                   "question" => $question, 
                                                   "instance_id" => $instance_id, 
                                                   "user" => $user,
                                                   "encoded_search" => $encoded_search,
                                                   "mcq_options" => $mcq_options,
                                                   "mcq_answers_idx" => $mcq_answers_idx]);
    }

    //readonly
    private function show_readonly(int $instance_id, int $idx, bool $from_instances, string $encoded_search): void{
        $user = $this->get_user_or_redirect();
        $instance = Instance::get_instance_by_id($instance_id);
        $form = Form::get_form_by_id($instance->get_form_id());
        //next/previous question and answer
        $question = $form->get_question_by_idx($idx);
        $mcq_options = $question->get_mcq_options();
        $mcq_answers_idx = Answer::get_mcq_answers_idx_by_key($instance_id, $question->get_id());//if no answers then empty
        $answer = Answer::get_answer_by_key($instance_id, $question->get_id());//if no answer then null
        (new View("readonly_instance"))->show(["user" => $user, 
                                                "form" => $form, 
                                                "instance" => $instance, 
                                                "question" => $question, 
                                                "idx" => $idx,
                                                "answer" => $answer,
                                                "from_instances" => $from_instances,
                                                "encoded_search" => $encoded_search,
                                                "mcq_options" => $mcq_options,
                                                "mcq_answers_idx" => $mcq_answers_idx]);
    }

    public function delete(): void{
        $user = $this->get_user_or_redirect();
        //is isset & instance id exists
        if(isset($_POST['instance_id']) && is_numeric($_POST['instance_id'])
            && Instance::get_instance_by_id($_POST['instance_id'])){
            $encoded_search = "";
            if(isset($_GET['param1'])){
                $encoded_search = $_GET['param1'];
            };
            $instance_id = $_POST['instance_id'];
            $instance = Instance::get_instance_by_id($instance_id);
            $instance->delete($user);
            $this->redirect("forms", "index", $encoded_search);
        }
        $this->dev_error("missing param");
    }



    public function view_instances() : void {
        $this->view_delete_instances();
    }

    public function delete_instances() : void {
        $this->view_delete_instances(true);
    }

    public function delete_all_instances() : void {
        //check user et que le formulaire existe et qu'il y a acces
        $form = $this->check_user_and_get_form();

        $encoded_search = "";
        if(isset($_GET['param2'])){
            $encoded_search = $_GET['param2'];
        }

        //recup toutes les instances meme les non soumises
        $submitted_intances = $form->get_all_instance();

        //on recupere les id de ces instances
        $to_delete = [];
        foreach ($submitted_intances as $instance) {
            $to_delete[] = $instance->get_id();
        }
        //on vat a la fenetre de popup de confirmation
        (new View("remove_popup"))->show([
            "form" => $form,
            "instances_to_delete" =>  $to_delete,
            "return_to_form" => true,
            "encoded_search" => $encoded_search]);
    }

    private function view_delete_instances(bool $push_delete_button=false ) : void {
        $form = $this->check_user_and_get_form();

        $encoded_search = "";
        if(isset($_GET['param2'])){
            $encoded_search = $_GET['param2'];
        }

        if (!empty($_POST)) {
            //si j'ai du post, je recup id et je vais sur la fenetre popup de confirmation

            $to_delete = $this->check_post_for_id_to_delete();
            (new View("remove_popup"))->show([
                "form" => $form,
                "instances_to_delete" => $to_delete,
                "encoded_search" => $encoded_search]);
        } else {
            //je recup toutes les instances soumises

            $submitted_intances = $form->get_all_submitted_instance_desc();

            // si aucune instance, je quitte
            if (empty($submitted_intances)) {
                $this->dev_error("No submitted instance");
            }
            // $push_delete_button => si on appuye sur delete sans formulaire selectionné (donc pas de POST)
            //          => on affiche message d'erreur
            (new View("instances"))->show([
                "submitted_intances" => $submitted_intances,
                "form" => $form,
                "push_delete_button" =>$push_delete_button,
                "encoded_search" => $encoded_search ]);
        }
    }

    public function delete_instances_confirmed() : void {
        $form = $this->check_user_and_get_form();

        $encoded_search = "";
        if(isset($_GET['param2'])){
            $encoded_search = $_GET['param2'];
        }

        if (empty($_POST)) {
            $this->dev_error("missing post param");
        }

        $to_delete = $this->check_post_for_id_to_delete();

        $this->multi_delete_and_redirect($to_delete,$form,$encoded_search);
    }

    private function check_user_and_get_form() : Form {
        $user = $this->get_user_or_redirect();
        $form = null;
        if (!isset($_GET['param1'])) {
            $this->dev_error("missing param");
        }else {
            if (!is_numeric($_GET['param1']) || isset($_GET['param3'])) {
                //si pas format numérique ou trop de param
                $this->dev_error("Wrong param use");

            } else {
                $form_id = $_GET['param1'];
                $form = Form::get_form_by_id($form_id);
                if (!$form) {
                    $this->dev_error("This form doesn't exist");
                }
                if (!$form->has_edit_access($user)) {
                    $this->dev_error("You do not have permission to edit this form");
                }
            }
        }
        return $form;
    }

    private function check_post_for_id_to_delete() : array {
        $to_delete = [];

        foreach($_POST as $key => $id_for_delete){
            if(!preg_match("/^id[0-9]{1,}$/", $key) ) {
                $this->dev_error("bad post name");
            }
            if(!is_numeric($id_for_delete)) {
                $this->dev_error("bad post format");
            }
            $to_delete[] = $id_for_delete;
        }

        return $to_delete ;
    }


    private function multi_delete_and_redirect(array $to_delete, Form $form, string $encoded_search) : void {
        $user = $this->get_user_or_redirect();

        foreach($to_delete as $id_for_delete){
            $instance = Instance::get_instance_by_id($id_for_delete);
            if($instance->get_form_id() != $form->get_id() ){
                $this->dev_error("the instance ".$instance->get_form_id().
                    " does not belong to the form ".$form->get_id());
            }
            $instance->delete($user);
        }

        if($form->has_complete_instances()){
            $this->redirect("instance", "view_instances", $form->get_id(), $encoded_search);
        }else{
            $this->redirect("form","index", $form->get_id(), $encoded_search);
        }
    }

    public function analyze(): void{
        $user = $this->get_user_or_redirect();
        //if form_id isset & form exists
        if(isset($_GET['param1']) && is_numeric($_GET['param1']) &&
            Form::get_form_by_id($_GET['param1'])){

            if(isset($_POST['question'])){
                //pas trouvé moyen d'utiliser un <select...> sans passer par un form (en GET ou POST),
                //comme le form en GET genere un '?' dans l'url, on fait un redirect pour le retiré
                //comme le form en POST est 'transformé' en GET, l'url devient bookmarkables
                //$_GET['param2'] is the encoded search, once redirected it becomes param3, if present...
                //that's because the encoded search has to be the last param 
                $encoded_search = "";
                if(isset($_GET['param2'])){
                    $encoded_search = $_GET['param2'];
                }
                $this->redirect("instance", "analyze", $_GET['param1'], $_POST['question'], $encoded_search);
            }

            $form_id = $_GET['param1'];
            $form = Form::get_form_by_id($form_id);
            if(!$form->has_edit_access($user)){
                $this->dev_error("user no access");
            }

            if(!$form->has_complete_instances()){
                $this->dev_error("no submitted instances for this form");
            }

            $stats = null;
            $stats_jason = null;
            $idx = 0;
            $questions = $form->get_questions();
            $encoded_search = "";
            //if param2 is numeric == idx, if is not numeric then it's the encoded search from form (first entry)
            if(isset($_GET['param2']) && is_numeric($_GET['param2']) ){
                $idx = ($_GET['param2']);
                if(isset($questions[$idx - 1])){
                    $stats = $questions[$idx - 1]->get_stats();
                    $stats_jason = $questions[$idx - 1]->get_stats_as_jason();
                }else{
                    $this->dev_error("no question with this index");
                }
            }else if(isset($_GET['param2'])){
                $encoded_search = $_GET['param2'];
            }
            //if param3 exists is the encoded search comming from the redirect (looping from option list) 
            if(isset($_GET['param3'])){
                $encoded_search = $_GET['param3'];
            }

            (new View("analyze"))->show(["user" => $user,
                                         "form" => $form,
                                         "questions" => $questions,
                                         "idx" => $idx,
                                         "stats" => $stats,
                                         "stats_jason" => $stats_jason,
                                         "encoded_search" => $encoded_search]);

        }else{
            $this->dev_error("wrong or missing param");
        }
    }

}

