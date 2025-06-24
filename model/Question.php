<?php
require_once 'model/ValueStat.php';
require_once 'model/Form.php';
require_once 'model/User.php';
require_once 'model/QuestionValidator.php';
//require_once 'model/Type.php';
require_once 'framework/Model.php';
require_once 'framework/View.php';
require_once 'framework/Controller.php';

enum Type: string
{
    case Date = 'date';
    case Email = 'email';
    case Long = 'long';
    case mcq_multiple = 'mcq_multiple';

    case Short = 'short';
    case mcq_single = 'mcq_single';

    /*note ENum:
        $var = Type::Date;
        $var->name ; =>  affiche 'Date'
        $var->value ; => affiche 'date'

        *** si on a FN: function(Type $var) {...}
        function('Date'); => ERROR
        function(Type::Date); => ok
        $val = Type::Date; function($val); => ok

        Type::cases(); => affiche [Type::Date, Type::Email, Type::Long, Type::Short]
        Type::cases()[0]->name; =>  affiche: 'Date'
        Type::cases()[0]->value; =>  affiche: 'date'

        $question->get_type() == "date"; on compare avec la value
    */

    public function get_text(): string
    {
        return match($this) {
            Type::Date => 'Date', //DeepSkyBlue
            Type::Email => 'Email', //Crimson
            Type::Long => 'Long text', //Gold
            Type::mcq_multiple => 'Multiple options', //Chartreuse
            Type::Short => 'Short text',
            Type::mcq_single => 'Single option',
        };
    }
}

class Question extends Model
{


    public function __construct(
        private ?int $id = null, 
        private ?int $form = null, 
        private ?int $idx = null, 
        private String $title = '', 
        private string $type = '', 
        private int $required = 0, 
        private ?string $description = null
        ) {}

    public static function get_question_by_id(int $id) : Question|false {
        $query = self::execute("SELECT * FROM questions where id = :id", ["id"=>$id]);
        $data = $query->fetch(); // un seul résultat au maximum
        if ($query->rowCount() == 0) {
            return false;
        } else {
            return new Question($data["id"], $data["form"], $data["idx"], $data["title"],
                $data["type"], $data["required"], $data["description"]);
        }
    }

    public static function get_question_by_title_and_form_id(string $title, int $form_id): Question|false
    {
        $query = self::execute(
            "SELECT * FROM questions where title=:title and form = :form_id",
            ["title" => $title, "form_id" => $form_id]
        );
        if ($query->rowCount() == 0) {
            return false;
        } else {
            $data = $query->fetch();
            return new Question($data["id"], $data["form"], $data["idx"], $data["title"],
                $data["type"], $data["required"], $data["description"]);
        }
    }

    public function get_required(): bool
    {
        return $this->required;
    }

    public function get_required_tostring() : String {
        if($this->required){
            return "True";
        } else {
            return "False";
        }
    }

    public function get_type(): string
    {
        return $this->type;
    }

    public function have_type(): bool
    {
        return $this->type != '';
    }

    public function get_idx(): int|null
    {
        return $this->idx;
    }
    public function set_idx(int $new_idx): void
    {
        $this->idx = $new_idx;
    }
    public function get_id(): int|null
    {
        return $this->id;
    }

    public function get_form(): int|null
    {
        return $this->form;
    }

    public function set_form(int $form_id): void
    {
        $this->form = $form_id;
    }

    public function get_title(): string
    {
        return $this->title;
    }

    public function get_description(): string|null
    {
        return $this->description;
    }

    public function is_mcq(): bool
    {
        return $this->type == 'mcq_single' || $this->type == 'mcq_multiple';
    }

    public function is_mcq_single(): bool
    {
        return $this->type == 'mcq_single';
    }

    public function can_edit(int $user_id): bool
    {
        //Je dois toujours vérifier si je peux éditer le formulaire de la question ou auquel on ajoute une question

        $form = Form::get_form_by_id($this->form);
        if (!$form) {
            return false;
        } else {
            return $form->can_edit($user_id);
        }
    }


    
    public function validate(array $mcq_options): array|false
    {
        $errors = array("title" => [], "description" => [], "type" => [], "options" => [], "on_option" => []);
        $errors["title"] = QuestionValidator::validate_title($this->title);
        $errors["title"] = array_merge($errors["title"],
            QuestionValidator::is_valid_available_title($this->title, $this->id, $this->form));
        //Théoriquement, on ne pourrait pas avoir d'erreur de titre invalide et de titre pas disponible :
        //  ça voudrait dire qu'on a save en DB un titre pas valide

        //  laisser pour au cas où on change la règle des titres dans l'app change en cours d'année
        $errors["description"] = QuestionValidator::validate_description($this->description);
        if($this->is_mcq()) {
            $errors["options"] = QuestionValidator::validate_options($mcq_options);
            $errors["on_option"] = QuestionValidator::validate_one_by_one_option($mcq_options);
        }
        if ($this->type == '')
            $errors["type"] = ["You need to select a type of question"];

        if (count($errors["title"]) == 0 &&
            count($errors["description"]) == 0 &&
            count($errors["type"]) == 0 &&
            (   (!$this->is_mcq()) || (
                count($errors["options"]) == 0 &&
                count($errors["on_option"]) == 0)
            ) ){
            return false;
        }else{
            return $errors;
        }
    }

