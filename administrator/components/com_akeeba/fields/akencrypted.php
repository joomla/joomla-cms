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

class JFormFieldAkencrypted extends JFormFieldText
{
	protected function getInput()
	{
		$this->value = $this->conditionalDecrypt($this->value);

		return parent::getInput();
	}

	private function conditionalDecrypt($value)
	{
		// If the Factory is not already loaded we have to load the
		if (!class_exists('Akeeba\Engine\Factory'))
		{
			if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php'))
			{
				return $value;
			}

			$container = \FOF30\Container\Container::getInstance('com_akeeba', [], 'admin');

			/** @var \Akeeba\Backup\Admin\Dispatcher\Dispatcher $dispatcher */
			$dispatcher = $container->dispatcher;

			try
			{
				$dispatcher->loadAkeebaEngine();
				$dispatcher->loadAkeebaEngineConfiguration();
			}
			catch (Exception $e)
			{
				return $value;
			}
		}

		$secureSettings = \Akeeba\Engine\Factory::getSecureSettings();

		return $secureSettings->decryptSettings($this->value);
	}
}
