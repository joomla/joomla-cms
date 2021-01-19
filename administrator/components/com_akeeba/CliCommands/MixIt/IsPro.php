<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\CliCommands\MixIt;

defined('_JEXEC') || die;

/**
 * Is this Akeeba Backup Pro?
 *
 * @since   7.5.0
 */
trait IsPro
{
	/**
	 * Caches whether this is the Pro version of the software.
	 *
	 * @var   null|bool
	 * @since 7.5.0
	 */
	private $isPro = null;

	/**
	 * is this the Professional version of the software?
	 *
	 * @return  bool
	 * @since   7.5.0
	 */
	private function isPro(): bool
	{
		if (!is_null($this->isPro))
		{
			return $this->isPro;
		}

		$componentFolder = JPATH_ADMINISTRATOR . '/components/com_akeeba';
		$this->isPro     = is_dir($componentFolder . '/AliceEngine');

		return $this->isPro;
	}
}