    public function persist(array $mcq_options=null): Question
    {

        if ($this->id === null) {
            //new Question
            $form = Form::get_form_by_id($this->form);
            $list_question = $form->get_questions();
            if (!$list_question) {
                $list_question = [];
            }

            self::execute(
                "INSERT INTO questions(form,idx,title,description,type,required) 
                VALUES(:form,:idx,:title,:description,:type,:required)",
                [
                    "form" => $this->form,
                    "idx" => sizeof($list_question) + 1,
                    "title" => $this->title,
                    "description" => $this->description,
                    "type" => $this->type,
                    "required" => $this->required
                ]
            );

            $this->id = self::lastInsertId();

            if($this->is_mcq()) {
                if($mcq_options === null) {
                    throw new exception("No options list for mcq question");
                }
                for ($i = 0; $i < count($mcq_options); ++$i) {
                    self::execute(
                        "INSERT INTO option_values(label,question,idx) VALUES(:label,:question,:idx)",
                        [
                            "label" => $mcq_options[$i],
                            "question" => $this->id,
                            "idx" => $i + 1
                        ]
                    );
                }
            }
        } else {
            //edit form
            //une question ne peut pas changer d'id, ni de form
            self::execute(
                "UPDATE questions 
                SET idx=:idx,title=:title,description=:description,type=:type,required=:required 
                WHERE id=:id ",
                [
                    "idx" => $this->idx,
                    "title" => $this->title,
                    "description" => $this->description,
                    "type" => $this->type,
                    "required" => $this->required,
                    "id" => $this->id
                ]
            );

            if(!$this->is_mcq()) {
                //si plus de type mcq, je doit delete les options listes
                self::execute(
                    "DELETE FROM option_values WHERE question=:question",
                    ["question" => $this->id] );
            }else {
                //si je n'ai pas de $mcq_options, c'est que je mets juste a jour les autres informations,
                //  par exemple : changement ordre des questions dans un formulaire. Donc pas de modif de la liste
                //si je un $mcq_options, je mets a jour la liste, mais avant je delete l'ancienne
                if($mcq_options !== null) {
                    self::execute(
                        "DELETE FROM option_values WHERE question=:question",
                        ["question" => $this->id] );
                    for ($i = 0; $i < count($mcq_options); ++$i) {
                        self::execute(
                            "INSERT INTO option_values(label,question,idx) VALUES(:label,:question,:idx)",
                            [
                                "label" => $mcq_options[$i],
                                "question" => $this->id,
                                "idx" => $i + 1
                            ]
                        );
                    }
                }
            }
        }


        return $this;
    }
    //get all the values of an answer, grouped by value, count :
    // counts repetitions, instance_count : shows total instances
    public function get_stats() : array | null {
        if($this->is_mcq()) {
            $query = self::execute("
                SELECT ov.label as value, 
                        count(*) as count, 
                        (select count(*) 
                         from instances 
                         where form= :form and completed is not null) as instance_count
                from answers a 
                    join instances i on a.instance=i.id
                    join option_values ov on a.question = ov.question and a.idx=ov.idx
                where a.question= :question and i.completed is not null and a.value='true'
                group by ov.label
                order by count desc, value", ["form" => $this->form, "question" => $this->id]);
        }else {
            $query = self::execute("
                SELECT if(value is not null and value != '', value, '--- empty ---') as value, count(*) as count,
                    (select count(*) from instances where form=:form and completed is not null) as instance_count
                from answers a join instances i on a.instance=i.id
                where question=:question and i.completed is not null
                group by value
                order by count desc, value", ["form" => $this->form, "question" => $this->id]);
        }
        $data = $query->fetchAll();  
        $stats = [];
        if ($query->rowCount() == 0) {
            if($this->is_mcq()){
                $query = self::execute("
                        select count(*) as instance_count
                        from instances 
                        where form= :form and completed is not null",
                    ["form" => $this->form]);
                $data = $query->fetch();
                return array(new ValueStat("-- no answer --", $data['instance_count'], $data['instance_count']));
            }
            return null;
        } else{
            foreach ($data as $row){
                $stats[] = new ValueStat($row['value'], $row['count'], $row['instance_count']);
            }
        }
        return $stats;
    }

    public function get_mcq_options() : array | null{
        $mcq_options = [];

        $query = self::execute("SELECT * from option_values
        where question=:question
        order by idx ", ["question"=>$this->id]);

        $data = $query->fetchAll();
        if ($query->rowCount() == 0) {
            return null;
        } else{
            foreach ($data as $row){
                $mcq_options[] = $row['label'];
            }
        }
        return $mcq_options;
    }

    public function get_stats_as_jason() : string {
        $stats = $this->get_stats();

        $table = [];
        if($stats !== null) {
            foreach ($stats as $stat) {
                $row = [];
                $row["value"] = $stat->get_value();
                $row["count"] = $stat->get_count();
                $row["percentage"] = number_format($stat->get_count() / $stat->get_instance_count() * 100, 1);
                $table[] = $row;
            }
        }
        return json_encode($table);
    }

}
