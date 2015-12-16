<?php
/**
 * @author Danh Nguyen
 * Stores configuration and threshold values for db interaction
 */

/**
 * Class Database stores database schema data.
 */
class Database {
    #todo
    const MAXCHARS = 100; // maximum characters in the brief description of a block content

    const PUBLICATION = 'publication';
    const PUBLICATION_TITLE = 'title';
    const PUBLICATION_TYPE = 'type';
    const PUBLICATION_AUTHOR = 'author';
    const PUBLICATION_DEPARTMENT = 'department';
    const PUBLICATION_DESCRIPTION = 'description';
    const PUBLICATION_CONTENT = 'content';
    const PUBLICATION_RLYEAR = 'rlyear';
    const PUBLICATION_UDATE = 'udate';

    const SEARCH = 'search';

    const DEPARTMENT = "department";
    const DEPARTMENT_ID = "id";
    const DEPARTMENT_NAME = "name";

    const AND_HOLDER = '/and/';
    const AND_HOLDER_COUNT = 5;

    public static function get_all_beans($bean) {
        $beans = R::findAll($bean);
Debug::show("beans: ");
Debug::vdump($beans);
        return $beans;
    }

    public static function get_beans_single_param($bean, $param, $value) {
        if (empty($bean) || empty($param)) {
Debug::show("cant find ".$bean.$param.$value);
            return false;
        }
        $beans = R::findAll($bean, "where ".$param." = ?", array($value));
Debug::show("Finding beans ". $bean." for : ".$param.' = '.$value);
Debug::vdump($beans);
        return $beans;
    }
}