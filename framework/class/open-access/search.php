<?php

/**
 * @author Danh nguyen
 */

/**
 * Class Search to handle search queries.
 */
class Search extends Siteaction
{
    /**
     * /search handler.
     * @param $context Context the context object for the site
     * @return string the template
     */
    public function search($context) {
        $query = array();
        foreach (InterfaceValues::VALID_SEARCH_PARAMS as $param) {
            if ($context->haspostpar($param))
                array_push($query, array($param => $context->postpar($param)));
        }
        #@@ add twig variables to the local object
        $context->local()->addVal(InterfaceValues::BLOCKCONTENTS, $this->search_publications($query));
        return 'page.twig';
    }

    /**
     * Inner search of
     * @param $query array
     * @return array
     */
    private function search_publications($query) {
        //todo verify query (javascript?)
        $query = $this->filter_query($query);
        $publications = R::find(Database::PUBLICATION, $this->build_search_query($query), array_values($query));
        return $this->make_content($query);
    }

    private function build_search_query($query) {
        $sql = '';
        foreach (array_keys($query) as $param) {
            if (!strcmp($sql, '')) {
                $sql .= ' and ';
            }
            #@@ use string searching for title and author
            if (strcmp($param, InterfaceValues::BLOCKCONTENT_TITLE) || strcmp($param, InterfaceValues::BLOCKCONTENT_AUTHOR)) {
                $sql .= ($param.' regexp ?');
            }
            #@@ use equality searching for the remainder
            $sql .= ($param.' =?');
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
            if ($pair = $this->filter_parameter($param, $value))
                array_push($filtered_query, $pair);
        }
    }

    /**
     * Filter a single parameter
     * @param $parameter string the parameter
     * @param $value string the value
     * @return array|bool
     */
    private function filter_parameter($parameter, $value) {
        # param is invalid
        if (array_search($parameter, InterfaceValues::VALID_SEARCH_PARAMS)) {
            return false;
        }
        $value = trim($value);
        # value is invalid
        if ($value === '') {
            return false;
        }
        switch ($parameter) {
            case InterfaceValues::BLOCKCONTENT_TYPE :
            case InterfaceValues::BLOCKCONTENT_DEPRARTMENT:
            case InterfaceValues::BLOCKCONTENT_RLYEAR:
                return (strcmp($value, InterfaceValues::BLOCKCONTENT_DEF) ? false : array($parameter => $value));
        }
        return array($parameter => $value);
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
        return $content;
    }

    public function handle($context)
    {
        $action = $context->action();
        return $this->$action($context); // return the template
    }

}