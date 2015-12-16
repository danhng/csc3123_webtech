<?php
/**
 * Created by PhpStorm.
 * User: dtn
 * Date: 28/11/15
 * Time: 15:46
 */

$fd = fopen('abc.txt', 'a');
fputs($fd, 'abcdbdd');
fclose($fd);

?>