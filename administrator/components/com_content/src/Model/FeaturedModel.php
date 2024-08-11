<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
use Joomla\Database\QueryInterface;

\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Methods supporting a list of featured article records.
 *
 * @since  1.6
 *
 * @deprecated  6.0 will be removed in 7.0
 *              Use \Joomla\Component\Conent\Administrator\Model\ArticlesModel&amp;featured=1 instead
 */
class FeaturedModel extends ArticlesModel
{
}
