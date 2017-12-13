<?php
require_once 'Cloud.php';

$cloud = new Cloud('../FileSystem', '../rights', '../users', './project-6.php');

if(!isset($_REQUEST['do'])){
    $cloud->printLogin();
    exit;
}


switch ($_REQUEST['do']){
    case 'registration':{
        $cloud->printRegistration();
        exit;
    }
    case 'authorization':{
        $cloud->printLogin();
        exit;
    }
    case 'register':{
        $login = $_REQUEST['login'];
        $password = $_REQUEST['password'];
        $mail = $_REQUEST['mail'];
        $cloud->register($login, $password, $mail);
        exit;
    }
    case 'login':{
        $login = $_REQUEST['login'];
        $password = $_REQUEST['password'];
        $cloud->login($login, $password);
        exit;
    }
    case 'logout':{
        $cloud->logout();
        exit;
    }
    case 'openDir':{
        $clpath = $_REQUEST['dir_path'];
        $cloud->openDir($clpath);
        exit;
    }
    case 'download':{
        $clpath = $_REQUEST['file_path'];
        $cloud->downloadFile($clpath);
        exit;
    }
    case 'upload':{
        $file_name = $_FILES['FILE']['name'];
        $tmp_name = $_FILES['FILE']['tmp_name'];
        $clpath = $_REQUEST['path'];

        $cloud->uploadFile($clpath, $file_name, $tmp_name);
        exit;
    }
    case 'makeDir':{
        $dir_name = $_REQUEST['dir_name'];
        $clpath = $clpath = $_REQUEST['path'];

        $cloud->makeDir($clpath, $dir_name);
        exit;
    }
    case 'delete': {
        $clpath = $_REQUEST['file_path'];

        $cloud->deleteFile($clpath);
        exit;
    }
    case 'changeRightsMenu':{
        $clpath = $_REQUEST['file_path'];

        $cloud->printRightsMenu($clpath);
        exit;
    }
    case 'changeRights':{
        $clpath = $_REQUEST['file_path'];

        $users = array();
        for($i = 1; isset($_REQUEST["Param$i"]); ++$i)
            $users[] = $_REQUEST["Param$i"];

        $cloud->changeRights($clpath, $users);
        exit;
    }
    default: {
        echo "УПСссс....";
        exit;
    }
}