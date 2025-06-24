<?php
require_once 'model/Question.php';
require_once 'model/Instance.php';
require_once 'model/User.php';
require_once 'framework/Model.php';
require_once 'framework/View.php';
require_once 'framework/Controller.php';

class Answer extends Model {

    public function __construct(
        private int $instance_id, 
        private int $question_id, 
        private string $value,
        private ?int $idx = -1
        ) {}

    public function get_question() : Question {
        return Question::get_question_by_id($this->question_id);
    }   
    
    public function get_value() : string {
        return $this->value;
    }

    public function get_idx() : int {
        return $this->idx;
    }

    private function new_answer() : bool {
        $query = self::execute("select * from answers where instance=:instance and question=:question and idx=:idx", 
                                ["instance" => $this->instance_id, "question" => $this->question_id, "idx"=>$this->idx]);
        if ($query->rowCount() == 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function get_answer_by_key(int $instance, int $question) : Answer | null {
        $query = self::execute("select * from answers where instance=:instance and question=:question", 
                                ["instance" => $instance, "question" => $question]);
        if ($query->rowCount() == 0) {
            return null;
        } else {
            $row = $query->fetch();
            return new Answer($row['instance'], $row['question'], $row['value']);
        }
    }

    public function persist() : Answer {
        if($this->new_answer()){
            //new answer
            self::execute("INSERT INTO answers(instance,question,value,idx) VALUES(:instance,:question,:value,:idx)",
                          ["instance"=>$this->instance_id, "question"=>$this->question_id, "value"=>$this->value, "idx"=>$this->idx]);
        }else{
            self::execute("UPDATE answers SET value=:value WHERE question=:question AND instance=:instance AND idx=:idx",
                          ["instance"=>$this->instance_id, "question"=>$this->question_id, "value"=>$this->value, "idx"=>$this->idx]);
        }
        return $this;
    }    
    
    public function validate_answer() : array { //trans in validate()
        $errors = [];
        $question = Question::get_question_by_id($this->question_id);
        $question_type = $question->get_type();
        
        if($question_type == "email"){
            $errors = AnswerValidator::validate_email($this->value);
        }elseif($question_type == "date"){
            $errors = AnswerValidator::validate_date($this->value);
        }
        return $errors;
    }

    public static function get_mcq_answers(int $instance, int $question) : array | null {
        $mcq_answers = [];
        $query = self::execute("select * from answers where instance=:instance and question=:question", 
                                ["instance" => $instance, "question" => $question]);
        if ($query->rowCount() == 0) {
            return null;
        } else {
            $data = $query->fetchAll();
            foreach ($data as $row){
                $mcq_answers[] = new Answer($row['instance'], $row['question'], $row['value'], $row['idx']);
            }
        }
            
        return $mcq_answers;
    }

    public static function get_mcq_answers_idx_by_key(int $instance, int $question) : array {
        $mcq_answers_idx = [];
        $query = self::execute("select * from answers where instance=:instance and question=:question and value=:value", 
                                ["instance" => $instance, "question" => $question, "value" => 'true']);
        if ($query->rowCount() == 0) {
            //
        } else {
            $data = $query->fetchAll();
            foreach ($data as $row){
                $mcq_answers_idx[] = $row['idx'];
            }
        }
            
        return $mcq_answers_idx;
    }

    public static function first_time_in_question(int $instance, int $question) : bool {
        $query = self::execute("select * from answers where instance=:instance and question=:question", 
                                ["instance" => $instance, "question" => $question]);
        if ($query->rowCount() == 0) {
            return true;
        }
            
        return false;
    }
}