<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_newsflash
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>

<ul class="newsflash-horiz<?php echo $params->get('moduleclass_sfx'); ?>">
<?php for ($i = 0, $n = count($list); $i < $n; $i ++) :

	echo '<li>';
	modNewsFlashHelper::renderItem($list[$i], $params, $access);

	if ($n > 1 && (($i < $n - 1) || $params->get('showLastSeparator'))) : ?>

	<span class="article_separator">&nbsp;</span>

	<?php endif; ?>
	</li>
<?php endfor; ?>

</ul>