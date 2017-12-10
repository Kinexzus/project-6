<?php
/**
 * Class FileSystem - Класс для работы с файлововй системой облачного хранилища и правами доступа к файлам
 */
class FileSystem
{
    private $cloud_path;    //путь к файловой системе облака
    private $rights_path;   //путь к файлу, содержащему права доступа к файлам


    function __construct($__cloud_path, $__rights_path)
    {
        //Проверка и иницализация пути к файловой системе облака
        if (!(is_writable($__cloud_path) && is_readable($__cloud_path)))
            throw new Exception("Incorrect path to the file system: $__cloud_path");
        $this->cloud_path = $__cloud_path;
        ////////////////////////////////////
        ////___ПРОВЕРКА НА ВСЕ ДЕРЕВО___////
        ////////////////////////////////////


        //Проверка и иницализация пути к файлу прав доступа
        if (!(is_writable($__rights_path) && is_readable($__rights_path)))
            throw new Exception("Incorrect path to the access rights file : $__rights_path");
        $this->rights_path = $__rights_path;


        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        //!!!!___СИНХРОНИЗАЦИЯ___!!!!!!
        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    }


    /**
     * Метод конвертирует представление пути к файлу в файловой системе облака
     * в представление пути к файлу в файловой системе сервера
     * @param $__clpath
     * @return string
     */
    function cl2fs($__clpath)
    {
        $__clpath = str_replace('\\', '/', $__clpath);
        return $this->cloud_path . $__clpath;
    }


    /**
     * Метод конвертирует представление пути к файлу в файловой системе сервера
     * в представление пути к файлу в файловой системе облака
     * @param $__fspath
     * @return string
     */
    function fs2cl($__fspath)
    {
        $__fspath = str_replace('\\', '/', $__fspath);
        return str_replace($this->cloud_path, '', $__fspath);
    }


    /**
     * Метод возвращает тип прав пользователя на файл.
     * @param string $__clpath - путь к файлу внутри файловой системы облака
     * @param string $__user - логин пользователя
     * @return string
     */
    function getRight($__clpath, $__user)
    {
        $file = file($this->rights_path);
        foreach ($file as $str) {
            $rights = explode('::', $str);
            if ($rights[0] == $__clpath) {
                if ($rights[1] == $__user)
                    return 'rw';            //пользователь имеет право на чтение и запись

                $users = explode(',', $rights[2]);
                foreach ($users as $user)
                    if ($user == $__user)
                        return 'r';         //пользователь имеет право на чтение
            }
        }

        return '';                          //пользователь не имеет никаких прав на файл
    }


    /**
     * Метод возвращает права на файл.
     * @param string $__clpath - путь к файлу внутри файловой системы облака
     * @return array
     */
    function getRights($__clpath)
    {
        $rights = array();

        $file = file($this->rights_path);
        foreach ($file as $str) {
            $rights_tmp = explode('::', $str);
            $rights['clpath'] = $rights_tmp[0];
            $rights['owner'] = $rights_tmp[1];
            $rights['readers'] = explode(',', $rights_tmp[2]);
        }

        return $rights;
    }

    /**
     * Метод лишает пользователей прав на файл. Игнорирует владельца файла.
     * @param string $__clpath - путь к файлу в файловой системе облака
     * @param array $__users - массив логинов пользователей
     * @return bool
     */
    function delRight($__clpath, $__users)
    {
        //////////////////////////////////////////////////////////
        ////___ИСКЛЮЧЕНИЕ ДЛЯ НЕСТРОКОВЫХ ЭЛЕМЕНТОВ МАССИВА___////
        //////////////////////////////////////////////////////////

        $text = file($this->rights_path);
        if ($text) {
            foreach ($text as $str_key => $str) {
                $rights = explode('::', $str);
                if ($rights[0] == $__clpath) {
                    $readers = explode(',', $rights[2]);

                    foreach ($readers as $reader_key => $reader)
                        foreach ($__users as $user_key => $user)
                            if ($reader == $user)
                                unset($readers[$reader_key]);

                    $rights[2] = implode(',', $readers);
                }

                $text[$str_key] = implode('::', $rights);
            }

            $fp = fopen($this->rights_path, 'w');
            if ($fp)
                if (fputs($fp, $text))
                    if (fclose($fp))
                        return true;
        }

        return false;
    }

    /**
     * Метод удаляет все права на файл.
     * @param string $__clpath - путь к файлу в файловой системе облака
     * @return bool
     */
    function delFileRights($__clpath)
    {
        $text = file($this->rights_path);
        if($text)
        {
            $new_text = '';
            foreach ($text as $str)
                $new_text .= preg_replace("@$this->rights_path::.*\n@", "\n", $str);

            $fp = fopen($this->rights_path, 'w');
            if ($fp)
                if (fputs($fp, $new_text))
                    if (fclose($fp))
                        return true;
        }

        return false;
    }

    /**
     * Метод лишает пользователя прав на все файлы
     * и удаляет все права на файлы, владельцем которых он является.
     * @param $__user
     * @return bool
     */
    function delUserRights($__user)
    {
        //////////////////////
        ////___ДОДЕЛАТЬ___////
        //////////////////////

        return true;
    }


    /**
     * Метод возвращает основную информацию о фйле
     * @param string $__clpath - путь к файлу в файловой системе облака
     * @return array
     */
    function getInfo($__clpath)
    {
        //////////////////////
        ////___ДОДЕЛАТЬ___////
        //////////////////////

        $fspath = $this->cl2fs($__clpath);

        $info = [];                                                 //данные о файле:
        if(is_dir($fspath))
        {
            $fspath_arr = glob($fspath . '/*');

            foreach($fspath_arr as $fspath)
                $fspath = $this->fs2cl($fspath);
                $info[] = $this->getInfo($fspath);
        }
        if(is_file($fspath))
        {
            $info['name'] = basename($fspath);                      //имя файла
            $info['type'] = filetype($fspath);                      //тип файла
            $info['size'] = (is_dir($fspath))                       //размер файла
                ?dirsize($fspath)
                :filesize($fspath);
            $info['chdate'] = filemtime($fspath);                   //время последней модификации
            $info['access_rights'] = $this->getRights($__clpath);   //права дотупа
        }

        return $info;
    }







//    function removeFile($__clpath)
//    {
//        ///////////////////////////////////////////////////////////////
//        ////___ДОБАВИТЬ РЕКУРСИВНОЕ УДАЛЕНИЕ ПРАВ ДЛЯ ДИРЕКТОРИЙ___////
//        ///////////////////////////////////////////////////////////////
//
//        $fspath = $this->cl2fs($__clpath);
//
//        $remove = false;
//        if(is_dir($fspath))
//        {
//            $files_arr = glob($fspath."/*");
//            foreach($files_arr as $file)
//                $remove &= remove($file);
//
//            $remove &= rmdir($fspath);
//        }
//        else
//            $remove = unlink($fspath);
//
//        if($remove)
//            $remove = $this->delFileRights($__clpath);
//
//        return $remove;
//    }



}

/**
 * Функция вычисления размера директори
 * @param string $fspath
 * @return int
 */
function dirsize($fspath) {
    $totalsize=0;
    if ($dirstream = @opendir($fspath)) {
        while (false !== ($filename = readdir($dirstream))) {
            if ($filename!="." && $filename!="..")
            {
                if (is_file($fspath."/".$filename))
                    $totalsize+=filesize($fspath."/".$filename);

                if (is_dir($fspath."/".$filename))
                    $totalsize+=dirsize($fspath."/".$filename);
            }
        }
    }
    closedir($dirstream);
    return $totalsize;
}