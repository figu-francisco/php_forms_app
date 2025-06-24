<?php
require_once 'model/Question.php';
require_once 'model/Instance.php';
require_once 'model/User.php';
require_once 'framework/Model.php';
require_once 'framework/View.php';
require_once 'framework/Controller.php';

abstract class AnswerValidator extends Model {

    public static function validate_email(String $email) : array{
        return UserValidator::validate_email($email);
    }

    public static function validate_date(String $date_string) : array {
        $errors = [];
        $format = "Y-m-d";
        $d = DateTime::createFromFormat($format, $date_string);
        if(!$d){
            $errors[] = "not valid date format"; 
        }else{
            $parts = explode("-", $date_string);
            list($year, $month, $day) = $parts;
            if(!checkdate((int)$month, (int)$day, (int)$year)){
                $errors[] = "date does not exist";
            }      
        } 
        return $errors;
    }
}