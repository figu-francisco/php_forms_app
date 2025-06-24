<?php

require_once 'Form.php';

abstract class FormValidator {

    public static function validate_title(string $title) : array {
        $errors = [];
        if (!strlen($title) > 0) {
            $errors[] = "Title is required.";
        } if (!(strlen($title) >= Configuration::get("title_min_length")
            && strlen($title) <= Configuration::get("title_max_length"))) {
            $errors[] = "Title length must be between "
                .Configuration::get("title_min_length")
                ." and "
                .Configuration::get("title_max_length")
                .".";
        } if (!(preg_match("/^[a-zA-Z].*$/", $title))) {
            $errors[] = "Title must start by a letter.";
        }
//        $errors = array("title" => $errors);
        return $errors;
    }

    public static function validate_description(string $description) : array {
        $errors = [];
        if ((strlen($description) >0 )){
            if (strlen($description) < Configuration::get("title_min_length")){
                $errors[] = "Description length must be at least "
                    .Configuration::get("title_min_length")
                    ."characters.";
            }
            //retire les caracteres "a la ligne" de la description pour le test
            $description = str_replace(array("\r","\n"),"",$description);
            if (!(preg_match("/^[a-zA-Z].*$/", $description))) {
                $errors[] = "Description must start by a letter";
            }
        }
//        $errors = array("description" => $errors);
        return $errors;
    }


    public static function is_valid_available_title(string $title, ?int $id_current_form,int $id_user) : array {
        $errors = [];
        $form = Form::get_form_by_title_and_user_id($title, $id_user);
        if ($form !== false && $form->get_id() !== $id_current_form ){
            //s'il existe un Form avec ce titre pour cette user et que ce form est different de l'actuel
            $errors[] = "Title is already used for this user.";
        }
        return $errors;
    }


}