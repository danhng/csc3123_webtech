<?php

/**
 * @author Danh nguyen
 *
 */
class Experiment extends Siteaction
{
        public function publish_inner($what) {
            $publication = R::dispense(Database::PUBLICATION);
            $publication->title = $what[Database::PUBLICATION_TITLE];
            $publication->type = $what[Database::PUBLICATION_TYPE];
            $publication->author = $what[Database::PUBLICATION_AUTHOR];
            $publication->department = $what[Database::PUBLICATION_DEPARTMENT];
            $publication->rlyear = $what[Database::PUBLICATION_RLYEAR];
            $publication->udate = $what[Database::PUBLICATION_UDATE];
            #@@ todo count
            return R::store($publication);

        }

    public function publish_test($context) {
        $bcontents = array("type" => "[RAW]",
            "title" => "This is a title",
            "author" => "Juan Rodriguez",
            "department" => "Computing Science",
            "rlyear" => "2014",
            "description"=> "Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium
                                doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae
                                dicta sunt explicabo.",
            'udate' => '2015-07-31');
        $this->publish_inner($bcontents);
        $context->local();
        return 'okay.twig';
    }

    public function handle($context)
    {
        $action = $context->action() . '_test';
        $this->$action($context);
    }


}