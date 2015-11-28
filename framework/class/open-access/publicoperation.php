<?php

/**
 * @author Danh nguyen
 */

/**
 * Class SearchPublication to handle search queries.
 * @@ Seems like Search and search.php can't be used (500). Have they been used elsewhere?
 */
class PublicOperation extends Siteaction
{
    /**
     * /search handler.
     * @param $context Context the context object for the site
     * @return string the template
     */
    public function searchcontent($context) {
        $query = array();
        foreach (InterfaceValues::VALID_SEARCH_PARAMS as $param) {
            if ($context->hasgetpar($param))
                $query[$param] = $context->getpar($param, false);
        }
        #@@ add template variables to the local object
        $full_result = $this->search_publications($query);

        $context->local()->addVal(InterfaceValues::BLOCKCONTENTS,
            $this->get_blocks($full_result, $context->mustgetpar(InterfaceValues::CURRENT_PAGE, 0)));
        $context->local()->addVal(InterfaceValues::CURRENT_PAGE, 1);
        $context->local()->addVal(InterfaceValues::PAGES_COUNT, ceil(sizeof($full_result) / InterfaceValues::BLOCKS_PER_PAGE));
        $context->local()->addval(InterfaceValues::LEFTNAV, true);
        $context->local()->addval(InterfaceValues::PAGNIATION, true);

        return 'search.twig';
    }

    /**
     * @param $blocks array all blocks
     * @param $page_number mixed the page number
     * @return array the blocks needed
     */
    private function get_blocks($blocks, $page_number) {
        if (!is_numeric($page_number) || $page_number < 0)
            (new Web)->internal('Something went wrong. We are working on it. '.$page_number);
        if ($page_number === 0) {
            $page_number = 1;
        }
        return array_slice($blocks, ($page_number - 1) * InterfaceValues::BLOCKS_PER_PAGE, InterfaceValues::BLOCKS_PER_PAGE);
    }

    /**
     * Inner search of
     * @param $query array
     * @return array
     */
    private function search_publications($query) {
        //todo verify query (javascript?)
        $filtered_query = $this->filter_query($query);
        $publications = R::find(Database::PUBLICATION, $this->build_search_query($filtered_query), array_values($filtered_query));
        return $this->make_content($publications);
    }

    private function build_search_query($query) {
        $sql = ' where ';
        foreach (array_keys($query) as $param) {
            if (!strcmp($sql, '')) {
                $sql .= ' and ';
            }
            #@@ use string searching for title and author
            if (strcmp($param, InterfaceValues::BLOCKCONTENT_TITLE) === 0 || strcmp($param, InterfaceValues::BLOCKCONTENT_AUTHOR) === 0) {
                $sql .= ($param.' regexp ? ');
            }
            #@@ use equality searching for the remainder
            else {
                $sql .= ($param.' =? ');
            }
        }
        return $sql;
    }
    /**
     * filter the raw query from the client
     * @param $raw_query array the raw query from context (reqrest)
     * return the filter query in which all trash parameters are ruled out.
     */
    private function filter_query($raw_query) {
        $filtered_query = array();
        foreach ($raw_query as $param => $value) {
            if ($this->filter_parameter($param, $value))
                $filtered_query[$param] = $value;
        }
        return $filtered_query;
    }

    /**
     * Filter a single parameter
     * @param $parameter string the parameter
     * @param $value string the value
     * @return array|bool
     */
    private function filter_parameter($parameter, $value) {
        # param is invalid
        if (!array_search($parameter, InterfaceValues::VALID_SEARCH_PARAMS)) {
            return false;
        }
        $value = trim($value);
        # value is invalid
        if (!$value) {
            return false;
        }
        switch ($parameter) {
            case InterfaceValues::BLOCKCONTENT_TYPE :
            case InterfaceValues::BLOCKCONTENT_DEPRARTMENT:
            case InterfaceValues::BLOCKCONTENT_RLYEAR:
                return !strcmp($value, InterfaceValues::BLOCKCONTENT_DEF);
        }
        return true;
    }

    /**
     * make content for twig rendering
     * @param $publications array publications
     * @return array
     */
    private function make_content($publications) {
        $content = array();
        foreach ($publications as $p) {
            array_push($content, array(Database::PUBLICATION_TITLE => $p->title,
                Database::PUBLICATION_TYPE => $p->type, Database::PUBLICATION_AUTHOR => $p->author,
                Database::PUBLICATION_DEPARTMENT => $p->department, Database::PUBLICATION_CONTENT => $p->content,
                Database::PUBLICATION_RLYEAR => $p->rlyear, Database::PUBLICATION_UDATE => $p->udate));
        }
        Debug::vdump($content);
        return $content;
    }

    public function handle($context)
    {
        $action = $context->action();
        return $this->$action($context); // return the template
    }

}