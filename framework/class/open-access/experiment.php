<?php

/**
 * @author Danh nguyen
 *
 */
class Experiment extends Siteaction
{
        public function publish_inner($what) {

            $publication = R::dispense(Database::PUBLICATION);

            $department_bean = Database::get_bean_single_param(Database::DEPARTMENT, Database::DEPARTMENT_NAME, $what[Database::PUBLICATION_DEPARTMENT]);

            R::begin();
            try{
                $d_id = null;
                if (empty($department_bean)) {
                    $department_bean = R::dispense(Database::DEPARTMENT);
                    $department_bean->name= $what[Database::PUBLICATION_DEPARTMENT];
                    $d_id = R::store($department_bean);
Debug::show("No department has been stored for this department. New id ".$d_id);
                }
                else {
                    $d_id = $department_bean->id;
                }
                $publication->title = $what[Database::PUBLICATION_TITLE];
                $publication->type = $what[Database::PUBLICATION_TYPE];
                $publication->author = $what[Database::PUBLICATION_AUTHOR];
                $publication->department = $d_id;
                $publication->description = $what[Database::PUBLICATION_DESCRIPTION];
                $publication->rlyear = $what[Database::PUBLICATION_RLYEAR];
                $publication->udate = $what[Database::PUBLICATION_UDATE];
                $id = R::store($publication);
                R::commit();
                return $id;
            }
            catch(Exception $e) {
                R::rollback();
Debug::show("Exception caught for transaction: ");
Debug::vdump($e->getMessage());
                return 0;
            }

//            $publication = R::dispense(Database::PUBLICATION);
//            $publication->title = $what[Database::PUBLICATION_TITLE];
//            $publication->type = $what[Database::PUBLICATION_TYPE];
//            $publication->author = $what[Database::PUBLICATION_AUTHOR];
//            $publication->department = $what[Database::PUBLICATION_DEPARTMENT];
//            $publication->description = $what[Database::PUBLICATION_DESCRIPTION];
//            $publication->rlyear = $what[Database::PUBLICATION_RLYEAR];
//            $publication->udate = $what[Database::PUBLICATION_UDATE];
//            #@@ todo count
//            return R::store($publication);

        }

    public function publish_test($context) {
        $bcontents = array('type' => '1',
            'title' => 'This is a title',
            'author' => 'Juan Rodriguez',
            'department' => 'Electrical Comp',
            'rlyear' => '2014',
            'description'=> 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium
                                doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae
                                dicta sunt explicabo.',
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