<?php

/**
 * @author Danh Nguyen <d.t.nguyen@newcastle.ac.uk>
 */

/**
 * Class SearchPublication to handle search queries.
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
                $filtered_query = $this->filter_query($query);
Debug::show("Entry method filtered query:");
Debug::vdump($filtered_query);
                #@@ make sure query is not empty...
                if (!empty($filtered_query)){
                    $full_result = $this->search_publications_query($filtered_query);
                    $currentpage = $context->postpar(InterfaceValues::CURRENT_PAGE, 1);
                }
                else {
                    Debug::show("Entry method query empty ");
                    $full_result = $this->make_content(array(), 0);
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
       return $this->prepareSearchPage($context, $currentpage, 'Search results', $full_result,
           array(array("name" => InterfaceValues::SEARCH_HASH,  'val' => $full_result[InterfaceValues::SEARCH_HASH] )));
    }

    /**
     * Inner publication search using search id
     * @param $search_id string
     * @return array of results (twig compatible)
     */
     function search_publications_id($search_id) {
        $search_rb = R::findOne(Database::SEARCH, 'hash =?', [$search_id]);
        #@@ oops more than 1 search is found...
        if (!$search_rb) {
            (new Web)->notfound("Page not found.");
        }
        $query = $this->filter_query(array(InterfaceValues::BLOCKCONTENT_TYPE => $search_rb->type,
            InterfaceValues::BLOCKCONTENT_TITLE => $search_rb->title,
            InterfaceValues::BLOCKCONTENT_AUTHOR => $search_rb->author,
            InterfaceValues::BLOCKCONTENT_DEPRARTMENT => $search_rb->department,
            InterfaceValues::BLOCKCONTENT_RLYEAR => $search_rb->rlyear));
        //todo verify query (javascript?)
        $filtered_query = $this->filter_query($query);
        $sql = $this->build_search_query($filtered_query);
Debug::show("SQL query:");
Debug::vdump($sql);
        $publications_rb = R::find(Database::PUBLICATION, $sql , array_values($filtered_query));
        return $this->make_content($publications_rb, $search_id);
}

    /**
     * Inner publication search using query
     * @param $query array|null
     * @return array of results (twig compatible)
     */
     function search_publications_query($query) {
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
            $sql = $this->build_search_query($query);
        Debug::show("SQL query:");
        Debug::vdump($sql);
            $publications_rb = R::find(Database::PUBLICATION, $sql,
                array_values($query));
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
            #@@ use string searching for title and author
            if (strcmp($param, InterfaceValues::BLOCKCONTENT_TITLE) === 0 || strcmp($param, InterfaceValues::BLOCKCONTENT_AUTHOR) === 0) {
                $sql .= ($param.' regexp ?');
            }
            #@@ use equality searching for the remainder
            else {
                $sql .= ($param.' = ?');
            }
                $sql .= Database::AND_HOLDER;
        }
        $sql = substr($sql, 0, count($sql) - Database::AND_HOLDER_COUNT - 1);
        $sql = str_replace(Database::AND_HOLDER, ' and ', $sql);
        return $sql;
    }
    /**
     * filter the raw query from the client
     * @param $raw_query array the raw query from context (reqrest)
     * @return array the filter query in which all trash parameters are ruled out.
     */
    private function filter_query($raw_query) {
Debug::show("query before filter:");
Debug::vdump($raw_query);
        $filtered_query = array();
        foreach ($raw_query as $param => $value) {
            if ($this->filter_parameter($param, $value))
                $filtered_query[$param] = $value;
        }
Debug::show("query after filter:");
Debug::vdump($filtered_query);
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
        if (array_search($parameter, InterfaceValues::VALID_SEARCH_PARAMS) === false) {
Debug::show("param not valid: ".$parameter.$value);
            return false;
        }
        $value = trim($value);

        # value is invalid
        if (!$value) {
            Debug::show("value after trim is null: ".$parameter.$value);
            return false;
        }
        switch ($parameter) {
            case InterfaceValues::BLOCKCONTENT_TYPE :
            case InterfaceValues::BLOCKCONTENT_DEPRARTMENT:
            case InterfaceValues::BLOCKCONTENT_RLYEAR: {
            $valid = strcmp($value, InterfaceValues::BLOCKCONTENT_DEF);
Debug::show("private param filter in switch: ".$parameter.$value.$valid);
            return $valid;
        }
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
            $department_name = Database::get_beans_single_param(Database::DEPARTMENT, Database::DEPARTMENT_ID, $p->department)[$p->department][Database::DEPARTMENT_NAME];
            array_push($content, array(Database::PUBLICATION_TITLE => $p->title,
                Database::PUBLICATION_TYPE => $p->type, Database::PUBLICATION_AUTHOR => $p->author,
                Database::PUBLICATION_DEPARTMENT => $department_name, Database::PUBLICATION_CONTENT => $p->content,
                Database::PUBLICATION_RLYEAR => $p->rlyear, Database::PUBLICATION_UDATE => $p->udate,
                Database::PUBLICATION_DESCRIPTION => $p->description));
        }
        return ['content' => $content, InterfaceValues::SEARCH_HASH => $search_id];
    }

    public function category($context) {
        $category_abbrv = $context->rest()[0];
        if (!array_key_exists($category_abbrv, InterfaceValues::CATEGORIES)) {
            $context->divert('/error/404');
        }
        $category = InterfaceValues::CATEGORIES[$category_abbrv];
        $page = array_key_exists(1, $context->rest()) ? $context->rest()[1] : 1;

        $full_result = $this->search_publications_query(array(Database::PUBLICATION_TYPE => $category));

        return $this->prepareSearchPage($context, $page, "Category ".$this->categoryName($category), $full_result,
            array(array('name' => InterfaceValues::CATEGORY_TYPE, 'val' => $category_abbrv)));
    }

    public function full($context, $page) {
        $full_result = $this->search_publications_query(array(Database::PUBLICATION_TITLE => '.+'));
        return $this->prepareSearchPage($context, $page , 'Home', $full_result);
    }

    private function prepareSearchPage($context, $current_page, $page_title, $blockcontents, $additional_attributes = array()) {
        #@@ add bcontents to twig compatible vars
        $context->local()->addVal(InterfaceValues::BLOCKCONTENTS,
            $this->get_blocks($blockcontents['content'], $current_page));
        #@@ add currentpage to twig compatible vars
        $context->local()->addVal(InterfaceValues::CURRENT_PAGE, $current_page);
        $page_count = ceil(sizeof($blockcontents['content']) / InterfaceValues::BLOCKS_PER_PAGE);
        $context->local()->addVal(InterfaceValues::PAGES_COUNT, $page_count);
        $context->local()->addval(InterfaceValues::LEFTNAV, true);
        $context->local()->addval(InterfaceValues::PAGINATION, $page_count > 0);
        $context->local()->addval(InterfaceValues::PAGE_TITLE, $page_title);

        foreach ($additional_attributes as $attribute) {
            $context->local()->addval($attribute["name"], $attribute["val"]);
        }

        return "search.twig";
    }



    private function categoryName($id) {
        if (array_key_exists($id, InterfaceValues::BLOCKCONTENT_VALUES))
            return InterfaceValues::BLOCKCONTENT_VALUES[$id];
        return "Null";
    }


    public function handle($context)
    {
        $action = $context->action();
        return $this->$action($context); #@@ return the template
    }

}