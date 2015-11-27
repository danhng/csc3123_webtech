<?php
/**
 * Created by Danh Nguyen
 * Date: 27/11/15
 * Time: 15:10
 * Stores a handful of TWIG variables that will be used in the site's templates.
 */

class TwigValues {
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

    #Pagniation
    const PAGNIATION = "pagi";
    const PAGES_COUNT = "pages-count";
    const CURRENT_PAGE = "current-page";

}