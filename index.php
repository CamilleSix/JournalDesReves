<?php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    session_start() ;


    require_once "class/Page.php";
    require_once "class/Json.php";
    require_once "fonctions/svgs.php";


        $page = new Page();


    if (!empty($_GET['form'])){

        header("Location: ../../");
    } else {
        echo $page->output() ;
    }
