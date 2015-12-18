<?php
/**
 * @author Danh nguyen
 *
 */

/**
 * Class LeftSearchBar prepares the left searching bar.
 */
class LeftSearchBar {

    /**
     * Load all the form inputs at run time.
     *
     * @param Context $context the current context
     */
    public static function loadLeftBar($context)
    {
        $departments = Database::get_all_beans(Database::DEPARTMENT);
        $rlyears = R::getCol( 'SELECT distinct '.Database::PUBLICATION_RLYEAR. ' FROM '.Database::PUBLICATION);
        $authors = R::getCol( 'SELECT distinct '.Database::PUBLICATION_AUTHOR. ' FROM '.Database::PUBLICATION);
        for ($i = 0; $i < count($authors); $i++)
        {
            $authors[$i] = str_replace(' ', '_', $authors[$i]);
        }
Debug::show('all authors loaded: ');
Debug::vdump($authors);
Debug::show("all release years beans:");
Debug::vdump($rlyears);
Debug::show("all author beans:");
Debug::vdump($authors);
        $context->local()->addVal(InterfaceValues::LEFTNAV_DEPARTMENTS, $departments);
        $context->local()->addVal(InterfaceValues::LEFTNAV_RLYEARS, $rlyears);
        $context->local()->addVal(InterfaceValues::PUBLISH_AUTHORS, $authors);
    }
}