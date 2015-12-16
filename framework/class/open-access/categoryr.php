<?php
//
///**
// * @author Danh nguyen
// *
// */
//
///**
// * Class Category
// */
//class CategoryR extends Siteaction
//{
//
//    public function category($context) {
//        $category_abbrv = $context->rest()[0];
//        if (!array_key_exists($category_abbrv, InterfaceValues::CATEGORIES)) {
//            (new Web())->notfound("URL /category/".$category_abbrv.' is not found.');
//        }
//        $category = InterfaceValues::CATEGORIES[$category_abbrv];
//        $page = array_key_exists(1, $context->rest()) ? $context->rest()[1] : 1;
//
//        $full_result = (new PublicOperation())->search_publications_query(array(Database::PUBLICATION_TYPE => $category));
//
//        #@@ add search hash to twig compatible vars
//        $context->local()->addval(InterfaceValues::SEARCH_HASH, $full_result[InterfaceValues::SEARCH_HASH]);
//        #@@ add bcontents to twig compatible vars
//        $context->local()->addVal(InterfaceValues::BLOCKCONTENTS,
//            $this->get_blocks($full_result['content'], $page));
//        #@@ add currentpage to twig compatible vars
//        $context->local()->addVal(InterfaceValues::CURRENT_PAGE, $page);
//        $context->local()->addVal(InterfaceValues::PAGES_COUNT, ceil(sizeof($full_result['content']) / InterfaceValues::BLOCKS_PER_PAGE));
//        $context->local()->addval(InterfaceValues::LEFTNAV, true);
//        $context->local()->addval(InterfaceValues::PAGNIATION, true);
//        $context->local()->addval(InterfaceValues::PAGE_TITLE, "Category ".$this->categoryName($category));
//
//        return "search.twig";
//    }
//
//    private function categoryName($id) {
//        if (array_key_exists($id, InterfaceValues::BLOCKCONTENT_VALUES))
//            return InterfaceValues::BLOCKCONTENT_VALUES[$id];
//        return "Null";
//    }
//
//    public function handle($context)
//    {
//        $action = $context->action();
//        return $this->$action($context); #@@ return the template
//    }
//
//}