<?php

/**
 * @author Danh Nguyen <d.t.nguyen@newcastle.ac.uk>
 *
 */

/**
 * Class PrivateOperation provides login and admin required operations. (Staff)
 */
class PrivateOperation
{
    /**
     * do a publication (input is still inside $context)
     *
     * @param object $context the current context
     *
     * @return bool|int|string return the id of the newly published content, or false or 0 if failed.
     */
    public function dopublish($context)
    {
        // is admin yet?
        if (!$context->hasadmin())
        {
            $context->divert('/error/403');
        }
        // is the department id ready yet or the content being published brings in a new department?
        $departmentid_ready = true;
        // does the content require file upload?
        $hasfile = $_FILES['file-id'];

Debug::show('$files');
Debug::vdump($_FILES);
Debug::vdump($hasfile);

        $user = $context->user();

        // filter the contents
        // todo looks quite messy at the moment
        $author_raw = str_replace('_', ' ', $context->postpar(InterfaceValues::BLOCKCONTENT_AUTHOR, ''));
        if(!$author_raw)
        {
            $author_raw = $context->postpar('new-author', '');
        }
        $title_raw = $context->postpar(InterfaceValues::BLOCKCONTENT_TITLE, '');
        $type_raw = $context->postpar(InterfaceValues::BLOCKCONTENT_TYPE, '');
        $department_raw = $context->postpar(InterfaceValues::BLOCKCONTENT_DEPRARTMENT, '');
        if(!$department_raw)
        {
            $department_raw= $context->postpar('new-department', '');
            $departmentid_ready = false;
        }
        $description_raw = $context->postpar(InterfaceValues::BLOCKCONTENT_DESCRIPTION, '');
        $rlyear_raw = $context->postpar(InterfaceValues::BLOCKCONTENT_RLYEAR, '');
        if(!$rlyear_raw)
        {
            $rlyear_raw = $context->postpar('new-rlyear', '');
        }
        $url = $context->postpar('url', '');

        // all the fields except file and external url must be filled.
        if (!$author_raw || !$title_raw || !$type_raw || !$department_raw || !$description_raw || !$rlyear_raw) {
            return 0;
        }
Debug::show('upload date is'. date('Y-m-d'));

        return $this->publish_inner(array('type' => $type_raw,
            'title' => $title_raw,
            'author' => $author_raw,
            'department' => $department_raw,
            'rlyear' => $rlyear_raw,
            'description' => $description_raw,
            'udate' => date('Y-m-d'),
            'postby' => $user->login,
            'hasfile' => $hasfile,
            'url' => $url), $departmentid_ready);
    }

    /**
     * Do a content removal. The admin user must be the owner of the content in order to perform the removal.
     *
     * @param object $context the current context
     * @param $content_id int the id of the content being removed
     * @return bool true if the removal succeeds, false otherwise
     */
    public function remove($context, $content_id)
    {
        // is admin yet?
        if (!$context->hasadmin())
        {
            $context->divert('/error/403');
        }

        // does the admin own this content?
        if (!$this->own($context, $content_id))
        {
            return false;
        }
        return $this->remove_inner($content_id);
    }

    /**
     * Do a content edit.
     *
     * @param object $context the current context
     * @param $content_id int the id of the current content
     * @return bool|int|string return the new id of the content. false or 0 if the editing fails.
     * todo this method is quite similar to dopublish in that they both perform the same operation to the database
     * todo needs a way to generalise them.
     */
    public function doedit($context, $content_id)
    {
        // must be admin
        if (!$context->hasadmin())
        {
            $context->divert('/error/403');
        }

        // must own the content in order to edit it
        if (!$this->own($context, $content_id))
        {
Debug::show('fail ownership check');
            return false;
        }

        $departmentid_ready = true;
        $user = $context->user();

        //fetch input fields.
        // todo again another mess...
        $author_raw = str_replace('_', ' ', $context->postpar(InterfaceValues::BLOCKCONTENT_AUTHOR, ''));
        if(!$author_raw)
        {
            $author_raw = $context->postpar('new-author', '');
        }
        $title_raw = $context->postpar(InterfaceValues::BLOCKCONTENT_TITLE, '');
        $type_raw = $context->postpar(InterfaceValues::BLOCKCONTENT_TYPE, '');
        $department_raw = $context->postpar(InterfaceValues::BLOCKCONTENT_DEPRARTMENT, '');
        if(!$department_raw)
        {
            $department_raw= $context->postpar('new-department', '');
            $departmentid_ready = false;
        }
        $description_raw = $context->postpar(InterfaceValues::BLOCKCONTENT_DESCRIPTION, '');
        $rlyear_raw = $context->postpar(InterfaceValues::BLOCKCONTENT_RLYEAR, '');
        if(!$rlyear_raw)
        {
            $rlyear_raw = $context->postpar('new-rlyear', '');
        }
        $url = $context->postpar('url', '');
        $file = $context->load(Database::PUBLICATION, $content_id)['file'];

        // all input fields must not be empty except the file and url fields.
        if (!$author_raw || !$title_raw || !$type_raw || !$department_raw || !$description_raw || !$rlyear_raw)
        {
Debug::show('fail input filter check');
            return false;
        }

        return $this->edit_inner($content_id, array('type' => $type_raw,
            'title' => $title_raw,
            'author' => $author_raw,
            'department' => $department_raw,
            'rlyear' => $rlyear_raw,
            'description' => $description_raw,
            'udate' => date('Y-m-d'),
            'postby' => $user->login,
            'url' => $url,
            'file' => $file), $departmentid_ready);
    }

