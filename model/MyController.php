<?php

require_once 'model/User.php';
abstract class MyController extends Controller
{
    public function dev_error(string $err): void{
        //echo "<br> dev_error : ".$err." <br>";
        if( Configuration::is_dev() ){
            throw new Exception($err);
        }else{
            $this->redirect('');
        }
    }

    public function dev_error_for_service(string $err): void{
        if( Configuration::is_dev() ){
            http_response_code(400); 
            echo json_encode($err);
        }else{
            http_response_code(400); 
            echo json_encode('bad request');
        }
    }

    public function guest_logged(): bool {
        return isset($_SESSION['user']) && $_SESSION['user']->is_guest();
    }

    public function get_user_or_http_error():object|false{
        $user = $this->get_user_or_false();
        //$user = false; // for testing error
        if(!$user){
            http_response_code(401); 
            echo json_encode("User not authenticated");
        }
        return $user;
    }
}