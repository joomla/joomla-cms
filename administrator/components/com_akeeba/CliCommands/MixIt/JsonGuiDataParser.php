<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\CliCommands\MixIt;

defined('_JEXEC') || die;

use Akeeba\Engine\Factory;

trait JsonGuiDataParser
{
	/**
	 * Parse the JSON GUI definition returned by Akeeba Engine into something I can use to provide information about
	 * the options.
	 *
	 * @return  array
	 *
	 * @since   7.5.0
	 */
	private function parseJsonGuiData(): array
	{
		$jsonGUIData = Factory::getEngineParamsProvider()->getJsonGuiDefinition();
		$guiData     = json_decode($jsonGUIData, true);

		$ret = [
			'engines'    => [],
			'installers' => [],
			'options'    => [],
		];

		// Parse engines
		foreach ($guiData['engines'] as $engineType => $engineRecords)
		{
			if (!isset($ret['engines'][$engineType]))
			{
				$ret['engines'][$engineType] = [];
			}

			foreach ($engineRecords as $engineName => $record)
			{
				$ret['engines'][$engineType][$engineName] = [
					'title'       => $record['information']['title'],
					'description' => $record['information']['description'],
				];

				foreach ($record['parameters'] as $key => $optionRecord)
				{
					$ret['options'][$key] = array_merge($optionRecord, [
						'section' => $record['information']['title'],
					]);
				}
			}
		}

		// Parse installers
		foreach ($guiData['installers'] as $installerName => $installerInfo)
		{
			$ret['installers'][$installerName] = $installerInfo['name'];
		}

		// Parse GUI sections
		foreach ($guiData['gui'] as $section => $options)
		{
			foreach ($options as $key => $optionRecord)
			{
				$ret['options'][$key] = array_merge($optionRecord, [
					'section' => $section,
				]);
			}
		}

		return $ret;
	}

	/**
	 * Flattens the option tree returned by exportToJson into an array with dotted notation for each option.
	 *
	 * @param   array   $rawOptions  The option tree
	 * @param   string  $prefix      Current prefix, used for recursion
	 *
	 * @return  array
	 * @since   7.5.0
	 */
	private function flattenOptions(array $rawOptions, string $prefix = ''): array
	{
		$ret = [];

		foreach ($rawOptions as $k => $v)
		{
			if (is_array($v))
			{
				$ret = array_merge($ret, $this->flattenOptions($v, $prefix . $k . '.'));

				continue;
			}

			$ret[$prefix . $k] = $v;
		}

		return $ret;
	}

	/**
	 * Get the information for an option record.
	 *
	 * @param   string  $key   The option key
	 * @param   array   $info  The array returned by parseJsonGuiData
	 *
	 * @return  array
	 *
	 * @since   7.5.0
	 */
	private function getOptionInfo(string $key, array &$info): array
	{
		$ret = [];

		if (!isset($info['options'][$key]))
		{
			return $ret;
		}

		$keyInfo = $info['options'][$key];

		$ret = [
			'title'        => $keyInfo['title'],
			'description'  => $keyInfo['description'],
			'section'      => $keyInfo['section'],
			'type'         => $keyInfo['type'],
			'default'      => $keyInfo['default'],
			'options'      => [],
			'optionTitles' => [],
			'limits'       => [],
		];

		switch ($keyInfo['type'])
		{
			case 'integer':
				if (isset($keyInfo['shortcuts']))
				{
					$ret['options'] = explode('|', $keyInfo['shortcuts']);
				}

				$ret['limits'] = [
					'min' => $keyInfo['min'],
					'max' => $keyInfo['max'],
				];
				break;

			case 'bool':
				$ret['type']    = 'integer';
				$ret['options'] = [0, 1];
				$ret['limits']  = [
					'min' => 0,
					'max' => 1,
				];
				break;

			case 'engine':
				$ret['type']         = 'enum';
				$ret['type']         = 'string';
				$ret['options']      = array_keys($info['engines'][$keyInfo['subtype']]);
				$ret['optionTitles'] = [];

				foreach ($info['engines'][$keyInfo['subtype']] as $k => $details)
				{
					$ret['optionTitles'][$k] = $details['title'];
				}

				break;

			case 'installer':
				$ret['type']         = 'enum';
				$ret['type']         = 'string';
				$ret['options']      = array_keys($info['installers']);
				$ret['optionTitles'] = $info['installers'];

				break;

			case 'enum':
				$ret['type']         = 'string';
				$ret['options']      = explode('|', $keyInfo['enumvalues']);
				$ret['optionTitles'] = explode('|', $keyInfo['enumkeys']);

				break;

			case 'hidden':
			case 'button':
			case 'separator':
				$ret['type'] = 'hidden';
				break;

			case 'string':
			case 'browsedir':
			case 'password':
			default:
				$ret['type'] = 'string';
				break;
		}

		return $ret;
	}

}
