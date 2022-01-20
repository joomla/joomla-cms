<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\Input\Input;
use Joomla\Utilities\ArrayHelper;

/**
 * Articles list controller class.
 *
 * @since  1.6
 */
class ArticlesController extends AdminController
{
	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The Application for the dispatcher
	 * @param   Input                $input    Input
	 *
	 * @since   3.0
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		// Articles default form can come from the articles or featured view.
		// Adjust the redirect view on the value of 'view' in the request.
		if ($this->input->get('view') == 'featured')
		{
			$this->view_list = 'featured';
		}

		$this->registerTask('unfeatured', 'featured');
	}

	/**
	 * Method to toggle the featured setting of a list of articles.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function featured()
	{
		// Check for request forgeries
		$this->checkToken();

		$user        = $this->app->getIdentity();
		$ids         = $this->input->get('cid', array(), 'array');
		$values      = array('featured' => 1, 'unfeatured' => 0);
		$task        = $this->getTask();
		$value       = ArrayHelper::getValue($values, $task, 0, 'int');
		$redirectUrl = 'index.php?option=com_content&view=' . $this->view_list . $this->getRedirectToListAppend();

		// Access checks.
		foreach ($ids as $i => $id)
		{
			if (!$user->authorise('core.edit.state', 'com_content.article.' . (int) $id))
			{
				// Prune items that you can't change.
				unset($ids[$i]);
				$this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 'notice');
			}
		}

		if (empty($ids))
		{
			$this->app->enqueueMessage(Text::_('JERROR_NO_ITEMS_SELECTED'), 'error');

			$this->setRedirect(Route::_($redirectUrl, false));

			return;
		}

		// Get the model.
		/** @var \Joomla\Component\Content\Administrator\Model\ArticleModel $model */
		$model = $this->getModel();

		// Publish the items.
		if (!$model->featured($ids, $value))
		{
			$this->setRedirect(Route::_($redirectUrl, false), $model->getError(), 'error');

			return;
		}

		if ($value == 1)
		{
			$message = Text::plural('COM_CONTENT_N_ITEMS_FEATURED', count($ids));
		}
		else
		{
			$message = Text::plural('COM_CONTENT_N_ITEMS_UNFEATURED', count($ids));
		}

		$this->setRedirect(Route::_($redirectUrl, false), $message);
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Article', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Method to get the JSON-encoded amount of published articles
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function getQuickiconContent()
	{
		$model = $this->getModel('articles');

		$model->setState('filter.published', 1);

		$amount = (int) $model->getTotal();

		$result = [];

		$result['amount'] = $amount;
		$result['sronly'] = Text::plural('COM_CONTENT_N_QUICKICON_SRONLY', $amount);
		$result['name'] = Text::plural('COM_CONTENT_N_QUICKICON', $amount);

		echo new JsonResponse($result);
	}
}
