<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Field to select Content History from a modal list.
 *
 * @since  3.2
 */
class JFormFieldContenthistory extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.2
	 */
	public $type = 'ContentHistory';

	/**
	 * Layout to render the label
	 *
	 * @var  string
	 */
	protected $layout = 'joomla.form.field.contenthistory';

	/**
	 * Get the data that is going to be passed to the layout
	 *
	 * @return  array
	 */
	public function getLayoutData()
	{
		// Get the basic field data
		$data = parent::getLayoutData();

		$typeId = JTable::getInstance('Contenttype')->getTypeId($this->element['data-typeAlias']);
		$itemId = $this->form->getValue('id');
		$label  = JText::_('JTOOLBAR_VERSIONS');

		$link   = 'index.php?option=com_contenthistory&amp;view=history&amp;layout=modal&amp;tmpl=component&amp;field='
			. $this->id . '&amp;item_id=' . $itemId . '&amp;type_id=' . $typeId . '&amp;type_alias='
			. $this->element['data-typeAlias'] . '&amp;' . JSession::getFormToken() . '=1';

		$extraData = array(
				'type' => $typeId,
				'item' => $itemId,
				'label' => $label,
				'link' => $link,
		);

		return array_merge($data, $extraData);
	}

	/**
	 * Method to get the content history field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.2
	 */
	protected function getInput()
	{
		if (empty($this->layout))
		{
			throw new UnexpectedValueException(sprintf('%s has no layout assigned.', $this->name));
		}

		return $this->getRenderer($this->layout)->render($this->getLayoutData());
	}
}
