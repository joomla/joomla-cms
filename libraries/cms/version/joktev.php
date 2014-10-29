<?php
/**
 * @package    Joomla.Site
 * @author     juuntos.org
 * @copyright  Copyleft.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Version information class for the Joomla CMS.
 *
 * @package  Joomla.Site
 * @since    1.0
 */
final class JVjokte
{
	/** @var  string  Product name. */
	public $PRODUCTO = 'Jokte!';

	/** @var  string  Release version. */
	public $LIBERACION = '1.3';

	/** @var  string  Maintenance version. */
	public $DESARROLLO = '5';

	/** @var  string  Development STATUS. */
	public $ESTADO = 'Estable';

	/** @var  string  Build number. */
	public $COMPILACION = '';

	/** @var  string  Code name. */
	public $NOMBRECODIGO = 'Jeyuu';

	/** @var  string  Release date. */
	public $LIBDIA = '03-Nov-2014';

	/** @var  string  Release time. */
	public $LIBHORA = '17:00';

	/** @var  string  Release timezone. */
	public $LIBTZ = 'GMT';

	/** @var  string  Copyright Notice. */
	public $COPYR = 'Copyleft juuntos.org.';

	/** @var  string  Link text. */
	public $URLJ = '<a href="http://www.jokte.org">Jokte!</a> is Free Software released under the GNU General Public License.';

	/**
	 * Compares two a "PHP standardized" version number against the current Joomla version.
	 *
	 * @param   string  $minimum  The minimum version of the Joomla which is compatible.
	 *
	 * @return  bool    True if the version is compatible.
	 *
	 * @see     http://www.php.net/version_compare
	 * @since   1.0
	 */
	public function esCompatible($minimum)
	{
		return version_compare(VJOKTE, $minimum, 'ge');
	}

	/**
	 * Method to get the help file version.
	 *
	 * @return  string  Version suffix for help files.
	 *
	 * @since   1.0
	 */
	public function getVersionAyuda()
	{
		return '.' . str_replace('.', '', $this->LIBERACION);
	}

	/**
	 * Gets a "PHP standardized" version string for the current Joomla.
	 *
	 * @return  string  Version string.
	 *
	 * @since   1.5
	 */
	public function getVersionCorta()
	{
		return $this->LIBERACION . '.' . $this->DESARROLLO;
	}

	/**
	 * Gets a version string for the current Joomla with all release information.
	 *
	 * @return  string  Complete version string.
	 *
	 * @since   1.5
	 */
	public function getVersionLarga()
	{
		return $this->PRODUCTO . ' ' . $this->LIBERACION . '.' . $this->DESARROLLO . ' '
				. $this->ESTADO . ' [ ' . $this->NOMBRECODIGO . ' ] ' . $this->LIBDIA . ' '
				. $this->LIBHORA . ' ' . $this->LIBTZ;
	}

	/**
	 * Returns the user agent.
	 *
	 * @param   string  $component    Name of the component.
	 * @param   bool    $mask         Mask as Mozilla/5.0 or not.
	 * @param   bool    $add_version  Add version afterwards to component.
	 *
	 * @return  string  User Agent.
	 *
	 * @since   1.0
	 */
	public function getUserAgente($component = null, $mask = false, $add_version = true)
	{
		if ($component === null)
		{
			$component = 'Framework';
		}

		if ($add_version)
		{
			$component .= '/' . $this->LIBERACION;
		}

		// If masked pretend to look like Mozilla 5.0 but still identify ourselves.
		if ($mask)
		{
			return 'Mozilla/5.0 ' . $this->PRODUCTO . '/' . $this->LIBERACION . '.' . $this->DESARROLLO . ($component ? ' ' . $component : '');
		}
		else
		{
			return $this->PRODUCTO . '/' . $this->LIBERACION . '.' . $this->DESARROLLO . ($component ? ' ' . $component : '');
		}
	}
}
