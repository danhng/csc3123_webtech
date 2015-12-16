<?php
/**
 * Created by Danh Nguyen
 * Date: 27/11/15
 * Time: 15:10
 * Stores a handful of TWIG variables that will be used in the site's templates.
 */

class InterfaceValues {
    #Left search nav bar
    const LEFTNAV = "leftnav";
    const LEFTNAV_DEPARTMENTS = "departments";
    const LEFTNAV_RLYEARS = "releaseyears";

    # Block content: a typical publication placeholder.
    const BLOCKCONTENT_TYPE = "type";
    const BLOCKCONTENT_VALUES = ["1" => "[DOC]", "2" => "[RAW]", "3" => "[APP]", "4" => "[SRC]"];
    const BLOCKCONTENT_TITLE = "title";
    const BLOCKCONTENT_AUTHOR = "author";
    const BLOCKCONTENT_DEPRARTMENT = "department";
    const BLOCKCONTENT_RLYEAR = "rlyear";
    const BLOCKCONTENT_UPLOADDATE = "udate";
    const BLOCKCONTENT_DESCRIPTION = "description";
    const BLOCKCONTENTS = "bcontents";
    const BLOCKCONTENT_DEF = "0";
    const BLOCKCONTENT_PAGE = 'bcontent-page';

    const SEARCH_HASH = 'searchhash';

    #Pagination
    const PAGNIATION = "pagi";
    const PAGES_COUNT = "pagescount";
    const CURRENT_PAGE = "currentpage";

    const VALID_SEARCH_PARAMS = array(
        InterfaceValues::BLOCKCONTENT_TITLE, InterfaceValues::BLOCKCONTENT_AUTHOR,
        InterfaceValues::BLOCKCONTENT_DEPRARTMENT, InterfaceValues::BLOCKCONTENT_RLYEAR,
        InterfaceValues::BLOCKCONTENT_UPLOADDATE, InterfaceValues::BLOCKCONTENT_TYPE);

    #@@todo
    const BLOCKS_PER_PAGE = 3;

}