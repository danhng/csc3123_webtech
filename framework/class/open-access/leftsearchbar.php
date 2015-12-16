<?php
/**
 * @author Danh nguyen
 *
 */

/**
 * Class LeftNavBar prepares the left searching bar.
 */
class LeftSearchBar {


    public static function loadLeftBar(Context $context) {
        $departments = Database::get_all_beans(Database::DEPARTMENT);
        $rlyears = R::getCol( 'SELECT distinct '.Database::PUBLICATION_RLYEAR. ' FROM '.Database::PUBLICATION);
Debug::show("all release years beans:");
Debug::vdump($rlyears);
        $context->local()->addVal(InterfaceValues::LEFTNAV_DEPARTMENTS, $departments);
        $context->local()->addVal(InterfaceValues::LEFTNAV_RLYEARS, $rlyears);
    }
}