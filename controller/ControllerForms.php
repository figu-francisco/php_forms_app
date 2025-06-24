<?php
require_once 'model/UserFormAccess.php';
require_once 'model/User.php';
require_once 'framework/View.php';
require_once 'framework/Controller.php';
require_once 'model/MyController.php';
require_once 'utils/Base64Helper.php';
class ControllerForms extends MyController{
    public function index() : void {
        $user = $this->get_user_or_redirect();
        $encoded_search = "";
        $text_search = "";
        $color_search = [];

        if(isset($_GET['param1'])){
            if(!Base64Helper::isBase64($_GET['param1'])){
                $this->dev_error("FORMS: wrong get param1 (not Base64)");
            }
            $encoded_search = $_GET['param1'];

            $decode_search = Base64Helper::url_safe_decode($encoded_search);
            if(isset($decode_search["noscript"])){
                //Si j'ai un noscript, c'est que je reviens d'un PRG ou que je reviens d'une autre vue où j'ai propagé
                // la recherche (sans script). Je mets à jour $text_search et $color_search pour trié directement dans
                // la vue. Si je n'ai pas de noscript, je laisse les search vides pour charger tous les forms
                // (pour l'utilisateur actuel) et le script va s'occuper de hide les mauvais grâce à $encoded_search.

                //Si je mets toujours à jour les search, quand je reviens d'une autre vue après un filtrage via script,
                // je ne chargerais que le parti des forms qui sont ok avec les search et le script ne pourra pas créer
                // de nouveau form si je retire les filtres, car il se contente de cacher/afficher ceux déjà existant
                // sur la vue.

                if($decode_search["noscript"] === "true"){
                    if(!isset($decode_search["texte"])){
                        $this->dev_error("FORMS: wrong get param1 (no texte)");
                    }
                    $text_search = $decode_search["texte"];

                    if(!isset($decode_search["color"])){
                        $this->dev_error("FORMS: wrong get param1 (no color)");
                    }
                    $color_search = $decode_search["color"];

                    if(!is_array($color_search)){
                        $this->dev_error("FORMS: wrong get param1 (not array color)");
                    }
                }
            }
        }

        if (!empty($_POST)) {

            if(!isset($_POST['input_search'])){
                $this->dev_error("FORMS: missing post param input_search");
            }
            $text_search = $_POST['input_search'];

            if(isset($_POST['search_color'])){
                if(!is_array($_POST['search_color'])){
                    $this->dev_error("FORMS: wrong post param search_color");
                }
                $color_search = $_POST['search_color'];
            }
            // Remarque : chaque fois que je clique sur la recherche, un post est généré (même s'il peut être vide).
            // Je dois effectuer une redirection dans tous les cas. Cependant, s'il n'y a aucun critère de recherche,
            // je redirige simplement vers l'index de base afin de ne pas "polluer" l'URL.

            $encoded_search = MyTools::merge_search_and_encode_no_script($text_search,$color_search);
            if($text_search !== "" or !empty($color_search) ){ //on ne fait un redirect que si
                $this->redirect("forms",
                                "index",
                                $encoded_search);
            }else{
                $this->redirect("forms","index");
            }
        }


        (new View("forms"))->show(["user" => $user,
                                    "text_search" => $text_search,
                                    "color_search" => $color_search,
                                    "encoded_search" => $encoded_search]);
    }

}