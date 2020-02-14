<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');
jimport('joomla.filesystem.file');
use Joomla\Utilities\ArrayHelper;

class SppagebuilderControllerPage extends JControllerForm {

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	protected function allowAdd($data = array())
	{
		$categoryId = ArrayHelper::getValue($data, 'catid', $this->input->getInt('filter_category_id'), 'int');
		$allow = null;

		if ($categoryId)
		{
			// If the category has been passed in the data or URL check it.
			$allow = JFactory::getUser()->authorise('core.create', 'com_sppagebuilder.category.' . $categoryId);
		}

		if ($allow === null)
		{
			// In the absense of better information, revert to the component permissions.
			return parent::allowAdd();
		}

		return $allow;
	}

	protected function allowEdit($data = array(), $key = 'id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user = JFactory::getUser();

		// Zero record (id:0), return component edit permission by calling parent controller method
		if (!$recordId)
		{
			return parent::allowEdit($data, $key);
		}

		// Check edit on the record asset (explicit or inherited)
		if ($user->authorise('core.edit', 'com_sppagebuilder.page.' . $recordId))
		{
			return true;
		}

		// Check edit own on the record asset (explicit or inherited)
		if ($user->authorise('core.edit.own', 'com_sppagebuilder.page.' . $recordId))
		{
			// Existing record already has an owner, get it
			$record = $this->getModel()->getItem($recordId);

			if (empty($record))
			{
				return false;
			}

			// Grant if current user is owner of the record
			return $user->id == $record->created_by;
		}

		return false;
	}

	public function save($key = null, $urlVar = null) {

		$output = array();
		// Check for request forgeries.
		$output['status'] = false;
		$output['message'] = JText::_('JINVALID_TOKEN');
		JSession::checkToken() or die(json_encode($output));

		$user 		= JFactory::getUser();
		$app      = JFactory::getApplication();
		$model    = $this->getModel();
		$data     = $this->input->post->get('jform', array(), 'array');
		$task     = $this->getTask();
		$context  = 'com_sppagebuilder.edit.page';
		$recordId = $data['id'];

		//Authorized
		if (empty($recordId)) {
			$authorised = $user->authorise('core.create', 'com_sppagebuilder') || (count((array) $user->getAuthorisedCategories('com_sppagebuilder', 'core.create')));
		} else {
			$authorised = $user->authorise('core.edit', 'com_sppagebuilder') || $user->authorise('core.edit', 'com_sppagebuilder.page.' . $recordId) || $user->authorise('core.edit', 'com_sppagebuilder.page.' . $recordId) || ($user->authorise('core.edit.own',   'com_sppagebuilder.page.' . $recordId) && $data['created_by'] == $user->id);
		}

		if ($authorised !== true)
		{
			$output['status'] = false;
			$output['message'] = JText::_('JERROR_ALERTNOAUTHOR');
			echo json_encode($output);
			die();
		}

		$output['status'] = true;

		// The save2copy task needs to be handled slightly differently.
		if ($task == 'save2copy') {
			// Check-in the original row.
			if ($model->checkin($data['id']) === false) {
				// Check-in failed, go back to the item and display a notice.
				$output['status'] = false;
				$output['message'] = JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());
				echo json_encode($output);
				die();
			}

			// Reset the ID and then treat the request as for Apply.
			$output['title'] = $model->pageGenerateNewTitle($data['title']);
			$data['id'] = 0;
			$task = 'apply';
		}


		// Validate the posted data.
		// This post is made up of two forms, one for the item and one for params.
		$form = $model->getForm($data);

		if (!$form) {
			$output['status'] = false;
			$output['message'] = $model->getError();
			$output['redirect'] = false;
			echo json_encode($output);
			die();
		}

		$data = $model->validate($form, $data);

