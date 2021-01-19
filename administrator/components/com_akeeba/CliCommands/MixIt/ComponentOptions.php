<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\CliCommands\MixIt;

defined('_JEXEC') || die;

use Akeeba\Engine\Platform;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormField;

trait ComponentOptions
{
	private function getComponentOptions(bool $defaultValuesOnly = false): array
	{
		$output     = [];
		$fieldNames = [];

		$form = new Form('config');
		$form->loadFile(JPATH_ADMINISTRATOR . '/components/com_akeeba/config.xml', true, '//config');

		foreach ($form->getFieldsets() as $group => $fieldSetInfo)
		{
			$fields = $form->getFieldset($group);

			if (empty($fields))
			{
				continue;
			}

			foreach ($fields as $fieldName => $v)
			{
				if (!is_object($v) || !($v instanceof FormField))
				{
					continue;
				}

				if (substr((string) $v->type, -5) === 'Rules')
				{
					continue;
				}

				if (in_array(strtolower((string) $v->type), ['hidden', 'rules', 'spacer']))
				{
					continue;
				}

				$fieldNames[$fieldName] = $v->value ?? null;
			}
		}

		if (!$defaultValuesOnly)
		{
			foreach ($fieldNames as $k => $default)
			{
				$output[$k] = Platform::getInstance()->get_platform_configuration_option($k, $default);
			}
		}

		return $output;
	}
}
