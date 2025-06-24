<?php

require_once 'model/User.php';
require_once 'framework/Model.php';
require_once 'framework/View.php';
require_once 'framework/Controller.php';

class UserValidator extends Model{

private static function validate_password(string $password) : array {
        $errors = [];
        if (strlen($password) < Configuration::get("password_min_length") || strlen($password) > Configuration::get("password_max_length")) {
            $errors[] = "Password length must be between ".Configuration::get("password_min_length")." and ".Configuration::get("password_max_length").".";
        } if (!((preg_match("/[A-Z]/", $password)) 
                && preg_match("/[a-z]/", $password) 
                && preg_match("/\d/", $password) 
                && preg_match("/['\";:,.\/?!\\-]/", $password))) {
            $errors[] = "Password must contain a special character, a lowercase, 
                            an uppercase and a digit.";
        }
        return $errors;
    }
    
    public static function validate_passwords(string $password, string $password_confirm) : array {
        $errors = UserValidator::validate_password($password);
        if ($password != $password_confirm) {
            $errors[] = "You have to enter twice the same password.";
        }
        return $errors;
    }
    
    public static function validate_login(string $email, string $password) : array {
        $errors = [];
        $user = User::get_user_by_email($email);
        if(!$email){
            $errors[] = "you must enter an email address";
        }
        if(!$password){
            $errors[] = "you must enter a password";
        }
        if(empty($errors)) {
            if ($user) {
                if (!self::check_password($password, $user->get_hashed_password())) {
                    $errors[] = "Wrong password. Please try again.";
                }
            } else {
                $errors[] = "Can't find a member with the email '$email'. Please sign up.";
            }
        }
        return $errors;
    }


    public static function validate_name(string $name) : array {
        $errors = [];
        if (!strlen($name) > 0) {
            $errors[] = "Name is required.";
        } if (!(strlen($name) >= Configuration::get("title_min_length") && strlen($name) <= Configuration::get("title_max_length"))) {
            $errors[] = "Name length must be between ".Configuration::get("title_min_length")." and ".Configuration::get("title_max_length").".";
        } if (!(preg_match("/^[a-zA-Z].*$/", $name))) {
            $errors[] = "Name must start by a letter.";
        }
        return $errors;
    }

    public static function is_valid_available_name(string $title, ?int $id_current_user) : array {
        $errors = [];
        $user = User::get_user_by_full_name($title);
        //s'il existe un user avec ce nom et qu'il est different de celui connecter
        if ($user !== false && $user->get_id() !== $id_current_user ){ 
            $errors[] = "Name is already used.";
        }
        return $errors;
    }

    public static function validate_email(string $email) : array {
        $errors = [];

        //patern "classique" d'email qui verifie qu'il y a un ".domaine" a la fin
        // $pattern = "^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z0-9]{2,}^";

        //patern proche de celui utilisé par nos navigateurs, évite une incohérence entre l'affichage du
        //      navigateur qui valide un email sans ".domaine" à la fin, celui-ci suit la regle :
        // ABNF implements the extensions described in RFC 1123. [ABNF] [RFC5322] [RFC1034] [RFC1123]
        $pattern = "/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/";
        if (!(preg_match($pattern, $email))){
            $errors[] = "The email format is not correct.";
        }
        return $errors;
    }


    public static function validate_need_email(string $email) : array {
        $errors = [];
        if (!strlen($email) > 0) {
            $errors[] = "Email is required.";
        }
        $array_error = UserValidator::validate_email($email);
        if($array_error){
            $errors[] = $array_error[0];
        }
        return $errors;
    }

    public static function is_valid_available_email(string $email, ?int $id_current_user) : array {
        $errors = [];
        $user = User::get_user_by_email($email);
        //s'il existe un user avec cette email et qu'il est different de celui connecter
        if ($user !== false && $user->get_id() !== $id_current_user ){ 
            $errors[] = "Email is already used.";
        }
        return $errors;
    }

    private static function check_password(string $clear_password, string $hash) : bool {
        return password_verify($clear_password, $hash);
    }

    public static function check_current_pw(string $password, string $current_hash_password) : array {
        $errors = [];
        if (!self::check_password($password, $current_hash_password)) {
            $errors[] = "Wrong current password. Please enter your current password again.";
        }
        return $errors;
    }
    
}