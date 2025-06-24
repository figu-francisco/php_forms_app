<?php

require_once 'model/User.php';
require_once 'framework/Model.php';
require_once 'framework/View.php';
require_once 'framework/Controller.php';
require_once 'utils/MyTools.php';

class Instance extends Model {

    public function __construct(
        private int $form, 
        private int $user, 
        private ?string $started, 
        private ?string $completed, 
        private ?int $id
        ) {}


    public function get_time_inteval_since_started() : string | null {
        return MyTools::time_elapsed_string($this->started);
    }

    public function get_time_inteval_since_completed() : string | null {
        return MyTools::time_elapsed_string($this->completed);
    }

    public function persist() : Instance | null {
        self::execute("INSERT INTO instances(form, user, started, completed) 
                            VALUES(:form,:user,:started,:completed)",
                          ["form"=>$this->form,
                              "user"=>$this->user,
                              "started"=>$this->started,
                              "completed"=>$this->completed]);
        
        $instance = self::get_instance_by_id(self::lastInsertId());
        return $instance;
    }

    public function is_completed() : bool {
        return $this->completed != null;
    }

    public static function get_instance_by_id(int $instance_id) : Instance|false {
        $query = self::execute("select * from instances where id = :id", ["id" => $instance_id]);
        if ($query->rowCount() == 0) {
            return false;
        } else {
            $row = $query->fetch();
            return new Instance($row['form'], $row['user'], $row['started'], $row['completed'], $row['id']);
        }
    }


    public function get_id() : int{
        return $this->id;
    }

    public function get_started() : string{
        return $this->started;
    }

    public function get_user() : int {
        return $this->user;
    }

    public function validate_user(User $user) : bool{
        return $user == $this->user;
    }

    public function get_form_id(): int{
        return $this->form;
    }

    public function get_completed(): string|null{
        return $this->completed;
    }

    //retuns true if this instance is the most recent instance of the user in parameter
    public function check_last_instance_user(int $user_id) : bool {
        $query = self::execute("SELECT * FROM instances where user = :user
        order by started desc
        limit 1", ["user"=>$user_id]);
        $data = $query->fetch();
        if ($query->rowCount() == 0) {
            return false;
        } 
        return $data["id"] == $this->id;
    } 
    
    //deletes instance if user has access (is the instance's owner or the form's owner)
    //returns the instance if ok. false otherwise
    public function delete(User $initiator) : bool 
    {
        $form = Form::get_form_by_id($this->form);
        if($this->user == $initiator->get_id() || $form->has_edit_access($initiator)){
            $this->remove();
            return true;
        }
        return false;
    }

    private function remove() : void
    {
        self::execute('DELETE FROM instances WHERE id = :id', ['id' => $this->id]);
    }

    public function persist_completed() : Instance | null {
        self::execute("UPDATE instances SET completed=:completed WHERE id = :id",
                          ["id"=>$this->id, "completed"=>$this->completed]);
        return $this;
    }
    
    public function set_completed(string $date) : void {
        $this->completed = $date;
    }

}