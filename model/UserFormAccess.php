<?php
require_once 'model/Form.php';
require_once 'model/User.php';
require_once 'framework/Model.php';
require_once 'framework/View.php';
require_once 'framework/Controller.php';

class UserFormAccess extends Model {

    public function __construct(
        private int $user, 
        private int $form, 
        private string $access_type
        ) {}


    /*
    // deplace dans Form::get_form_acces()
    public static function get_for_user(User $user) : array {
        $query = self::execute("select * from forms where 
                               owner=:userid 
                       or ( is_public=1 and forms.id in (select form from questions))
         order by title", ["userid" => $user->get_id()]);
            $data = $query->fetchAll();
            $forms = [];
            foreach ($data as $row) {
                $forms[] = new Form($row['id'], $row['title']." NEVER USE ", $row['owner'], $row['is_public'], $row['description']);
            }
            return $forms;

    }
    */
    public static function get_sorted_ids_json(User $user, string $filter, array $choice_color) : array {

        if($user->is_admin()){
            $query = self::execute("select * 
            from forms f
            where f.id in (select form 
                        from questions 
                        where title like :filter or
                        description like :filter) or
                    f.id in (select id 
                        from forms 
                        where title like :filter or
                        description like :filter) or
                    f.id in (select id
                        from forms 
                        where owner in (select id
                        from users
                        where full_name like :filter))
            order by f.title", ["filter" => "%$filter%"]);

        }else{
            $query = self::execute("select * 
                                    from forms f
                                    where (f.owner=:userid or 
                                    (f.is_public=1 and f.id in (select form from questions))) and 
                                    ((f.id in (select form 
                                                from questions 
                                                where title like :filter or
                                                description like :filter)) or
                                    (f.id in (select id 
                                                from forms 
                                                where title like :filter or
                                                description like :filter)) or
                                                f.id in (select id
                                                            from forms 
                                                            where owner in (select id
                                                            from users
                                                            where full_name like :filter)))    
                                    order by f.title", ["userid" => $user->get_id(), "filter" => "%$filter%"]);

        }
            $data = $query->fetchAll();
            $ids = [];
            foreach ($data as $row) {
                $id = $row['id'];
                if($choice_color){
                    $color_form = Form::get_color_by_id($id);
                    //on check s'il y a une intersection entre les tableau de couleur choisi et celle du form
                    if(count(array_intersect($choice_color, $color_form)) > 0 ){
                        $ids[] = $id;
                    }
                }else{
                    $ids[] = $id;
                }
            }
            //$jsonData = json_encode($ids);
            return $ids;
    }
    
    /* public static function get_user_access_by_key(int $form_id, int $user_id) : string | false {
        $query = self::execute("select * from user_form_accesses where form=:form and user=:user",
                                ["form" => $form_id, "user" => $user_id]);
        $data = $query->fetch();
        if ($query->rowCount() == 0) {
            return false;
        } else {
            return $data["access_type"];
        } 
    } */
}