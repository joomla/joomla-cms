<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

?>
<?php if ($this->params->get('show_articles')) : ?>
<div class="com-contact__articles contact-articles">
	<ul class="list-unstyled">
		<?php foreach ($this->item->articles as $article) : ?>
			<li>
				<?php echo HTMLHelper::_('link', Route::_(RouteHelper::getArticleRoute($article->slug, $article->catid, $article->language)), htmlspecialchars($article->title, ENT_COMPAT, 'UTF-8')); ?>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
<?php endif; ?>
