<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Associations\Administrator\View\Association;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Associations\Administrator\Helper\AssociationsHelper;
use Joomla\Component\Associations\Administrator\Model\AssociationModel;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * View class for a list of articles.
 *
 * @since  3.7.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Selected item type properties.
	 *
	 * @var    Registry
	 *
	 * @since  3.7.0
	 */
	public $itemType = null;

	/**
	 * Application instance
	 *
	 * @var    CMSApplication
	 * @since  3.7.0
	 */
	protected $app;

	/**
	 * Form
	 *
	 * @var    Form
	 * @since  3.7.0
	 */
	protected $form;

	/**
	 * Reference ID
	 *
	 * @var    integer
	 * @since  3.7.0
	 */
	protected $referenceId;

	/**
	 * The type of association
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	protected $type;

	/**
	 * The type supports
	 *
	 * @var    array
	 * @since  3.7.0
	 */
	protected $typeSupports = [];

	/**
	 * Set if save 2 copy
	 *
	 * @var    boolean
	 * @since  3.7.0
	 */
	protected $save2copy = false;

	/**
	 * The extension name
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	protected $extensionName;

	/**
	 * The type name
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	protected $typeName;

	/**
	 * The type item
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	protected $itemtype;

	/**
	 * The reference language
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	protected $referenceLanguage;

	/**
	 * The reference title
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	protected $referenceTitle;

	/**
	 * The editing URL
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	protected $editUri;

	/**
	 * The target ID
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	protected $targetId;

	/**
	 * The target language
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	protected $targetLanguage;

	/**
	 * The target source
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	protected $defaultTargetSrc;

	/**
	 * The target action
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	protected $targetAction;

	/**
	 * The target title
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	protected $targetTitle;

	/**
	 * An array of items
	 *
	 * @var    array
	 *
	 * @since  3.7.0
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var    Pagination
	 *
	 * @since  3.7.0
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var    CMSObject
	 *
	 * @since  3.7.0
	 */
	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 *
	 * @throws  Exception
	 */
	public function display($tpl = null)
	{
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		/** @var AssociationModel $model */
		$model             = $this->getModel();
		$this->app         = Factory::getApplication();
		$this->form        = $model->getForm();
		$input             = $this->app->input;
		$this->referenceId = $input->get('id', 0, 'int');

		list($extensionName, $typeName) = explode('.', $input->get('itemtype', '', 'string'), 2);

		$extension = AssociationsHelper::getSupportedExtension($extensionName);
		$types     = $extension->get('types');

		if (array_key_exists($typeName, $types))
		{
			$this->type         = $types[$typeName];
			$this->typeSupports = [];
			$details            = $this->type->get('details');
			$this->save2copy    = false;

			if (array_key_exists('support', $details))
			{
				$support            = $details['support'];
				$this->typeSupports = $support;
			}

			if (!empty($this->typeSupports['save2copy']))
			{
				$this->save2copy = true;
			}
		}

		$this->extensionName = $extensionName;
		$this->typeName      = $typeName;
		$this->itemtype      = $extensionName . '.' . $typeName;

		$languageField = AssociationsHelper::getTypeFieldName($extensionName, $typeName, 'language');
		$referenceId   = $input->get('id', 0, 'int');
		$reference     = ArrayHelper::fromObject(AssociationsHelper::getItem($extensionName, $typeName, $referenceId));

		$this->referenceLanguage = $reference[$languageField];
		$this->referenceTitle    = AssociationsHelper::getTypeFieldName($extensionName, $typeName, 'title');

		// Check for special case category
		$typeNameExploded = explode('.', $typeName);

		if (array_pop($typeNameExploded) === 'category')
		{
			$this->typeName = 'category';

			if ($typeNameExploded)
			{
				$extensionName .= '.' . implode('.', $typeNameExploded);
			}

			$options = [
				'option'    => 'com_categories',
				'view'      => 'category',
				'extension' => $extensionName,
				'tmpl'      => 'component',
			];
		}
		else
		{
			$options = [
				'option'    => $extensionName,
				'view'      => $typeName,
				'extension' => $extensionName,
				'tmpl'      => 'component',
			];
		}

		// Reference and target edit links.
		$this->editUri = 'index.php?' . http_build_query($options);

		// Get target language.
		$this->targetId         = '0';
		$this->targetLanguage   = '';
		$this->defaultTargetSrc = '';
		$this->targetAction     = '';
		$this->targetTitle      = '';

		if ($target = $input->get('target', '', 'string'))
		{
			$matches              = preg_split("#[\:]+#", $target);
			$this->targetAction   = $matches[2];
			$this->targetId       = $matches[1];
			$this->targetLanguage = $matches[0];
			$this->targetTitle    = AssociationsHelper::getTypeFieldName($extensionName, $typeName, 'title');
			$task                 = $typeName . '.' . $this->targetAction;

			/*
			 * Let's put the target src into a variable to use in the javascript code
			 *  to avoid race conditions when the reference iframe loads.
			 */
			$document = Factory::getDocument();
			$document->addScriptOptions('targetSrc', Route::_($this->editUri . '&task=' . $task . '&id=' . (int) $this->targetId));
			$this->form->setValue('itemlanguage', '', $this->targetLanguage . ':' . $this->targetId . ':' . $this->targetAction);
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since  3.7.0
	 *
	 * @throws  Exception
	 */
	protected function addToolbar()
	{
		// Hide main menu.
		Factory::getApplication()->input->set('hidemainmenu', 1);

		$helper = AssociationsHelper::getExtensionHelper($this->extensionName);
		$title  = $helper->getTypeTitle($this->typeName);

		$languageKey = strtoupper($this->extensionName . '_' . $title . 'S');

		if ($this->typeName === 'category')
		{
			$languageKey = strtoupper($this->extensionName) . '_CATEGORIES';
		}

		ToolbarHelper::title(Text::sprintf('COM_ASSOCIATIONS_TITLE_EDIT', Text::_($this->extensionName), Text::_($languageKey)), 'contract assoc');

		$bar = Toolbar::getInstance('toolbar');

		$bar->appendButton(
			'Custom', '<button onclick="Joomla.submitbutton(\'reference\')" '
			. 'class="btn btn-sm btn-success"><span class="icon-apply" aria-hidden="true"></span>'
			. Text::_('COM_ASSOCIATIONS_SAVE_REFERENCE') . '</button>', 'reference'
		);

		$bar->appendButton(
			'Custom', '<button onclick="Joomla.submitbutton(\'target\')" '
			. 'class="btn btn-sm btn-success"><span class="icon-apply" aria-hidden="true"></span>'
			. Text::_('COM_ASSOCIATIONS_SAVE_TARGET') . '</button>', 'target'
		);

		if ($this->typeName === 'category' || $this->extensionName === 'com_menus' || $this->save2copy === true)
		{
			ToolbarHelper::custom('copy', 'copy.png', '', 'COM_ASSOCIATIONS_COPY_REFERENCE', false);
		}

		ToolbarHelper::cancel('association.cancel', 'JTOOLBAR_CLOSE');
		ToolbarHelper::help('JHELP_COMPONENTS_ASSOCIATIONS_EDIT');

		\JHtmlSidebar::setAction('index.php?option=com_associationsss');
//		HTMLHelper::_('sidebar.setAction', 'index.php?option=com_associations');
	}
}
