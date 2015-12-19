<?php

/**
 * @author Danh Nguyen <d.t.nguyen@newcastle.ac.uk>
 */

/**
 * Class SearchPublication handles search related queries
 *
 * Handle /searchcontent/, /author/, /category/, /department/, /rlyear/.
 */
class PublicOperation extends Siteaction
{
    /**
     * /searchcontent/ handler.
     *
     * There are two types of search requests which could be sent to the server.
     *
     * 1. Post /searchcontent using parameters
     * The server will first obtain a hash id using search parameters. If the hash does not exist in the database
     * it then stores it so that later searches using this id could refer to the correct parameters.
     * The search is then proceeded as usual.
     * >>> The first page of the result is returned.
     *
     * 2. Get /searchcontent/{hash}/{pagenumber}
     * Used mostly to implement pagination. the {hash} extracted from context->rest() is used to query parameters in
     * the search table. The returned parameters are then used for searching publications as usual.
     * >>> the page {pagenumber} is returned. Or the first page is returned if {pagenunmber} is not specified.
     *
     * @param $context Context the context object for the site
     * @return string the template
     */
    public function searchcontent($context)
    {
        $search_id = '';
        $query = array();
        $currentpage = 1;
        // this is probably of type 1 search in which users submit form fields through post.
        switch ($_SERVER['REQUEST_METHOD'])
        {
            case 'POST' : {
                foreach (InterfaceValues::VALID_SEARCH_PARAMS as $param)
                {
                    if ($context->haspostpar($param))
                        $query[$param] = $context->mustpostpar($param, true);
                }
                $filtered_query = $this->filter_query($query);
Debug::show("Entry method filtered query:");
Debug::vdump($filtered_query);
                //make sure query is not empty...
                if (!empty($filtered_query))
                {
                    $full_result = $this->search_publications_query($filtered_query);
                    // default currentpage is 1.
                    $currentpage = $context->postpar(InterfaceValues::CURRENT_PAGE, 1);
                }
                else
                {
                    Debug::show("Entry method query empty ");
                    // prepare the content
                    $full_result = $this->make_contents(array(), 0, false);
                }
                break;
            }
            // if the search request is a get then it means it's probably a search using {hash} (type 2)
            case 'GET' :
            {
                // get search id for this search
                $search_id = $context->rest()[0];
                if ($search_id)
                {
                    $full_result = $this->search_publications_id($search_id);
                    $currentpage = array_key_exists(1, $context->rest() ) ? $context->rest()[1] : 1;
                }
                else
                {
                    $context->divert('/home/');
                }
                break;
            }
            default :
            {
                // any method other than post and get is not handled therefore restricted
                $context->divert('/error/403');
            }
        }
       return $this->prepare_search_page($context, $currentpage, 'Search results', $full_result,
           array(array("name" => InterfaceValues::SEARCH_HASH,  'val' => $full_result[InterfaceValues::SEARCH_HASH] )));
    }

    /**
     * Handle /author/
     *
     * @param $context object the context
     * @return string the processed template
     */
    public function author($context)
    {
        $author = str_replace('_', ' ', trim($context->rest()[0]));
        return $this->list_cat($context, 'author', $author,
            Database::PUBLICATION_AUTHOR, $author, 'Author '.$author);
    }

    /**
     * Handle /rlyear/
     *
     * @param $context object the context
     * @return string the processed template
     */
    public function rlyear($context)
    {
        $rlyear = str_replace('_', ' ', trim($context->rest()[0]));
        return $this->list_cat($context, 'rlyear', $rlyear,
            Database::PUBLICATION_RLYEAR, $rlyear, 'Release year '.$rlyear);

    }

    /**
     * Handle /department/
     *
     * @param $context object the context
     * @return string the processed template
     */
    public function department($context)
    {
        $department = str_replace('_', ' ', trim($context->rest()[0]));
        $department_id = Database::get_beans_single_param(Database::DEPARTMENT, Database::DEPARTMENT_NAME, $department, true)['id'];
        return $this->list_cat($context, 'department', str_replace('_', ' ', trim($context->rest()[0])),
            Database::PUBLICATION_DEPARTMENT, $department_id, 'Department '.$department);
    }

