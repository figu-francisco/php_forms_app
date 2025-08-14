<?php
require_once 'model/Question.php';
require_once 'model/Instance.php';
require_once 'model/User.php';
require_once 'model/FormValidator.php';
require_once 'model/UserFormAccess.php';
require_once 'framework/Model.php';
require_once 'framework/View.php';
require_once 'framework/Controller.php';

class Form extends Model {

    public function __construct(
        private ?int $id = null, 
        private string $title = "", 
        private ?int $owner = null, 
        private int $is_public=1, 
        private ?string $description = null,
        private array $color = [] //! enum list
        ) {}

    public function get_id() : int|null {
        return $this->id;
    }

    public function set_id(?int $id): void {
        $this->id = $id;
    }

    public function is_public() : bool {
        return $this->is_public;
    }

    public function set_is_public(bool $is_public) : void{
        $this->is_public = $is_public;
    }

    public function get_title() : string {
        return $this->title;
    }
    public function set_title(?string $title) : void {
        $this->title = $title;
    }

    public function get_description() : string|null {
        return $this->description;
    }

    public function set_description(?string $description) : void {
        $this->description = $description;
    }

    public function get_owner() : int|null {
        return $this->owner;
    }

    public function set_owner(int $owner) : void {
        $this->owner = $owner;
    }

    public function get_owner_fullname() : string {
        $user_owner = User::get_user_by_id($this->owner);
        return $user_owner->get_full_name();
    }

    public function get_color() : array {
        return $this->color;
    }

    public function set_colors(array $colors) : void {
        $list_color_enum = [];
        foreach ($colors as $color_name){
            $enum = Color::tryFrom($color_name);
            if($enum === null){
                throw new Exception('type of color '.$color_name.' not found, adapt enum if need');
            }
            $list_color_enum[] = Color::tryFrom($color_name);
        }
        $this->color = $list_color_enum;
    }

    private static function from_row_to_new_form(array $row): Form
    {
        $list_color_name = Form::get_color_by_id($row['id']);
        /*
        On passe par un enum Color afin d’obtenir les couleurs au
          format HEX (ex. #000000) via la méthode Color:get_hex().
        On aurait très bien pu se contenter d’utiliser les noms des couleurs, tant qu’ils correspondent à des
          noms valides en CSS (comme "red", "blue", etc.), et les utiliser directement dans la vue (apres les avoir
          stockés dans une variable), par exemple : style=" color:<?= $var? > "
        */
        $list_color_enum = [];

        if(sizeof($list_color_name) !== 0){ //test
            foreach ($list_color_name as $color_name){
                $enum = Color::tryFrom($color_name);
                if($enum === null){
                    throw new Exception('type of color '.$color_name.' not found, adapt enum if need');
                }
                $list_color_enum[] = Color::tryFrom($color_name);
            }
        }

        return new Form($row['id'], $row['title'], $row['owner'],
            $row['is_public'], $row['description'],$list_color_enum);
    }
    public static function get_form_by_id(int $form_id) : Form|false {
        $query = self::execute("select * from forms where id = :id", ["id" => $form_id]);
        if ($query->rowCount() == 0) {
            return false;
        } else {
            $row = $query->fetch();
            return Form::from_row_to_new_form($row);
        }
    }

    public static function get_form_by_list_id(array $list_id) : array {
        $res=[];
        foreach ($list_id as $id) {
            $res[] = Form::get_form_by_id($id);
        }
        return $res;
    }

    public static function get_form_by_title_and_user_id(string $title, int $user_id) : Form|false {
        $query = self::execute("SELECT * FROM forms where title=:title and owner = :user_id",
                               ["title"=>$title, "user_id"=>$user_id]);
        if ($query->rowCount() == 0) {
            return false;
        } else {
            $data = $query->fetch();
            return Form::from_row_to_new_form($data);
        }
    }

