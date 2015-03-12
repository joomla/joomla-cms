<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

abstract class JViewItem extends JViewCms
{
	protected $getItem = false;

	protected $getForm = false;

	public $item;

	protected $form;

	public function __construct($config = array())
	{
		$layout = $config['layout'];

		if ($layout == 'form')
		{
			$this->getForm = true;
			$this->getItem = true;
		}

		if($layout == 'item')
		{
			$this->getItem = true;
		}

		parent:: __construct($config);
	}

	public function render($tpl = null)
	{
		$model      = $this->getModel();

		if ($this->getItem && empty($this->item))
		{
			$this->item = $model->getItem();
		}

		if ($this->getForm && empty($this->form))
		{
			$this->form = $model->getForm();
			$this->prepareForm($model);
		}

		return parent::render($tpl);
	}

	protected function prepareForm($model)
	{
		$session = JFactory::getSession();

		$context = $model->getContext();
		$registry = $session->get('registry');

		$item = $registry->get($context.'.jform.data');
		$this->form->bind($item);
	}
}