<?php
/**
 * Declares the default controller (DisplayController) for com_cronjobs
 *
 * @package       Joomla.Administrator
 * @subpackage    com_cronjobs
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GPL v3
 */

namespace Joomla\Component\Cronjobs\Administrator\Controller;

// Restrict direct access
\defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;


/**
 * Default controller for com_cronjobs
 *
 * @since __DEPLOY_VERSION__
 */
class DisplayController extends BaseController
{
	/**
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	protected $default_view = 'cronjobs';

	/**
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link InputFilter::clean()}.
	 *
	 * @return BaseController|boolean  Returns either a BaseController object to support chaining, or false on failure
	 *
	 * @throws Exception
	 * @since  __DEPLOY_VERSION__
	 */
	public function display($cachable = false, $urlparams = array())
	{
		$layout = $this->input->get('layout', 'default');
		$id = $this->input->getInt('id');

		// Check for edit form.
		if ($layout == 'edit' && !$this->checkEditId('com_cronjobs.edit.cronjob', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			if (!\count($this->app->getMessageQueue()))
			{
				$this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
			}

			$this->setRedirect(Route::_('index.php?option=com_cronjobs&view=cronjobs'));

			return false;
		}

		// Let the parent method take over
		return parent::display($cachable, $urlparams);
	}
}
