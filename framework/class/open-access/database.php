<?php
/**
 * @author Danh Nguyen
 * Stores configuration and threshold values for db interaction
 */

/**
 * Class Database stores database schema data.
 */
class Database
{

    // publication bean
    const PUBLICATION = 'publication';
    const PUBLICATION_ID = "id";
    const PUBLICATION_TITLE = 'title';
    const PUBLICATION_TYPE = 'type';
    const PUBLICATION_AUTHOR = 'author';
    const PUBLICATION_DEPARTMENT = 'department';
    const PUBLICATION_DESCRIPTION = 'description';
    const PUBLICATION_CONTENT = 'content';
    const PUBLICATION_RLYEAR = 'rlyear';
    const PUBLICATION_UDATE = 'udate';
    const PUBLICATION_POSTBY = 'postby';

    // search table that stores hashed version of searches based on parameters
    const SEARCH = 'search';

    // department bean
    const DEPARTMENT = "department";
    const DEPARTMENT_ID = "id";
    const DEPARTMENT_NAME = "name";

    // holder for and operator queries
    const AND_HOLDER = '/and/';
    const AND_HOLDER_COUNT = 5;

    /**
     * get all beans from a bean by simply invoking R::findAll with some debugging messages
     *
     * @param $bean string the bean type
     * @return array all beans for this bean type
     */
    public static function get_all_beans($bean)
    {
        $beans = R::findAll($bean);
Debug::show("beans: ");
Debug::vdump($beans);
        return $beans;
    }

    /**
     * get bean or beans based on a single parameters with some debugging messages.
     *
     * @param $bean
     * @param $param
     * @param $value
     * @param bool|false $findOne
     * @return array|bool|null|\RedBeanPHP\OODBBean
     */
    public static function get_beans_single_param($bean, $param, $value, $findOne = false)
    {
        // no bean is found
        if (empty($bean) || empty($param))
        {
Debug::show("cant find ".$bean.$param.$value);
            return false;
        }
        $beans = null;
        if ($findOne)
        {
            $beans = R::findOne($bean, "where " . $param . " = ?", array($value));
        }
        else
        {
            $beans = R::findAll($bean, "where " . $param . " = ?", array($value));
        }
Debug::show("Finding beans ". $bean." for : ".$param.' = '.$value);
Debug::vdump($beans);
        return $beans;
    }
}