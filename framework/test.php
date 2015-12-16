<?php
/**
 * @author Danh nguyen
 *
 */
$fd = fopen('~/debug', 'a');
fputs($fd, 'abcddadad');
fclose($fd);
?>