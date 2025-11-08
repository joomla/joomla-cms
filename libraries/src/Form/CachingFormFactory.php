<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2025 Open Source Matters
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form;

use RuntimeException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Factory that creates and caches Form instances.
 *
 * @since  6.0.0
 */
class CachingFormFactory extends FormFactory
{
	/**
	 * Cache of created form instances.
	 *
	 * @var  Form[]
	 */
	private array $cache = [];

	/**
	 * Creates or returns a cached instance of a form.
	 *
	 * @param   string       $name     The name of the form.
	 * @param   string|null  $data     The XML file path or XML string to load.
	 * @param   array        $options  Optional form options.
	 * @param   bool         $replace  Whether to replace existing fields.
	 * @param   string|null  $xpath    XPath to search for fields.
	 *
	 * @return  Form
	 *
	 * @throws  RuntimeException
	 *
	 * @since   6.0.0
	 */
	public function createForm(
		string $name,
		?string $data = null,
		array $options = [],
		bool $replace = true,
		?string $xpath = null
	): Form {
		if (isset($this->cache[$name]))
		{
			return $this->cache[$name];
		}

		$form = parent::createForm($name, $options);

		if ($data)
		{
			if (str_starts_with($data, '<'))
			{
				if (!$form->load($data, $replace, $xpath))
				{
					throw new RuntimeException(sprintf('%s() could not load XML form data', __METHOD__));
				}
			}
			else
			{
				if (!$form->loadFile($data, $replace, $xpath))
				{
					throw new RuntimeException(sprintf('%s() could not load form file: %s', __METHOD__, $data));
				}
			}
		}

		$this->cache[$name] = $form;

		return $form;
	}

	/**
	 * Clears the cached forms.
	 *
	 * @return void
	 */
	public function clearCache(): void
	{
		$this->cache = [];
	}
}
