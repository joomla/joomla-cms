<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contenthistory\Administrator\View\History;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarFactoryInterface;

/**
 * View class for a list of contenthistory.
 *
 * @since  3.2
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * An array of items
	 *
	 * @var  array
	 */
	protected $items;

	/**
	 * The model state
	 *
	 * @var  Pagination
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var  \Joomla\CMS\Object\CMSObject
	 */
	protected $state;

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->toolbar = $this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page toolbar.
	 *
	 * @return  Toolbar
	 *
	 * @since  4.0.0
	 */
	protected function addToolbar(): Toolbar
	{
		/** @var Toolbar $toolbar */
		$toolbar = Factory::getContainer()->get(ToolbarFactoryInterface::class)->createToolbar('toolbar');

		// Cache a session token for reuse throughout.
		$token = Session::getFormToken();

		// Clean up input to ensure a clean url.
		$aliasArray = explode('.', $this->state->item_id);
		$option     = $aliasArray[1] == 'category'
			? 'com_categories&amp;extension=' . implode('.', array_slice($aliasArray, 0, count($aliasArray) - 2))
			: $aliasArray[0];
		$filter     = InputFilter::getInstance();
		$task       = $filter->clean($aliasArray[1], 'cmd') . '.loadhistory';

		// Build the final urls.
		$loadUrl    = Route::_('index.php?option=' . $filter->clean($option, 'cmd') . '&amp;task=' . $task . '&amp;' . $token . '=1');
		$previewUrl = Route::_('index.php?option=com_contenthistory&view=preview&layout=preview&tmpl=component&' . $token . '=1');
		$compareUrl = Route::_('index.php?option=com_contenthistory&view=compare&layout=compare&tmpl=component&' . $token . '=1');

		$toolbar->basicButton('load')
			->attributes(['data-url' => $loadUrl])
			->icon('icon-upload')
			->buttonClass('btn btn-success')
			->text('COM_CONTENTHISTORY_BUTTON_LOAD')
			->listCheck(true);

		$toolbar->basicButton('preview')
			->attributes(['data-url' => $previewUrl])
			->icon('icon-search')
			->text('COM_CONTENTHISTORY_BUTTON_PREVIEW')
			->listCheck(true);

		$toolbar->basicButton('compare')
			->attributes(['data-url' => $compareUrl])
			->icon('icon-search-plus')
			->text('COM_CONTENTHISTORY_BUTTON_COMPARE')
			->listCheck(true);

		$toolbar->basicButton('keep')
			->task('history.keep')
			->buttonClass('btn btn-inverse')
			->icon('icon-lock')
			->text('COM_CONTENTHISTORY_BUTTON_KEEP')
			->listCheck(true);

		$toolbar->basicButton('delete')
			->task('history.delete')
			->buttonClass('btn btn-danger')
			->icon('icon-times')
			->text('COM_CONTENTHISTORY_BUTTON_DELETE')
			->listCheck(true);

		return $toolbar;
	}
}
