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

    # Block content: a typical publication placeholder.
    const BLOCKCONTENT_TYPE = "type";
    const BLOCKCONTENT_VALUES = ["doc" => "[DOC]", "raw" => "[RAW]", "app" => "[APP]", "src" => "[SRC]"];
    const BLOCKCONTENT_TITLE = "title";
    const BLOCKCONTENT_AUTHOR = "author";
    const BLOCKCONTENT_DEPRARTMENT = "department";
    const BLOCKCONTENT_RLYEAR = "rlyear";
    const BLOCKCONTENT_UPLOADDATE = "udate";
    const BLOCKCONTENT_DESCRIPTION = "description";
    const BLOCKCONTENTS = "bcontents";
    const BLOCKCONTENT_DEF = '0';
    const BLOCKCONTENT_PAGE = 'bcontent-page';

    const SEARCH_HASH = 'searchhash';

    #Pagination
    const PAGNIATION = "pagi";
    const PAGES_COUNT = "pagescount";
    const CURRENT_PAGE = "currentpage";

    const VALID_SEARCH_PARAMS = [InterfaceValues::BLOCKCONTENT_TYPE,
        InterfaceValues::BLOCKCONTENT_TITLE, InterfaceValues::BLOCKCONTENT_AUTHOR,
        InterfaceValues::BLOCKCONTENT_DEPRARTMENT, InterfaceValues::BLOCKCONTENT_RLYEAR,
        InterfaceValues::BLOCKCONTENT_UPLOADDATE];

    #@@todo
    const BLOCKS_PER_PAGE = 3;

}