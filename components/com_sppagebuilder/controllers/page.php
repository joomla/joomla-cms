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

class SppagebuilderControllerPage extends JControllerForm {

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function getModel($name = 'form', $prefix = '', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function save($key = null, $urlVar = null) {

		$user = JFactory::getUser();
		$app      = JFactory::getApplication();
		$input = $app->input;
		$Itemid = $input->get('Itemid', 0, 'INT');
		$root = JURI::base();
		$root = new JURI($root);
		$link = $root->getScheme() . '://' . $root->getHost();

		$model    = $this->getModel('Form');
		$data     = $this->input->post->get('jform', array(), 'array');
		$task     = $this->getTask();
		$context  = 'com_sppagebuilder.edit.page';
		$recordId = $data['id'];
		$output = array();

		//Authorized
		if (empty($recordId)) {
			$authorised = $user->authorise('core.create', 'com_sppagebuilder') || (count((array) $user->getAuthorisedCategories('com_sppagebuilder', 'core.create')));
		} else {
			$authorised = $user->authorise('core.edit', 'com_sppagebuilder') || $user->authorise('core.edit', 'com_sppagebuilder.page.' . $recordId) || ($user->authorise('core.edit.own', 'com_sppagebuilder.page.' . $recordId) && $data['created_by'] == $user->id);
		}

		if ($authorised !== true)
		{
			$output['status'] = false;
			$output['message'] = JText::_('JERROR_ALERTNOAUTHOR');
			echo json_encode($output);
			die();
		}

		// Check for request forgeries.
		$output['status'] = false;
		$output['message'] = JText::_('JINVALID_TOKEN');
		JSession::checkToken() or die(json_encode($output));

		$output['status'] = true;

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
			$url = $link . JRoute::_('index.php?option=com_sppagebuilder&view=form&layout=edit&tmpl=component&id=' . $recordId . '&Itemid=' . $Itemid);
			$output['redirect'] = $url;
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
			$output['redirect'] = $link . JRoute::_('index.php?option=com_sppagebuilder&view=form&layout=edit&tmpl=component&id=' . $recordId . '&Itemid=' . $Itemid);
			echo json_encode($output);
			die();
		}

		// Save succeeded, check-in the row.
		if ($model->checkin($data['id']) === false) {

			// Check-in failed, go back to the row and display a notice.
			$output['status'] = false;
			$output['message'] = JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());
			$output['redirect'] = $link . JRoute::_('index.php?option=com_sppagebuilder&view=form&layout=edit&tmpl=component&id=' . $recordId . '&Itemid=' . $Itemid);
			echo json_encode($output);
			die();
		}

		$output['status'] = true;
		$output['message'] = JText::_('COM_SPPAGEBUILDER_PAGE_SAVE_SUCCESS');

		// Redirect the user and adjust session state based on the chosen task.
		switch ($task) {
			case 'apply':
				// Set the row data in the session.
				$this->holdEditId($context, $recordId);
				$app->setUserState('com_sppagebuilder.edit.page.data', null);

				// Delete generated CSS file
				$css_folder_path = JPATH_ROOT . '/media/com_sppagebuilder/css';
				$css_file_path = $css_folder_path . '/page-'. $recordId .'.css';
				if(file_exists($css_file_path)) {
					JFile::delete($css_file_path);
				}

				// Redirect back to the edit screen.
				$output['redirect'] = $link . JRoute::_('index.php?option=com_sppagebuilder&view=form&layout=edit&tmpl=component&id=' . $recordId . '&Itemid=' . $Itemid);
				$output['id'] = $recordId;
				break;

			default:
				// Clear the row id and data in the session.
				$this->releaseEditId($context, $recordId);
				$app->setUserState('com_sppagebuilder.edit.page.data', null);

				// Redirect to the list screen.
				$output['redirect'] = $link . JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false);
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

	public function cancel($key = 'id') {
		parent::cancel($key);
		$return_url = JFactory::getApplication()->input->get('return_page',null,'base64');

		$this->setRedirect(base64_decode($return_url));
	}

}
