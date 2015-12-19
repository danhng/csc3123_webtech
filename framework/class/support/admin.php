<?php
/**
 * Contains definition of Admin class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2015 Newcastle University
 */
/**
 * A class that contains code to handle any /admin related requests.
 *
 * Admin status is checked in index.php so does not need to be done here.
 */
    class Admin extends Siteaction
    {


/**
 * Handle various admin operations /admin/xxxx
 *
 * @param object	$context	The context object for the site
 *
 * @return string	A template name
 */
	public function handle($context)
	{
	    $tpl = 'support/admin.twig';
	    $rest = $context->rest();
Debug::show('rest for publish post ');
Debug::vdump($rest);
	    switch ($rest[0])
        {
            // handle admin/publish
            case 'publish':
            {
                $tpl = 'publish.twig';
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $dopublish = $context->postpar(InterfaceValues::PUBLISH_BUTTON_NAME, '0');
Debug::show('do publish ' . $dopublish);
                    if ($dopublish === '1') {
                        $id = (new PrivateOperation())->dopublish($context);
Debug::show('id of new publication ' . $id.'/'. $id == true);
                        $context->local()->addVal(InterfaceValues::PUBLISH_DO, 1);
                        $context->local()->addVal(InterfaceValues::PUBLISH_DO_OK, $id);
                        $context->local()->addVal(InterfaceValues::CONTENT_ID, $id);
Debug::show('local ');
Debug::vdump($context->local());
                    }
                }
                else
                {
                    $context->local()->addVal(InterfaceValues::PUBLISH_DO, 0);
                }
                $context->local()->addVal(InterfaceValues::PAGE_TITLE, 'Publish a new content.');
                break;
            }
            // handle /view/ view and edit posts
            case 'view':
            {
                $login = $context->user()->login;
                $tpl = (new PublicOperation())->list_cat($context, 'admin', $login , 'postby', $login, 'Your posts');
                break;
            }
            // handle /remove/ remove a post
            case 'remove':
            {
                $content_id = $context->rest()[1];
Debug::show('Rest at remove: '.$context->rest()[1]);
                (new PrivateOperation())->remove($context, $content_id);
                $context->divert('/admin/view/');
                break;
            }
            // handle edit - edit a post
            case 'edit': {
                $tpl = 'edit.twig';

                if ($_SERVER['REQUEST_METHOD'] === 'POST')
                {
                    $old_id = $context->postpar('e-id', '');
                    $doedit = $context->postpar(InterfaceValues::EDIT_BUTTON_NAME, '0');
Debug::show('do edit old id ' . $old_id);
Debug::show('do edit ' . $doedit);
                    if ($doedit !== '0') {
                        $id = (new PrivateOperation())->doedit($context, $old_id);
Debug::show('id of edited publication ' . $id);
                        $context->local()->addVal(InterfaceValues::EDIT_DO, true);
                        $context->local()->addVal(InterfaceValues::EDIT_DO_OK, $id);
                        $context->local()->addVal(InterfaceValues::CONTENT_ID, $id);
                        $context->local()->addVal(InterfaceValues::PAGE_TITLE, 'Editing publication...');
                        if (!$id)
                        {
                            $context->local()->addVal('old_id', $old_id);
                        }
Debug::show('doedit . doedit_ok' .  $id !== false);
                    }
                    else
                    {
                        $context->local()->addVal(InterfaceValues::EDIT_DO, false);
                        $context->local()->addVal(InterfaceValues::PAGE_TITLE, 'Editing publication...');
                    }
                }
                else
                {
                    $old_id = array_key_exists(1, $context->rest()) ? $context->rest()[1] : '';
                    if (!$old_id) {
                        $context->divert('/admin/view/');
                    }
                    $old_content = R::load(Database::PUBLICATION, $old_id);
                    $context->local()->addVal(InterfaceValues::EDIT_DO, false);
                    // update content in edit forms
                    $context->local()->addVal('description_hasdef', true);
                    $context->local()->addVal('description_def', $old_content[Database::PUBLICATION_DESCRIPTION]);
                    $context->local()->addVal('author_hasdef', true);
                    $context->local()->addVal('author_def', str_replace(' ', '_', $old_content[Database::PUBLICATION_AUTHOR]));
                    $context->local()->addVal('type_hasdef', true);
                    $context->local()->addVal('type_def', $old_content[Database::PUBLICATION_TYPE]);
                    $context->local()->addVal('department_hasdef', true);
                    $context->local()->addVal('department_def', $old_content[Database::PUBLICATION_DEPARTMENT]);
                    $context->local()->addVal('rlyear_hasdef', true);
                    $context->local()->addVal('rlyear_def', $old_content[Database::PUBLICATION_RLYEAR]);
                    $context->local()->addVal('title_hasdef', true);
                    $context->local()->addVal('title_def', $old_content[Database::PUBLICATION_TITLE]);
                    $context->local()->addVal('id_def', $old_content->getID());
                    $context->local()->addVal('url_def', $old_content->url);

                    $context->local()->addVal('file_def', $old_content->file);

                    Debug::show('Edit old content');
                    Debug::show($old_content[Database::PUBLICATION_DESCRIPTION]);
                    Debug::show(str_replace(' ', '_', $old_content[Database::PUBLICATION_AUTHOR]));
                    Debug::show($old_content[Database::PUBLICATION_TYPE]);
                    Debug::show($old_content[Database::PUBLICATION_TITLE]);
                    Debug::show($old_content[Database::PUBLICATION_DEPARTMENT]);
                    Debug::show($old_content[Database::PUBLICATION_RLYEAR]);
                    $context->local()->addVal(InterfaceValues::PAGE_TITLE, 'Edit publication \''. $old_content[Database::PUBLICATION_TITLE].'\'');

                }

                 break;
            }

	    case 'pages':
		$tpl = 'support/pages.twig';
		break;

	    case 'contexts':
		$tpl = 'support/contexts.twig';
		break;

	    case 'roles':
		$tpl = 'support/roles.twig';
		break;

	    case 'users':
		$tpl = 'support/users.twig';
		break;

	    case 'info':
		$_SERVER['PHP_AUTH_PW'] = '*************'; # hide the password in case it is showing.
	        phpinfo();
		exit;

	    case 'edit' : // Edit something - at the moment just a User
	        if (count($rest) < 3)
		{
		    (new Web)->bad();
		}
	        $kind = $rest[1];
                $obj = $context->load($kind, $rest[2]);
                if (!is_object($obj))
                {
                    (new Web)->bad();
                }
                if (($bid = $context->postpar('bean', '')) != '')
                { # this is a post
                    if ($bid != $obj->getID())
                    { # something odd...
                        (new Web)->bad();
                    }
                    $obj->edit($context);
                    // The edit call might divert to somewhere else so sometimes we may not get here.
                }
		$context->local()->addval($kind, $obj);
		$tpl = 'support/edit'.$kind.'.twig';
		break;

	    default :
		break;
	    }
	    return $tpl;
	}
    }
?>
