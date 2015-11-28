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
}