<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die();

use Joomla\CMS\Form\FormHelper;

if (class_exists('JFormFieldUrlencoded'))
{
	return;
}

FormHelper::loadFieldClass('text');

class JFormFieldUrlencoded extends JFormFieldText
{
	protected function getInput()
	{
		$this->value = urlencode($this->value);

		return parent::getInput();
	}
}
