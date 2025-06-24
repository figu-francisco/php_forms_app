<?php

require_once 'model/User.php';
require_once 'model/MyController.php';
class ControllerUser extends MyController
{
    public function index(): void
    {
        $this->settings();
    }

    public function settings(): void
    {
        //Init des varia
        $user = $this->get_user_but_not_guest();

        (new View("settings"))->show(["user" => $user]);
    }

    public function edit_profile(): void
    {
        //Init des varia
        $user = $this->get_user_but_not_guest();
        $errors = false;
        //Crée un clone, sionon les modif fait sur $user seront repercuter sur la variable SUPERGLOBAL $_SESSION,
        //   cela changera, par exemple, le nom affiché sur toutes les autres fenetre.
        $user_clone = clone $user;

        if (!empty($_POST)) {
            if(!isset($_POST['name']) && !isset($_POST['email']) ){
                $this->dev_error("missing post param");
            }
            $name = $_POST['name'];
            $email = $_POST['email'];
            $user_clone->set_full_name($name);
            $user_clone->set_email($email);
            $errors = $this->try_save_and_leave($user_clone);
        }

        (new View("edit_profile"))->show(["user" => $user_clone, "errors"=>$errors]);
    }

    private function get_user_but_not_guest(): User {
        $user = $this->get_user_or_redirect();
        if($user->is_guest()){
            $this->dev_error("Quest can't access this windows");
        }
        return $user;
    }

    private function try_save_and_leave(User $user): array
    {
        $errors = $user->validate_name_user();
        if (!$errors) {
            $user->persist();
            //on se reconnecte avec l'user editer pour forcer la mise a jour de la variable $_SESSION["user"]
            $this->log_user($user,"user");//effectue un redirect
        }
        return $errors;
    }

    public function change_password() : void
    {
        $user = $this->get_user_but_not_guest();
        $errors = array("current_pass" => [], "other_errors" => []);
        $old_pw = "";
        $new_pw = "";
        $new_pw_confirm = "";
        if(isset($_POST['old_pw']) && isset($_POST['new_pw']) && isset($_POST['new_pw_confirm'])){
            $old_pw = $_POST['old_pw'];
            $new_pw = $_POST['new_pw'];
            $new_pw_confirm = $_POST['new_pw_confirm'];

            if(empty($_POST['old_pw']) || empty($_POST['new_pw']) || empty($_POST['new_pw_confirm'])){
                $err[] = "You must fill up all fields";
                $errors["other_errors"] = $err;
            }else{
                $errors["current_pass"] = $user->check_current_pw($old_pw);
                $errors["other_errors"] = $user->validate_passwords($new_pw, $new_pw_confirm);
                if(empty($errors["current_pass"]) && empty($errors["other_errors"])){
                    $user->set_password($new_pw);
                    $user->persist();
                    $this->redirect("user/change_pw_success");
                }
            }
        }

        (new View("change_password"))->show(["errors" => $errors,
            "old_pw" => $old_pw, "new_pw" => $new_pw,"new_pw_confirm" => $new_pw_confirm
        ]);
    }

    public function change_pw_success(): void{
        //from this view encoded_search isn't required
        //but successfullt_submited_popup is a shared page that expects the vairable $encoded_search 
        $encoded_search = ''; 
        $msg = "Your password has been successfully updated.";
            (new View("successfully_submited_popup"))->show(["msg" => $msg, "encoded_search" => $encoded_search]);
    }
}