    //deplacer depuis UserFormAcess
    public static function get_form_acces(int $user_id) : array {
        $query = self::execute("select * from forms where 
                               owner=:userid 
                       or ( is_public=1 and forms.id in (select form from questions))
         order by title", ["userid" => $user_id]);
        $data = $query->fetchAll();
        $forms = [];
        foreach ($data as $row) {
            $forms[] = Form::from_row_to_new_form($row);
        }
        return $forms;
    }

    public function get_most_recent_instance(User $user) : Instance | null {
        // si l'utilisateur est un invité, on ne peut pas récupérer sa dernière instance
        if ($user->is_guest())
            return null;

        $query = self::execute("SELECT * FROM instances where form = :id
        and user = :user
        order by started desc
        limit 1", ["id"=>$this->id, "user"=>$user->get_id()]);

        $data = $query->fetch();
        if ($query->rowCount() == 0) {
            return null;
        } else{
            return new Instance($data["form"], $data["user"], $data["started"], $data["completed"], $data["id"]);
        }
    } 

    public function get_questions() : array | null{
        $query = self::execute("SELECT * FROM questions where form = :id 
                                order by idx", ["id"=>$this->get_id()]);
        $data = $query->fetchAll();                        
        $questions = [];
        if ($query->rowCount() == 0) {
//            return null;
        } else{
            foreach ($data as $row) {
                $questions[] = new Question($row['id'], $row['form'], $row['idx'],
                    $row['title'], $row['type'], $row['required'], $row['description']);
            }
        }
        return $questions;
    }

    //********************************************************
    //********************************************************
    //********************************************************
    public function get_questions_as_json() : string {

        $questions = $this->get_questions();

        $table = [];
        foreach ($questions as $question) {
            $row = [];
            $row["id"] = $question->get_id();
            $row["idx"] = $question->get_idx();
            $row["title"] = $question->get_title();
            $row["type"] = $question->get_type();
            $row["required"] = $question->get_required();
            $row["description"] = ($question->get_description() === null ? "": $question->get_description());
            $table[] = $row;
        }
        return json_encode($table);
    }

    public function has_edit_access(User $user) : bool{
        return $this->at_me($user->get_id());
    }

    public function persist() : Form {
        if($this->id === null){
            //new form
            self::execute("INSERT INTO forms(title,description,owner,is_public) 
                                VALUES(:title,:description,:owner,:is_public)",
                            ["title"=>$this->title,
                              "description"=>$this->description,
                              "owner"=>$this->owner,
                              "is_public"=>$this->is_public ? 1 : 0]);
            $this->id = self::lastInsertId();
        
            foreach($this->color as $col){
            //insert colors
            self::execute("INSERT INTO form_colors(form,color) 
            VALUES(:form,:color)",
                ["form"=>$this->id,
                "color"=>$col->value]);
            }
        }else{
            //edit form
//            echo "<br> !!!! je suis dans l'edition de formulaire <br>";
            self::execute("UPDATE forms 
                                SET title=:title,description=:description,is_public=:is_public 
                                WHERE id=:id ",
                        ["title"=>$this->title,
                            "description"=>$this->description,
                            "is_public"=>$this->is_public ? 1 : 0,
                            "id"=>$this->id]);


                            //delete existing color records
                            self::execute("DELETE FROM form_colors 
                            WHERE form=:form",
                                    ["form"=>$this->id]);

                            foreach($this->color as $col){
                                //intert colors
                                self::execute("INSERT INTO form_colors(form,color) 
                                VALUES(:form,:color)",
                                    ["form"=>$this->id,
                                    "color"=>$col->value]);
                            }

        }


        return $this;
    }

//    public function validate(Form $f, User $user) : array {
    public function validate() : array {        
        $errors = array("title" => [] , "description" => []);
        $errors["title"] = FormValidator::validate_title($this->title);
        $errors["title"] = array_merge(
            $errors["title"],
            FormValidator::is_valid_available_title($this->title,$this->id,$this->owner));
        //Théoriquement, on ne pourrait pas avoir d'erreur de titre invalide et de titre pas disponible : 
        //ça voudrait dire qu'on a save en DB un titre pas valide
        //  laisser pour au cas où on change la règle des titres dans l'app change en cours d'année
        $errors["description"] = FormValidator::validate_description($this->description);

        return $errors;
    }

    
    public function can_edit(int $user_id) : bool {
        //si owner null = nouveau formulaire => tlm peut l'editer
        //  ou (form a moi et pas en readonly )
        return  $this->get_owner() === null ||
            ( $this->at_me($user_id) && !$this->is_readonly() );
    }

    private function at_me(int $user_id) : bool
    {
        $user = User::get_user_by_id($user_id);
        return $user->is_admin() || $this->get_owner() === $user_id ;
    }

    //supprimer le form si l'initiateur en a le droit
    //renvoie le form si ok. false sinon.
    public function delete(User $initiator) : Form|false 
    {
        if ($this->has_edit_access($initiator)) {
            $this->remove();
            return $this;
        }
        return false;
    }

    private function remove() : void
    {
        self::execute('DELETE FROM forms WHERE id = :post_id', ['post_id' => $this->id]);
    }

    public function get_question_by_idx(int $idx) : Question|null {
        $query = self::execute("select * 
                                     from questions 
                                    where form = :id and idx = :idx", ["id" => $this->id, "idx" => $idx]);
        if ($query->rowCount() == 0) {
            return null;
        } else {
            $row = $query->fetch();
            return new Question($row['id'], $row['form'], $row['idx'],
                $row['title'], $row['type'], $row['required'], $row['description']);
        }
    }

    public function check_form_has_question(int $id) : bool {
        $query = self::execute("select * 
                                     from questions 
                                    where form = :form and id = :id", ["form" => $this->id, "id" => $id]);
        if ($query->rowCount() == 0) {
            return false;
        } else {
            $row = $query->fetch();
            return $row['id'] == $id;
        }
    }

    public function get_all_instance() : array {
        $query = self::execute("select * from instances where form = :form", ["form" => $this->id]);
        return $this->read_all_submitted_instance($query);
    }

    public function get_all_submitted_instance() : array {
        $query = self::execute("select * 
                                    from instances 
                                    where form = :form and completed is not null", ["form" => $this->id]);
        return $this->read_all_submitted_instance($query);
    }

    public function get_all_submitted_instance_desc() : array {
        $query = self::execute("select * 
                                    from instances 
                                    where form = :form and completed is not null 
                                    order by completed desc" , ["form" => $this->id]);
        return $this->read_all_submitted_instance($query);
    }

    public function has_complete_instances() : bool {
        return !empty($this->get_all_submitted_instance());
    }

    public function is_readonly() : bool {
        return !empty($this->get_all_instance());
    }

    private function read_all_submitted_instance(PDOStatement $query) : array
    {
        $data = $query->fetchAll();
        $submitted_instance = [];

        foreach ($data as $row) {
            $submitted_instance[] = new Instance($row['form'],$row['user'],
                $row['started'],$row['completed'],$row['id']);
        }
        return $submitted_instance;
    }

    //swap question idx with idx-1 or idx+1 
    //depending if is question_up or not (question going down)
    public function question_move_up_down(int $idx, int $question_up): void{
        $question_original = $this->get_question_by_idx($idx);
        $idx_to_swap = $question_up ? $idx-1 : $idx+1;
        $question_to_swap = $this->get_question_by_idx($idx_to_swap);
        //puts question_original idx in hold, in negative idx, temporarily
        //this is to avoid having 2x the same idx (unicity constraint)
        self::execute("UPDATE questions SET idx=:idx WHERE id=:id ",
                        ["idx"=>-$idx, "id"=>$question_original->get_id()]);
        //assigns idx to question_to_swap
        self::execute("UPDATE questions SET idx=:idx WHERE id=:id ",
                        ["idx"=>$idx, "id"=>$question_to_swap->get_id()]);
        //assigns idx_to_swap to question_original
        self::execute("UPDATE questions SET idx=:idx WHERE id=:id ",
                        ["idx"=>$idx_to_swap, "id"=>$question_original->get_id()]);
    }

    public function question_move_to_new_idx(int $original_idx, int $new_idx): void{
        //exemple
        //  je deplace question de idx 10 à 15 :
        //      je mets idx à -10 de celle a deplacer, je deplace les question jusqu'a 15:
        //          11 -> 10 ;
        //          12 -> 11 ;
        //          13 -> 12 ;
        //          14 -> 13 ;
        //          15 -> 14 ;
        //      je mets idx à 15 de celle a deplacer

        //  je deplace question de idx 15 à 10 :
        //      je mets idx à -15 de celle a deplacer, je deplace les question jusqu'a 10:
        //          14 -> 15 ;
        //          13 -> 14 ;
        //          12 -> 13 ;
        //          11 -> 12 ;
        //          10 -> 11 ;
        //      je mets idx à 10 de celle a deplacer

        $step = $original_idx < $new_idx ? +1 : -1;
        $start = $original_idx + $step ;
        $end = $new_idx + $step ; //je dois aller jusqu'a new idx, je dois m'arreter l'etape d'apres

        //puts question_original idx in hold, in negative idx, temporarily
        //this is to avoid having 2x the same idx (unicity constraint)
        $question_original = $this->get_question_by_idx($original_idx);
        $question_original->set_idx(-$original_idx);
        $question_original->persist();


        for ($i = $start; $i != $end; $i = $i + $step) {
            $question_to_swap = $this->get_question_by_idx($i);
            //je fais reculer (-$step), la question ($i) que j'ai trouver en avancant (+$step)
            $question_to_swap->set_idx($i - $step);
            $question_to_swap->persist();
        }

        $question_original->set_idx($new_idx);
        $question_original->persist();
    }

    public function remove_question(int $question_id): void{
        self::execute('DELETE FROM questions WHERE id = :question_id', ['question_id' => $question_id]);
        //after removing an index, reassign consecutive index to questions
        $this->reorder_questions_idx();
    }
    
    //reassign consecutive index to questions
    private function reorder_questions_idx(): void{
        $questions = $this->get_questions();
        $idx = 0;
        foreach($questions as $question){
            ++$idx;
            self::execute("UPDATE questions SET idx=:idx WHERE id=:id ",
                        ["idx"=>$idx, "id"=>$question->get_id()]);
        }
    }

    public function has_question_idx(int $idx) : bool {
        return $idx > 0 && $idx <= sizeof($this->get_questions());
    }

    public function toggle_public(): void{
        if($this->is_public()){
            self::execute("UPDATE forms SET is_public=:not_public WHERE id=:id ",
                        ["not_public"=>0, "id"=>$this->get_id()]);
        }else{
            self::execute("UPDATE forms SET is_public=:public WHERE id=:id ",
                        ["public"=>1, "id"=>$this->get_id()]);
        }
    }


    /* --------------------------------------------------------------------------------
            Color for form
       -------------------------------------------------------------------------------- */
    public static function get_color_by_id(int $form_id) : array|null {
        $query = self::execute("select * from form_colors where form = :form_id", ["form_id" => $form_id]);
        $res = [];
        if ($query->rowCount() >= 0) {
            $data = $query->fetchAll();
            foreach ($data as $row) {
                $res[] = $row['color'];
            }
            return $res;
        }
        return $res;
    }

}
enum Color: string
{
    case Work = 'blue';
    case Education = 'red';
    case Sport = 'yellow';
    case Personal = 'green';
    public function get_hex(): string
    {
        return match($this) {
            Color::Work => '#00BFFF', //DeepSkyBlue
            Color::Education => '#e74c3c', //Crimson
            Color::Sport => '#FFD700', //Gold
            Color::Personal => '#27ae60', //Chartreuse
        };
    }

}
