<?php

require_once 'framework/View.php';
require_once 'framework/Controller.php';
require_once 'framework/Model.php';
require_once 'model/User.php';
require_once 'model/UserValidator.php';
require_once 'model/UserFormAccess.php';
require_once 'model/MyController.php';

class ControllerMain extends MyController {

    //si l'utilisateur est connecté, redirige vers son menu de formulaires (view_forms).
    //sinon, produit la vue login.
    // 27/11/24 : mis en comment pour pouvoir aller directement sur view_form depuis l'index
    public function index() : void {
        $this->login();
    }

    //gestion de la connexion d'un utilisateur
    public function login() : void {
        if ($this->user_logged()) {
            $this->redirect("forms", "index");
        } else {
            $email = '';
            $password = '';
            $errors = [];
            if (isset($_POST['email']) && isset($_POST['password'])) { //note : pourraient contenir des chaînes vides
                $email = $_POST['email'];
                $password = $_POST['password'];

                $errors = UserValidator::validate_login($email, $password);
                if (empty($errors)) {
                    $this->log_user(User::get_user_by_email($email));
                }
            }
            (new View("login"))->show(["email" => $email, "password" => $password, "errors" => $errors]);
        }
    }

     //gestion de l'inscription d'un utilisateur
     public function signup() : void {

         if($this->guest_logged()) {
             //rien a faire, le cancel de signup renvoie sur main/login 
             //qui reagis si utilisateur log (meme guest) => renvoie vers form/index
         }elseif ($this->user_logged()) {
             $this->redirect("forms", "index");
         }
         $email = '';
         $fullname = '';
         $password = '';
         $password_confirm = '';
         $errors = [];

         $user = new User();

         if (isset($_POST['email']) && 
            isset($_POST['full_name']) && 
            isset($_POST['password']) && 
            isset($_POST['password_confirm'])) {
                $email = $_POST['email'];
                $fullname = $_POST['full_name'];
                $password = $_POST['password'];
                $password_confirm = $_POST['password_confirm'];
                $role = 'user';
                $id = null;

                $user = new User($fullname, $email, password_hash($password, PASSWORD_BCRYPT), $role, $id);
                $errors = $user->validate_signup($password, $password_confirm);

                if (!$errors) {
                    $user->persist(); //sauve l'utilisateur
                    $this->log_user($user); //log fait un redirect
                }
         }
         (new View("signup"))->show(["user" => $user, "fullname" => $fullname, "password" => $password,
                                             "password_confirm" => $password_confirm, "errors" => $errors]);
    }

    public function error() : void {
        $user = $this->get_user_or_redirect();
        if(isset($_GET['param1'])){
            $this->dev_error($_GET['param1']);
        }else{
            $this->dev_error("no specific message for this error");
        }
    }

}