    /**
     * Handle /category/ (doc|raw|src|src)
     *
     * @param $context object the context
     * @return string the processed string
     */
    public function category($context)
    {
        $category_abbrv = $context->rest()[0];
        $category = InterfaceValues::CATEGORIES[$category_abbrv];
        if (!array_key_exists($category_abbrv, InterfaceValues::CATEGORIES))
        {
            $context->divert('/error/404');
        }
        return $this->list_cat($context, 'category', $category_abbrv, Database::PUBLICATION_TYPE, $category, 'Category', 'Category '.$category_abbrv);
    }

    /**
     * Retrieve all publications (useful for handling /home/ and /)
     *
     * @param $context object the context
     * @param $page int the page to be shown
     * @return string the processed template
     */
    public function full($context, $page)
    {
        $full_result = $this->search_publications_query(array(Database::PUBLICATION_TITLE => '.+'));
        return $this->prepare_search_page($context, $page , 'Home', $full_result);
    }

    /**
     * Handle /download/
     *
     * @param $context object the context
     */
    public function download($context)
    {
Debug::show('download operation triggered');
        // build a proper relative path first
        $filepath = "assets/uploads/".$context->rest()[0];
        if (!$filepath)
        {
            $context->divert('/home/');
        }
        $context->sendfile($filepath);
    }

    /**
     * handle /content/ - show any content
     * TODO user
     *
     * @param $context object the context
     * @return string the processed publication template
     */
    public function content($context)
    {
        // verify the content id first
        $pub_id = array_key_exists(0, $context->rest()) ? ($context->rest()[0] ? $context->rest()[0] : 0) : 0;
        if ($pub_id === 0)
        {
            $context->divert("/error/404");
        }
        $pub_bean = $context->load(Database::PUBLICATION, $pub_id);
        //prepare array content
        $pub_content = $this->make_content($pub_bean, true);
        $context->local()->addval(InterfaceValues::LEFTNAV, true);
        $context->local()->addval(InterfaceValues::PAGE_TITLE, $pub_bean->title);
        $context->local()->addVal(InterfaceValues::BLOCKCONTENT, $pub_content);
        return 'publication.twig';
    }

    /**
     * Inner publication search using search hashed id (Hashed parameters)
     * @param $search_id string the hashed search id
     * @return array of results (twig compatible)
     */
     function search_publications_id($search_id)
     {
        $search_rb = R::findOne(Database::SEARCH, 'hash =?', [$search_id]);
        // if the search hash is not found then return 404
        if (!$search_rb)
        {
            (new Web)->notfound("Page not found.");
        }
        $query = $this->filter_query(array(InterfaceValues::BLOCKCONTENT_TYPE => $search_rb->type,
            InterfaceValues::BLOCKCONTENT_TITLE => $search_rb->title,
            InterfaceValues::BLOCKCONTENT_AUTHOR => $search_rb->author,
            InterfaceValues::BLOCKCONTENT_DEPRARTMENT => $search_rb->department,
            InterfaceValues::BLOCKCONTENT_RLYEAR => $search_rb->rlyear));
        $filtered_query = $this->filter_query($query);
        $sql = $this->build_search_query($filtered_query);
Debug::show("SQL query:");
Debug::vdump($sql);
        $publications_rb = R::find(Database::PUBLICATION, $sql , array_values($filtered_query));
        return $this->make_contents($publications_rb, $search_id, false);
}

