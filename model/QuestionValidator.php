<?php

require_once 'Question.php';

abstract class QuestionValidator {

    public static function validate_title(string $title) : array {
        $errors = [];
        if (!strlen($title) > 0) {
            $errors[] = "Title is required.";
        } if (!(strlen($title) >= Configuration::get("title_min_length") && strlen($title) <= Configuration::get("title_max_length"))) {
            $errors[] = "Title length must be between ".Configuration::get("title_min_length")." and ".Configuration::get("title_max_length").".";
        } if (!(preg_match("/^[a-zA-Z].*$/", $title))) {
            $errors[] = "Title must start by a letter.";
        }
        return $errors;
    }

    public static function validate_description(string $description) : array {
        $errors = [];
        if ((strlen($description) >0 )){
            if (strlen($description) < Configuration::get("title_min_length")){
                $errors[] = "Description length must be at least ".Configuration::get("title_min_length")." characters.";
            }
            //retire les caracteres "a la ligne" de la description pour le test
            $description = str_replace(array("\r","\n"),"",$description);
            if (!(preg_match("/^[a-zA-Z].*$/", $description))) {
                $errors[] = "Description must start by a letter";
            }
        }
        return $errors;
    }


    public static function is_valid_available_title(string $title, ?int $id_current_question, int $id_form) : array {
        $errors = [];
        $question = Question::get_question_by_title_and_form_id($title,$id_form);
        //echo 'in error check title';
        if ($question !== false && $question->get_id() !== $id_current_question ){ //s'il existe une Question avec ce titre pour ce formulaire et que cette question est different de l'actuel
            $errors[] = "Title is already used for this form.";
        }
        return $errors;
    }

    public static function is_valid_available_title_for_nw_quest_service(string $title, int $id_form) : array {
        $errors = [];
        
        $question = Question::get_question_by_title_and_form_id($title,$id_form);
        
        if ($question !== false){ //s'il existe une Question avec ce titre pour ce formulaire 
            $errors[] = "Title is already used for this form.";
        }
        return $errors;
    }

    public static function validate_options(array $mcq_options) : array {
        //check la contrainte général d'un choix multiple : pas vide
        $errors = [];
        if(count($mcq_options) === 0){
            $errors[] = "At least one option is required.";
        }
        return $errors;
    }

    public static function validate_one_by_one_option(array $mcq_options) : array {
        // $mcq_options contient des options qui ont déja été ajouté a la liste, je dois verifier :
        //    pas de blanc/vide
        //    pas de doublon
        $errors_on_option = [];
        for($i = 0; $i < count($mcq_options); $i++){
            if ($mcq_options[$i] == ''){
                $errors_on_option[$i] = "the option cannot be empty";
            }else{
                for($j = $i+1; $j < count($mcq_options); $j++){
                    if ($mcq_options[$i] == $mcq_options[$j]){
                        $errors_on_option[$i] = "option already exists ";
                    }
                }
            }
        }

        return $errors_on_option;
    }

    public static function validate_last_option(array $mcq_options, string $last_options) : string|false {
        //note quand j'arrive ici, mcq_options a tjr au moin 1 val
        $errors = false;
//        $idx_last = count($mcq_options) - 1;
        if($last_options == ""){
            $errors = "cannot add empty option";
        }else{
            for($i = 0; $i < count($mcq_options); $i++){
                if ($mcq_options[$i] == $last_options){
                    $errors = "option already exists";
                }
            }
        }
        return $errors;
    }


}