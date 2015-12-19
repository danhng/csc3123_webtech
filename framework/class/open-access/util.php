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

    /**
     * Shorten a string to a defined limit
     *
     * Used mostly for shortening descriptions shown in search pages
     *
     * @param $string string the string to be shorted
     * @param $limit int the limit of the output string that contains significant characters
     * @return string the output string
     */
    public static function shorten($string, $limit)
    {
    $len = strlen($string);
        if ($len <= $limit)
        {
            return $string;
        }
    return substr($string, 0, $limit).'[TO BE CONTINUED]';
}

    /**
     * upload a file to the directory
     *
     * see: http://www.w3schools.com/php/php_file_upload.asp
     *
     * @return int|string error codes (int) or the file name if the file is uploaded
     */
public static function upload()
{
    $target_dir = "assets/uploads/";
    $target_file = $target_dir . basename($_FILES["file-id"]["name"]);
    $uploadOk = 1;
    $file_type = pathinfo($target_file, PATHINFO_EXTENSION);

    // Check if file already exists
    if (file_exists($target_file))
    {
Debug::show('file '.$target_file.'exists');
        return 1;
    }
    // Check file size
    if ($_FILES["file-id"]["size"] > InterfaceValues::FILE_LIMIT_B)
    {
Debug::show('file '.$target_file.'too large');
        return 2;
    }
    // Allow certain file formats
    if ($file_type != "txt" && $file_type != "pdf" && $file_type != "doc" && $file_type != "docx"
        && $file_type != "pdf" && $file_type != "xls" && $file_type != "xlsx" && $file_type != "odt" && $file_type != "rtf"
        && $file_type != "zip" && $file_type != "tar.gz")
    {
Debug::show('file '.$target_file.' format not supported');
        return 3;
    }
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0)
    {
Debug::show('uploading file '.$target_file.' fails');
        return 4;
    // if everything is ok, try to upload file
    }
    else
    {
        // move uploaded
        $r = move_uploaded_file($_FILES["file-id"]["tmp_name"], $target_file);
Debug::vdump($r);
        if ($r)
        {
            return basename($_FILES["file-id"]["name"]);
        }
        else
        {
Debug::show('file'.$target_file.' upload failed.');
            return 5;
        }
    }
}
}