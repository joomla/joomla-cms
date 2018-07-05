<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

/**
 * Supports a modal selector.
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class ModalField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'Modal';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $layout = 'joomla.form.field.modal';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getInput()
	{
		if (empty($this->layout))
		{
			throw new \UnexpectedValueException(sprintf('%s has no layout assigned.', $this->name));
		}

		return $this->getRenderer($this->layout)->render($this->getLayoutData());
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();

		$extraData = [
			'allowed' => $this->getAllowed(),
			'urls'    => $this->getUrls(),
			'text'    => $this->getText(),
			'title'   => $this->getSelectedTitle(),
			'attribs' => $this->getAttributes(),
		];

		return array_merge($data, $extraData);
	}

	/**
	 * Method to get the operations allowed for this field.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getAllowed()
	{
		return [
			'new'    => ((string) $this->element['new'] == 'true'),
			'edit'   => ((string) $this->element['edit'] == 'true'),
			'clear'  => ((string) $this->element['clear'] != 'false'),
			'select' => ((string) $this->element['select'] != 'false'),
		];
	}

	/**
	 * Method to get the urls used by this field.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected abstract function getUrls();

	/**
	 * Method to get the strings used by this field.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getText()
	{
		return [
			'button-select'      => Text::_('JSELECT'),
			'button-create'      => Text::_('JACTION_CREATE'),
			'button-edit'        => Text::_('JACTION_EDIT'),
			'button-clear'       => Text::_('JCLEAR'),
			'modal-button-close' => Text::_('JLIB_HTML_BEHAVIOR_CLOSE'),
			'modal-button-save'  => Text::_('JSAVE'),
			'modal-button-apply' => Text::_('JAPPLY'),
		];
	}

	/**
	 * Method to get the title of the currently selected item.
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected abstract function getSelectedTitle();

	/**
	 * Method to get attributes for the html tag.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getAttributes()
	{
		return [];
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   3.4
	 */
	protected function getLabel()
	{
		return str_replace($this->id, $this->id . '_id', parent::getLabel());
	}
}
