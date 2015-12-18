<?php
/**
 * @author Danh Nguyen <d.t.nguyen@newcastle.ac.uk>
 *
 */

/**
 * Class InterfaceValues stores a handful of TWIG variables that will be used in the site's templates.
 */
class InterfaceValues {
    //Left search bar
    const LEFTNAV = "leftnav";
    const LEFTNAV_DEPARTMENTS = "departments";
    const LEFTNAV_RLYEARS = "releaseyears";

    //page
    const PAGE_TITLE = "page_title";
    const CATEGORIES = ["doc" => "1", "raw" => "2", "app" => "3", "src" => "4", "home" => "0"];
    const CATEGORY_TYPE = 'cattype';
    const CATEGORY_VAL = 'catval';

    // Block content: a typical publication placeholder.
    const BLOCKCONTENT_TYPE = "type";
    const BLOCKCONTENT_VALUES = ["1" => "Document", "2" => "Raw data", "3" => "Application", "4" => "Source code"];
    const BLOCKCONTENT_TITLE = "title";
    const BLOCKCONTENT_AUTHOR = "author";
    const BLOCKCONTENT_DEPRARTMENT = "department";
    const BLOCKCONTENT_RLYEAR = "rlyear";
    const BLOCKCONTENT_UPLOADDATE = "udate";
    const BLOCKCONTENT_DESCRIPTION = "description";
    const BLOCKCONTENTS = "bcontents";
    const BLOCKCONTENT = "bcontent";
    const BLOCKCONTENT_DEF = "0";
    const BLOCKCONTENT_PAGE = 'bcontent-page';

    // content_id
    const CONTENT_ID = 'id';

    //authors for publishing
    const PUBLISH_AUTHORS = 'authors';
    const PUBLISH_BUTTON_NAME = 'publishaction';
    const PUBLISH_DO = 'dopublish';
    const PUBLISH_DO_OK = 'dopublish_ok';
    const EDIT_BUTTON_NAME = 'editaction';
    const EDIT_DO = 'doedit';
    const EDIT_DO_OK = 'doedit_ok';

    // hashed search
    const SEARCH_HASH = 'searchhash';

    //pagination
    const PAGINATION = "pagination";
    const PAGES_COUNT = "pagescount";
    const CURRENT_PAGE = "currentpage";

    // valid params for search bar
    const VALID_SEARCH_PARAMS = array(
        InterfaceValues::BLOCKCONTENT_TITLE, InterfaceValues::BLOCKCONTENT_AUTHOR,
        InterfaceValues::BLOCKCONTENT_DEPRARTMENT, InterfaceValues::BLOCKCONTENT_RLYEAR,
        InterfaceValues::BLOCKCONTENT_UPLOADDATE, InterfaceValues::BLOCKCONTENT_TYPE);

    // some configuration information
    // todo need furthur thought
    const BLOCKS_PER_PAGE = 3; // how many content blocks per page
    const FILE_LIMIT_B = 50000000; // maximum upload file size
    const BRIEF_DESCRIPTION_LIMIT = 500; // word limit for description shown at search results

}