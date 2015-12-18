<?php

/**
 * @author Danh nguyen
 *
 */

/**
 * Class Util stores some various useful stuff that is used commonly.
 */
class Util
{
    const ERROR_MESSAGE_500 = 'Something went wrong. We are working on it.';

//    public static function get_left_search_bar_params($attribute) {
//        $val = Database::get_all_beans($attribute);
//Debug::show("Departments for left search bar:");
//Debug::vdump($val);
//        return $val;
//    }

public static function shorten($string, $limit) {
    $len = strlen($string);
        if ($len <= $limit) {
            return $string;
        }
    return substr($string, 0, $limit).'[TO BE CONTINUED]';
}

public static function upload()
{
    $target_dir = "assets/uploads/";
    $target_file = $target_dir . basename($_FILES["file-id"]["name"]);
    $uploadOk = 1;
    $fileType = pathinfo($target_file, PATHINFO_EXTENSION);
// Check if image file is a actual image or fake image
//if(isset($_POST["submit"])) {
//$check = filesize($_FILES["fileToUpload"]["tmp_name"]);
//if($check !== false) {
//echo "File is an image - " . $check["mime"] . ".";
//$uploadOk = 1;
//} else {
//    echo "File is not an image.";
//    $uploadOk = 0;
//}
//}
// Check if file already exists
    if (file_exists($target_file)) {
Debug::show('file'.$target_file.'exists');
        return 1;
    }
// Check file size
    if ($_FILES["file-id"]["size"] > InterfaceValues::FILE_LIMIT_B) {
Debug::show('file'.$target_file.'too large');
        return 2;
    }
// Allow certain file formats
    if ($fileType != "txt" && $fileType != "pdf" && $fileType != "doc" && $fileType != "docx"
        && $fileType != "pdf" && $fileType != "xls" && $fileType != "xlsx" && $fileType != "odt" && $fileType != "rtf"
        && $fileType != "zip" && $fileType != "tar.gz"
    ) {
Debug::show('file'.$target_file.' format not supported');
        return 3;
    }
// Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
Debug::show('file'.$target_file.' format not supported');
        return 4;
// if everything is ok, try to upload file
    } else {
        $r = move_uploaded_file($_FILES["file-id"]["tmp_name"], $target_file);
Debug::vdump($r);
        if ($r) {
            return basename($_FILES["file-id"]["name"]);
        } else {
Debug::show('file'.$target_file.' upload failed.');
            return 5;
        }
    }
}

}