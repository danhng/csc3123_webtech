<?php

/**
 * @author Danh Nguyen <d.t.nguyen@newcastle.ac.uk>
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
        $search_id = '';
        $query = array();
        $currentpage = 1;

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST' : {
                foreach (InterfaceValues::VALID_SEARCH_PARAMS as $param) {
                    if ($context->haspostpar($param))
                        $query[$param] = $context->mustpostpar($param, true);
                }
                #@@ make sure query is not empty...
                if ($query) {
                    $full_result = $this->search_publications_query($query);
                    $currentpage = $context->postpar(InterfaceValues::CURRENT_PAGE, 1);
                }
                else {
                    (new Web)->bad(Util::ERROR_MESSAGE_500);
                }
                break;
            }
            case 'GET' : {
                #@@ get search id for this search
                $search_id = $context->rest()[0];
                if ($search_id) {
                    $full_result = $this->search_publications_id($search_id);
                    $currentpage = array_key_exists(1, $context->rest() ) ? $context->rest()[1] : 1;
                }
                else {
                    (new Web)->bad(Util::ERROR_MESSAGE_500);
                }
                break;
            }
            default : {
                (new Web)->bad('Method not supported.');
            }
        }
        #@@ add search hash to twig compatible vars
        $context->local()->addval(InterfaceValues::SEARCH_HASH, $full_result[InterfaceValues::SEARCH_HASH]);
        #@@ add bcontents to twig compatible vars
        $context->local()->addVal(InterfaceValues::BLOCKCONTENTS,
            $this->get_blocks($full_result['content'], $currentpage));
        #@@ add currentpage to twig compatible vars
        $context->local()->addVal(InterfaceValues::CURRENT_PAGE, $currentpage);
        $context->local()->addVal(InterfaceValues::PAGES_COUNT, ceil(sizeof($full_result['content']) / InterfaceValues::BLOCKS_PER_PAGE));
        $context->local()->addval(InterfaceValues::LEFTNAV, true);
        $context->local()->addval(InterfaceValues::PAGNIATION, true);

        return 'search.twig';
    }

    /**
     * Inner publication search using search id
     * @param $search_id string
     * @return array of results (twig compatible)
     */
    private function search_publications_id($search_id) {
        $search_rb = R::findOne(Database::SEARCH, 'hash =?', [$search_id]);
        #@@ oops more than 1 search is found...
        if (!$search_rb) {
            (new Web)->internal(Util::ERROR_MESSAGE_500.var_export($search_rb, true));
        }
        $query = $this->filter_query(array(InterfaceValues::BLOCKCONTENT_TYPE => $search_rb->type,
            InterfaceValues::BLOCKCONTENT_TITLE => $search_rb->title,
            InterfaceValues::BLOCKCONTENT_AUTHOR => $search_rb->author,
            InterfaceValues::BLOCKCONTENT_DEPRARTMENT => $search_rb->department,
            InterfaceValues::BLOCKCONTENT_RLYEAR => $search_rb->rlyear));
        //todo verify query (javascript?)
        $filtered_query = $this->filter_query($query);
        $publications_rb = R::find(Database::PUBLICATION, $this->build_search_query($filtered_query), array_values($filtered_query));
        return $this->make_content($publications_rb, $search_id);
}

    /**
     * Inner publication search using query
     * @param $query array|null
     * @return array of results (twig compatible)
     */
    private function search_publications_query($query) {
        if (!$query) {
            (new Web)->internal(Util::ERROR_MESSAGE_500.var_export($query, true));
        }
        #@@ $query takes precedence over $search_id. However the $search_id is not trashed.
            #@@ make the search id for this query
            $search_id = $this->make_search_id($query);
            #@@ if search id is not already in database? Create and store it.
            $search_rb = R::findOne(Database::SEARCH, ' hash = ? ', [$search_id]);
            if (!$search_rb) {
                $search_rb = R::dispense(Database::SEARCH);
                $search_rb->hash = $search_id;
                $search_rb->type = $this->get_value_param($query, InterfaceValues::BLOCKCONTENT_TYPE);
                $search_rb->title = $this->get_value_param($query, InterfaceValues::BLOCKCONTENT_TITLE);
                $search_rb->author = $this->get_value_param($query, InterfaceValues::BLOCKCONTENT_AUTHOR);
                $search_rb->department = $this->get_value_param($query, InterfaceValues::BLOCKCONTENT_DEPRARTMENT);
                $search_rb->rlyear = $this->get_value_param($query, InterfaceValues::BLOCKCONTENT_RLYEAR);
                #@@ store the search bean back to database
                R::store($search_rb);
            }
            //todo verify query (javascript?)
            $filtered_query = $this->filter_query($query);
            $publications_rb = R::find(Database::PUBLICATION, $this->build_search_query($filtered_query),
                array_values($filtered_query));
            return $this->make_content($publications_rb, $search_id);
    }

    /**
     * build a search query (query part)
     * @param $query array
     * @return string
     */
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
     * @return array the filter query in which all trash parameters are ruled out.
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
     * @return bool
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
     *
     * @param $param
     * @return string the default value for the parameter
     */
    private function def($param)
    {
        switch ($param) {
            case InterfaceValues::BLOCKCONTENT_TYPE :
            case InterfaceValues::BLOCKCONTENT_DEPRARTMENT:
            case InterfaceValues::BLOCKCONTENT_RLYEAR:
                return InterfaceValues::BLOCKCONTENT_DEF;
            default :
                return '';
        }
    }

    /**
     * get a search id for a query
     * the id to bed returned needs to be hashed in a uniform order across searches
     * @param $query array the filtered query
     * @return string the search id that is consistent for searches with identical params.
     */
    private function make_search_id($query) {
        $plain = '';
        foreach (array_values($query) as $value) {
            $plain .= $value;
        }
        $code = hash('sha256', $plain);
        if (!$code) {
            (new Web)->internal(Util::ERROR_MESSAGE_500.$code);
        }
        return $code;
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

    private function get_value_param($query, $param) {
        if (array_key_exists($param, $query)) {
            return $query[$param];
        }
        return $this->def($param);
    }

    /**
     * make content for twig rendering
     * @param $publications array publications
     * @return array
     */
    private function make_content($publications, $search_id) {
        $content = array();
        foreach ($publications as $p) {
            array_push($content, array(Database::PUBLICATION_TITLE => $p->title,
                Database::PUBLICATION_TYPE => $p->type, Database::PUBLICATION_AUTHOR => $p->author,
                Database::PUBLICATION_DEPARTMENT => $p->department, Database::PUBLICATION_CONTENT => $p->content,
                Database::PUBLICATION_RLYEAR => $p->rlyear, Database::PUBLICATION_UDATE => $p->udate));
        }

        Debug::vdump($content);
        return ['content' => $content, InterfaceValues::SEARCH_HASH => $search_id];
    }

    public function handle($context)
    {
        $action = $context->action();
        return $this->$action($context); #@@ return the template
    }

}