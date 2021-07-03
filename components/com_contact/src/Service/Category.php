<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contact\Site\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Categories\Categories;

/**
 * Contact Component Category Tree
 *
 * @since  1.6
 */
class Category extends Categories
{
	/**
	 * Class constructor
	 *
	 * @param   array  $options  Array of options
	 *
	 * @since   1.6
	 */
	public function __construct($options = array())
	{
		$options['table']      = '#__contact_details';
		$options['extension']  = 'com_contact';
		$options['statefield'] = 'published';

		parent::__construct($options);
	}
}
