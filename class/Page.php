<?php

    class Page
    {
        public $json ;
        public $meta ;
        public $title ;

        public $menu;

        public $content ;
        protected $currentVersion = "0.1" ;

        public $footer;
        public $css = ["mandatory"] ;
        public $beforePath = '' ;
        public $js = [] ;

        public $pageId = 'index' ;
        public $pageConfig ;

        public $searchPath = 'pages' ;

        public function __construct() {

            $this->json = new Json() ;
            $this->pageAccess() ;

        }

        public function menu(){
            $this->menu = file_get_contents("html/structures/menu.html") ;
            require_once "php/structures/menu.php" ;
            $this->css[] = "structures/menu" ;
        }


        public function pageAccess(){

            $file = 'pages' ;


            $access = $this->json->getFile($file) ;

            if (isset($access->{$this->pageId})){

                $this->pageConfig =$access->{$this->pageId} ;
                $this->buildPageHtml() ;
            } else {
                $this->content ="Cette page n'existe pas" ;
            }

            if (!empty( $this->pageConfig->title)){
                $this->title = $this->pageConfig->title ;
            }


        }

        function buildPageHtml(){

            $html = file_get_contents("html/{$this->searchPath}/{$this->pageId}.html") ;

            if (file_exists("php/{$this->searchPath}/{$this->pageId}.php")){

                require_once "php/{$this->searchPath}/{$this->pageId}.php" ;
            }

            if (file_exists("css/{$this->searchPath}/{$this->pageId}.css")){
                $this->css[] = "{$this->searchPath}/{$this->pageId}" ;
            }

            if (file_exists("js/{$this->searchPath}/{$this->pageId}.js")){
                $this->js[] = "{$this->searchPath}/{$this->pageId}" ;
            }

            $this->content = $html ;
        }


        public function output($onlyBody = false):string{


            $this->menu();
            $css = "" ;

            $this->css = array_unique($this->css) ;
            foreach ($this->css AS $stylesheet){
                $css .= "<link rel='stylesheet' href='{$this->beforePath}css/{$stylesheet}.css?{$this->currentVersion}'>" ;
            }

            $this->js = array_unique($this->js) ;

            foreach ($this->js AS $script){
                $css .= "<script src='{$this->beforePath}js/{$script}.js?{$this->currentVersion}'></script>" ;
            }


            $bodyClass = '' ;

            if (!empty($_GET['framed'])){
                $bodyClass = 'inIframe' ;
            }

            if ($onlyBody == false){
                $this->displayNotification() ;

                $output = "
<!doctype html>
<html lang='fr' class='{$bodyClass}'>
        <head>
          <title>{$this->title} - Katchacha</title>
          <meta name='viewport' content='width=device-width, initial-scale=1' />
          <meta charset='utf-8'>
          {$this->meta}
          {$css}
        </head>
    <body>
        {$this->menu}
        {$this->content}
        {$this->footer}
    </body>
</html>
" ;
            }else {
                $output = "<div class='bodyContent'>{$this->content}</div>" ;
            }

            return str_replace("\n\r", '', $output );

        }

        function newNotification($notificationId = 'Unknown', $isError = true){
            $messages = [
                "Unknown" =>["Erreur"],
                "goodSolution" =>["C'était vraiment facile après","Beau gosse", "Bien joué ♡"],
                "noSolution" =>["Merci d'indiquer une solution, une case vide ça marche pas, looser"],
                "badSolution" =>["C'est pas ça, pourtant vraiment c'est facile", "Aucune chance que ce soit ça la solution srx", "C'est pas bon, pourtant même Fluff aurait trouvé ", "Looser", "Attend, tu étais sur de toi ?"]
            ] ;

            if ($isError == true) {
                $_SESSION['errors'][] = $messages[$notificationId][array_rand($messages[$notificationId])];
            } else {
                $_SESSION['notifications'][] = $messages[$notificationId][array_rand($messages[$notificationId])];
            }

        }
        function displayNotification(){
            if (!empty($_SESSION['errors']) || !empty($_SESSION['notifications'])){
                $errorList = "<ul class='notifications'>" ;
                if (!empty($_SESSION['errors'])) {
                    foreach ($_SESSION['errors'] as $error) {
                        $errorList .= "<li class='error'>{$error}</li>";
                    }
                }

                if (!empty($_SESSION['notifications'])) {
                    foreach ($_SESSION['notifications'] as $error) {
                        $errorList .= "<li class='notification'>{$error}</li>";
                    }
                }
                $errorList .="</ul>" ;
                $this->menu .= $errorList ;
                $_SESSION['errors'] = NULL ; // supprime les erreurs une fois affichées
                $_SESSION['notifications'] = NULL ;
            }
        }


        function stringToCleanUrl(string $string):string{
            $caracteres = array(
                'Á' => 'a', 'Â' => 'a', 'Ä' => 'a', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', '@' => 'a',
                'È' => 'e', 'É' => 'e', 'Ê' => 'e', 'Ë' => 'e', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', '€' => 'e',
                'Ì' => 'i', 'Í' => 'i', 'Î' => 'i', 'Ï' => 'i', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
                'Ò' => 'o', 'Ó' => 'o', 'Ô' => 'o', 'Ö' => 'o', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o',
                'Ù' => 'u', 'Ú' => 'u', 'Û' => 'u', 'Ü' => 'u', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'µ' => 'u',
                'Œ' => 'oe', 'œ' => 'oe', 'ç' => 'c',
                '$' => 's');

            $url = strtr($string, $caracteres);
            $url = preg_replace('#[^A-Za-z0-9]+#', '-', $url);
            $url = trim($url, '-');

            return strtolower($url);
        }


        function stringToColor(string $string):string{
            $alreadyColors = ['Blanc' => 'e1e6e2', 'Gris' => '6e6e6e','Noir' => '292929','Vert' => '8bed61'] ;
            if (!empty($alreadyColors[$string])){
                $code = $alreadyColors[$string] ;
            } else {
                $code = dechex(crc32($string));
                $code = substr($code, 1, 6);
            }
            return '#'.$code;
        }

    }
