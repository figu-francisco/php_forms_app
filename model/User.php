<?php

require_once 'model/User.php';
require_once 'model/UserValidator.php';
require_once 'framework/Model.php';
require_once 'framework/View.php';
require_once 'framework/Controller.php';

class User extends Model
{

    public function __construct(
        private string $full_name="", 
        private string $email="", 
        private string $hashed_password="", 
        private string $role="", 
        private ?int $id = NULL
        ) {}

    public function get_id(): int
    {
        return $this->id;
    }

    public function get_full_name(): string
    {
        return $this->full_name;
    }

    public function set_full_name(string $name): void
    {
        $this->full_name = $name;
    }

    public function get_email(): string
    {
        return $this->email;
    }

    public function set_email(string $email): void
    {
        $this->email = $email;
    }

    public function get_hashed_password(): string{
        return $this->hashed_password;
    }


    public static function get_user_by_email(string $email): User|false
    {
        $query = self::execute("SELECT * FROM users where email = :email", ["email" => $email]);
        $data = $query->fetch(); // un seul résultat au maximum
        if ($query->rowCount() == 0) {
            return false;
        } else {
            return new User($data["full_name"], $data["email"], $data["password"], $data["role"], $data["id"]);
        }
    }

    public static function get_user_by_full_name(string $name): User|false
    {
        $query = self::execute("SELECT * FROM users where full_name = :name", ["name" => $name]);
        $data = $query->fetch(); // un seul résultat au maximum
        if ($query->rowCount() == 0) {
            return false;
        } else {
            return new User($data["full_name"], $data["email"], $data["password"], $data["role"], $data["id"]);
        }
    }

    public static function get_user_by_id(int $id): User|false
    {
        $query = self::execute("SELECT * FROM users where id = :id", ["id" => $id]);
        $data = $query->fetch(); // un seul résultat au maximum
        if ($query->rowCount() == 0) {
            return false;
        } else {
            return new User($data["full_name"], $data["email"], $data["password"], $data["role"], $data["id"]);
        }
    }

    public function is_guest(): bool
    {
        return $this->role == "guest";
    }

    public function is_admin(): bool
    {
        return $this->role == "admin";
    }

    public function validate(): array
    {
        $errors = [];
        $errors = UserValidator::validate_name($this->full_name);
        $errors = array_merge($errors, UserValidator::validate_email($this->email));
        $errors = array_merge($errors, UserValidator::is_valid_available_email($this->email, $this->id));

        return $errors;
    }


    public function validate_name_user(): array|false
    {

        $errors = [];

        //Check erreur de nom
        $name_error = UserValidator::validate_name($this->full_name);
        $name_error = array_merge($name_error, UserValidator::is_valid_available_name($this->full_name, $this->id));
        if($name_error){
            $errors["name"] = $name_error;
        }

        //Check erreur d'email
        $email_error = UserValidator::validate_need_email($this->email);
        $email_error = array_merge($email_error, UserValidator::is_valid_available_email($this->email, $this->id));
        if($email_error){
            $errors["email"] = $email_error;
        }

        return $errors;
    }

    public function validate_signup(string $password, string $password_confirm): array|false
    {
        $errors = $this->validate_name_user();

        $password_error = UserValidator::validate_passwords($password, $password_confirm);
        if($password_error){
            $errors["password"] = $password_error;
        }

        return $errors;
    }

    public function persist(): User
    {
        if ($this->id == NULL) {
            //new user
            self::execute(
                'INSERT INTO users (full_name, email, password, role) VALUES (:full_name,:email,:password,:role)',
                [
                    'full_name' => $this->full_name,
                    'email' => $this->email,
                    'password' => $this->hashed_password,
                    'role' => $this->role
                ]
            );
            // $user = self::get_user_by_id(self::lastInsertId());
            // $this->id = $user->id;
            $this->id = self::lastInsertId();
        } else {
            //edit user
            self::execute(
                "UPDATE users SET full_name=:full_name,email=:email,password=:password,role=:role WHERE id=:id ",
                [
                    "full_name" => $this->full_name,
                    "email" => $this->email,
                    "password" => $this->hashed_password,
                    "role" => $this->role,
                    "id" => $this->id
                ]
            );
        }
        return $this;
    }

        public function set_password(String $password) : void
    {
        $this->hashed_password = password_hash($password, PASSWORD_BCRYPT);
    }
    
    public function check_current_pw(string $old_pw) : array{
        return $errors["current_pass"] = UserValidator::check_current_pw($old_pw, $this->get_hashed_password());
    }
     
    public function validate_passwords(string $new_pw, string $new_pw_confirm) : array{
        return $errors["other_errors"] = UserValidator::validate_passwords($new_pw, $new_pw_confirm); 
    }

    public function get_user_forms() : array{
        return Form::get_form_acces($this->get_id());
    }
    public function get_user_forms_filtered(string $text_search ,array $choice_color) : array{
        $list_id = UserFormAccess::get_sorted_ids_json($this, $text_search, $choice_color);
        return Form::get_form_by_list_id($list_id);
    }
    public function get_sorted_ids_json(string $filter, array $choice_color) : array{
        return UserFormAccess::get_sorted_ids_json($this, $filter, $choice_color);
    }
}