		// Check for validation errors.
		if ($data === false) {
			// Get the validation messages.
			$errors = $model->getErrors();

			$output['status'] = false;
			$output['message'] = '';

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count((array) $errors); $i < $n && $i < 3; $i++) {
				if ($errors[$i] instanceof Exception) {
					$output['message'] .= $errors[$i]->getMessage();
				} else {
					$output['message'] .= $errors[$i];
				}
			}

			// Save the data in the session.
			$app->setUserState('com_sppagebuilder.edit.page.data', $data);

			// Redirect back to the edit screen.
			$output['redirect'] = 'index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId);
			echo json_encode($output);
			die();
		}

		// Attempt to save the data.
		if (!$model->save($data)) {

			// Save the data in the session.
			$app->setUserState('com_sppagebuilder.edit.page.data', $data);

			// Redirect back to the edit screen.
			$output['status'] = false;
			$output['message'] = JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError());
			$output['redirect'] = 'index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId);
			echo json_encode($output);
			die();
		}

		// Save succeeded, check-in the row.
		if ($model->checkin($data['id']) === false) {

			// Check-in failed, go back to the row and display a notice.
			$output['status'] = false;
			$output['message'] = JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());
			$output['redirect'] = 'index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId);
			echo json_encode($output);
			die();
		}

		$output['status'] = true;
		$output['message'] = JText::_('COM_SPPAGEBUILDER_PAGE_SAVE_SUCCESS');

		// Redirect the user and adjust session state based on the chosen task.
		switch ($task) {
			case 'apply':
				// Set the row data in the session.
				$recordId = $model->getState($this->context . '.id');
				$this->holdEditId($context, $recordId);
				$app->setUserState('com_sppagebuilder.edit.page.data', null);

				// Delete generated CSS file
				$css_folder_path = JPATH_ROOT . '/media/com_sppagebuilder/css';
				$css_file_path = $css_folder_path . '/page-'. $recordId .'.css';
				if(file_exists($css_file_path)) {
					JFile::delete($css_file_path);
				}

				// Redirect back to the edit screen.
				$output['redirect'] = 'index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId);
				$siteApp = JApplication::getInstance('site');
				$siteRouter = $siteApp->getRouter();
				$Itemid = SppagebuilderHelper::getMenuId($recordId);

				$preview = 'index.php?option=com_sppagebuilder&view=page&id=' . $recordId . $Itemid;
				$output['preview_url'] = str_replace('/administrator', '', $siteRouter->build($preview));

				$front_link = 'index.php?option=com_sppagebuilder&view=form&tmpl=componenet&layout=edit&id=' . $recordId . $Itemid;
				$output['frontend_editor_url'] = str_replace('/administrator', '', $siteRouter->build($front_link));
				$output['id'] = $recordId;
				break;

			default:
				// Clear the row id and data in the session.
				$this->releaseEditId($context, $recordId);
				$app->setUserState('com_sppagebuilder.edit.page.data', null);

				// Redirect to the list screen.
				$output['redirect'] = JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false);
				break;
		}

		if(isset($output['id']) && $output['id']){
			$css_file_path = JPATH_ROOT . "/media/sppagebuilder/css/page-{$output['id']}.css";
			if( file_exists( $css_file_path ) ) {
				unlink( $css_file_path );
			}
		}

		echo json_encode($output);
		die();
	}

	public function getMySections() {
		$model = $this->getModel();
		die($model->getMySections());
	}

	public function deleteSection(){
		$model = $this->getModel();
		$app = JFactory::getApplication();
		$input = $app->input;

		$id = $input->get('id', '', 'INT');

		die($model->deleteSection($id));
	}

	public function saveSection() {
		$model = $this->getModel();
		$app = JFactory::getApplication();
		$input = $app->input;

		$title = htmlspecialchars($input->get('title', '', 'STRING'));
		$section = $input->get('section', '', 'RAW');

		if($title && $section) {
			$section_id = $model->saveSection($title, $section);
			echo $section_id;
			die();
		} else {
			die('Failed');
		}
	}

}