    /**
     * Check if the current in context admin user owns a content
     *
     * @param object $context
     * @param $content_id
     * @return bool
     */
    private function own($context, $content_id) {
        $content_id = trim($content_id);
        if (!$content_id) {
            return false;
        }

        $content_bean = R::load(Database::PUBLICATION, $content_id);
        if (!$content_bean->id) {
Debug::show('warning. content id '.$content_id.' is not found.');
            return false;
        }
        if ($context->user()->login !== $content_bean[Database::PUBLICATION_POSTBY]) {
Debug::show('warning. '. $context->user()->login. ' does not own content ' .$content_id);
            return false;
        }
        return true;
    }

    private function remove_inner($content_id) {
        $content = R::load(Database::PUBLICATION, $content_id);
        try {
            R::trash($content);
            return true;
        } catch (Exception $e) {
Debug::show('Caught exception: '.$e->getMessage());
            return false;
        }
    }



    private function edit_inner($content_id, $new_pub, $has_department_id)
    {
        $content_id = trim($content_id);
        if (!$content_id)
        {
            return false;
        }
        $content = R::load(Database::PUBLICATION, $content_id, $has_department_id);
        R::begin();
        try
        {
            R::trash($content);
            return  $this->publish_inner($new_pub, $has_department_id);
        }
        catch (Exception $e)
        {
Debug::show('Caught exception: '.$e->getMessage());
            return false;
        }
    }

    private function publish_inner($what, $department_id_ready = false)
    {
        $fileupload = 0;
        if (array_key_exists('hasfile', $what) && $what['hasfile']) {
            $fileupload = Util::upload();
            if ($fileupload === 0 || $fileupload === 1 || $fileupload === 2 || $fileupload === 3 || $fileupload === 4 || $fileupload === 5) {
                    return false;
            }
        }
Debug::show('file upload status 0. '.$fileupload);

Debug::show('file upload status. '.$fileupload);
        $publication = R::dispense(Database::PUBLICATION);

        $department_bean = Database::get_beans_single_param(Database::DEPARTMENT, Database::DEPARTMENT_NAME, $what[Database::PUBLICATION_DEPARTMENT], true);
        $d_id = null;
        R::begin();
        try {
            if (!$department_id_ready) {
                if (empty($department_bean)) {
                    $department_bean = R::dispense(Database::DEPARTMENT);
                    $department_bean->name = $what[Database::PUBLICATION_DEPARTMENT];
                    $d_id = R::store($department_bean);

                    Debug::show("No department has been stored for this department. New id " . $d_id);
                } else {
                    $d_id = $department_bean->id;
                }
            }
            else {
                $d_id = $what[Database::DEPARTMENT];
            }
            $publication->title = $what[Database::PUBLICATION_TITLE];
            $publication->type = $what[Database::PUBLICATION_TYPE];
            $publication->author = $what[Database::PUBLICATION_AUTHOR];
            $publication->department = $d_id;
            $publication->description = $what[Database::PUBLICATION_DESCRIPTION];
            $publication->rlyear = $what[Database::PUBLICATION_RLYEAR];
            $publication->udate = $what[Database::PUBLICATION_UDATE];
            $publication->postby = $what[Database::PUBLICATION_POSTBY];
            $publication->url = $what['url'];
            $publication->file = $fileupload;
            $id = R::store($publication);
            R::commit();
            return $id;
        } catch (Exception $e) {
            R::rollback();
            Debug::show("Exception caught for transaction: ");
            Debug::vdump($e->getMessage());
            return false;
        }
    }


}