    /**
     * Inner publication search using query
     *
     * @param $query array|null the query
     * @return array of results (twig compatible)
     */
     function search_publications_query($query)
     {
        if (!$query)
        {
            (new Web)->internal(Util::ERROR_MESSAGE_500.var_export($query, true));
        }
        #@@ $query takes precedence over $search_id. However the $search_id is not trashed.
            #@@ make the search id for this query
            $search_id = $this->make_search_id($query);
            #@@ if search id is not already in database? Create and store it.
            $search_rb = R::findOne(Database::SEARCH, ' hash = ? ', [$search_id]);
            if (!$search_rb)
            {
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
            return $this->make_contents($publications_rb, $search_id, false);
    }

    /**
     * build a search query (query part)
     *
     * @param $query array
     * @return string return the query string
     */
    private function build_search_query($query)
    {
        $sql = ' where ';
        foreach (array_keys($query) as $param)
        {
            // use string searching for title and author
            if (strcmp($param, InterfaceValues::BLOCKCONTENT_TITLE) === 0 || strcmp($param, InterfaceValues::BLOCKCONTENT_AUTHOR) === 0)
            {
                $sql .= ($param.' regexp ?');
            }
            // use equality searching for the remainder
            else
            {
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
     * //todo try making use of filter_ methods provided in the framework
     *
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
     *
     * @param $parameter string the parameter
     * @param $value string the value
     * @return bool true if the paramter is valid, false otherwise
     */
    private function filter_parameter($parameter, $value)
    {
        // param is invalid
        if (array_search($parameter, InterfaceValues::VALID_SEARCH_PARAMS) === false)
        {
Debug::show("param not valid: ".$parameter.$value);
            return false;
        }
        $value = trim($value);
        // value is invalid
        if (!$value)
        {
            Debug::show("value after trim is null: ".$parameter.$value);
            return false;
        }
        switch ($parameter)
        {
            case InterfaceValues::BLOCKCONTENT_TYPE :
            case InterfaceValues::BLOCKCONTENT_DEPRARTMENT:
            case InterfaceValues::BLOCKCONTENT_RLYEAR:
        {
            $valid = strcmp($value, InterfaceValues::BLOCKCONTENT_DEF);
Debug::show("private param filter in switch: ".$parameter.$value.$valid);
            return $valid;
        }
        }
        return true;
    }

    /**
     *return the search parameter value, or its default if the parameter value is false.
     *
     * @param $param string the parameter
     * @return string the default value for the parameter
     */
    private function def($param)
    {
        switch ($param)
        {
            case InterfaceValues::BLOCKCONTENT_TYPE :
            case InterfaceValues::BLOCKCONTENT_DEPRARTMENT:
            case InterfaceValues::BLOCKCONTENT_RLYEAR:
                return InterfaceValues::BLOCKCONTENT_DEF;
            default :
                return '';
        }
    }

    /**
     * get a search id for a query by hashing the query
     *
     * the id to bed returned needs to be hashed in a uniform order across searches
     *
     * @param $query array the filtered query
     * @return string the search id that is consistent for searches with identical params.
     */
    private function make_search_id($query)
    {
        $plain = '';
        foreach (array_values($query) as $value)
        {
            $plain .= $value;
        }
        $code = hash('sha256', $plain);
        if (!$code)
        {
            (new Web)->internal(Util::ERROR_MESSAGE_500.$code);
        }
        return $code;
    }

    /**
     * get the blocks needed given a page number
     *
     * @param $blocks array all blocks
     * @param $page_number mixed the page number
     * @return array the blocks needed
     */
    private function get_blocks($blocks, $page_number)
    {
        if (!is_numeric($page_number) || $page_number < 0) {
            (new Web)->internal('Something went wrong. We are working on it. ' . $page_number);
        }
        // def is page 1
        if ($page_number === 0)
        {
            $page_number = 1;
        }
        return array_slice($blocks, ($page_number - 1) * InterfaceValues::BLOCKS_PER_PAGE, InterfaceValues::BLOCKS_PER_PAGE);
    }

    /**
     * Return a parameter value given a paramter in the query
     *
     * @param $query array the query
     * @param $param string the parameter
     * @return string the value corresponding to the parameter, or its default value
     */
    private function get_value_param($query, $param)
    {
        if (array_key_exists($param, $query))
        {
            return $query[$param];
        }
        return $this->def($param);
    }

    /**
     * make array of block contents for twig rendering
     *
     * @param $publications array publications
     * @param null $search_id string the search id (hash)
     * @param $full_description bool do we need to show the whole description? (Search page <> content page)
     * @return array the twig compatible value array
     */
    private function make_contents($publications, $search_id = null, $full_description = true) {
            $content = array();
            foreach ($publications as $p) {
                 array_push($content, $this->make_content($p, $full_description));
            }
        return ['content' => $content, InterfaceValues::SEARCH_HASH => $search_id];
    }

    /**
     * make a single block content for twig rendering
     *
     * @param $publication array the content
     * @param bool|true $full_description do we need to show the whole description
     * @return array the twig compatible array
     */
    private function make_content($publication, $full_description = true)
    {
        $department_name = Database::get_beans_single_param(Database::DEPARTMENT, Database::DEPARTMENT_ID, $publication->department)[$publication->department][Database::DEPARTMENT_NAME];
        return array(Database::PUBLICATION_TITLE => $publication->title,
            Database::PUBLICATION_TYPE => $publication->type,
            Database::PUBLICATION_AUTHOR => $publication->author,
            Database::PUBLICATION_DEPARTMENT => $department_name,
            Database::PUBLICATION_CONTENT => $publication->content,
            Database::PUBLICATION_RLYEAR => $publication->rlyear,
            Database::PUBLICATION_UDATE => $publication->udate,
            Database::PUBLICATION_DESCRIPTION => $full_description ? $publication->description : Util::shorten($publication->description, InterfaceValues::BRIEF_DESCRIPTION_LIMIT),
            Database::PUBLICATION_ID => $publication->id,
            Database::PUBLICATION_UDATE => date('d F Y', strtotime($publication->udate)),
            Database::PUBLICATION_POSTBY => $publication->postby,
            'file' => $publication->file,
            'url' => $publication->url);
    }

    /**
     *Inner operation of single valued search pages such as /author/, /department/
     *
     * @param $context object the context
     * @param $cattype string the category type (author, department or rlyear)
     * @param $catval string the category value (author name, department name, rlyear)
     * @param $cattype_dbname  string the column name of the cattype
     * @param $catval_dbval string the value of the catval (most of the time they are identical)
     * @param $page_title string the title of the page
     * @return string the processed template
     */
     function list_cat($context, $cattype, $catval, $cattype_dbname, $catval_dbval, $page_title)
     {
        if (!$catval)
        {
            $context->divert('/home');
        }
        $page = array_key_exists(1, $context->rest()) ? $context->rest()[1] : 1;
        $full_result = $this->search_publications_query(array($cattype_dbname => $catval_dbval));
        return $this->prepare_search_page($context, $page, $page_title, $full_result,
            array(array('name' => InterfaceValues::CATEGORY_TYPE, 'val' => $cattype),
                array('name' => InterfaceValues::CATEGORY_VAL, 'val' => $catval)));
    }

    /**
     * Content preparing function for a search page
     *
     * @param $context object the context
     * @param $current_page the int current page
     * @param $page_title string the page title
     * @param $blockcontents array publications (block contents) to be displayed
     * @param array $additional_attributes array any additional attributes
     * @return string the processed template
     */
    private function prepare_search_page($context, $current_page, $page_title, $blockcontents, $additional_attributes = array())
    {
        $max_page = ceil(sizeof($blockcontents['content']) / InterfaceValues::BLOCKS_PER_PAGE);
        if ($max_page > 0 && $current_page > $max_page) {
            $context->divert("/home");
        }
        $page_count = $max_page;
        // add bcontents to twig compatible vars
        $context->local()->addVal(InterfaceValues::BLOCKCONTENTS,
            $this->get_blocks($blockcontents['content'], $current_page));
        // add currentpage to twig compatible vars
        $context->local()->addVal(InterfaceValues::CURRENT_PAGE, $current_page);
        $context->local()->addVal(InterfaceValues::PAGES_COUNT, $page_count);
        $context->local()->addval(InterfaceValues::LEFTNAV, true);
        $context->local()->addval(InterfaceValues::PAGINATION, $page_count > 0);
Debug::show('page count for admin posts'. $page_count);
        $context->local()->addval(InterfaceValues::PAGE_TITLE, $page_title);

        foreach ($additional_attributes as $attribute)
        {
            $context->local()->addval($attribute["name"], $attribute["val"]);
        }
        return "search.twig";
    }

    /**
     * get a category name (doc, src, raw or app) given the id
     *
     * in this case, 'category' implies the four categories, not the general term used in list_cat.
     *
     * @param $id int the id of the category
     * @return array|string the category name
     */
    private function categoryName($id) {
        if (array_key_exists($id, InterfaceValues::BLOCKCONTENT_VALUES))
            return InterfaceValues::BLOCKCONTENT_VALUES[$id];
        return "Null";
    }

    /**
     * Main entry
     *
     * @param object $context the context
     * @return mixed
     */
    public function handle($context)
    {
        $action = $context->action();
        return $this->$action($context); // return the template
    }

}