<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Article
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Content\Site\Helper\RouteHelper;

\defined('_JEXEC') or die;

/**
 * @var \Joomla\Plugins\Fields\Article\Extension\Article $this
 * @var \stdClass $field
 */

if (!\is_object($field->value) || !isset($field->value->id)) {
    return;
}

$article = $field->value;

if ($article->params->get('access-view') || $article->params->get('access-edit')) :
    $link = Route::_(RouteHelper::getArticleRoute($article->id, $article->catid, $article->language));
else :
    $menu   = Factory::getApplication()->getMenu();
    $active = $menu->getActive();
    $itemId = $active->id;
    $link   = new Uri(Route::_('index.php?option=com_users&view=login&Itemid=' . $itemId, false));
    $link->setVar('return', base64_encode(RouteHelper::getArticleRoute($article->id, $article->catid, $article->language)));
endif;
?>
<a href="<?php echo $link; ?>">
    <?php echo htmlspecialchars($article->title); ?>
</a>
<?php if ($article->params->get('isUnpublished')) : ?>
    <span class="badge bg-warning">
        <?php echo Text::_('UNPUBLISHED'); ?>
    </span>
<?php endif; ?>
