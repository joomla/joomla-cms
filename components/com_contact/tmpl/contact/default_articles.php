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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

?>
<?php if ($this->params->get('show_articles')) : ?>
<div class="com-contact__articles contact-articles">
    <?php if ($this->params->get('show_articles_introimage') || $this->params->get('show_articles_introtext')) :
        foreach ($this->item->articles as $article) :
            if ($this->params->get('show_articles_introimage')) :
                $images     = json_decode($article->images);
                $layoutAttr = [
                    'src' => $images->image_intro,
                    'alt' => empty($images->image_intro_alt) && empty($images->image_intro_alt_empty) ? false : $images->image_intro_alt,
                ];
            endif;
        ?>
            <article class="contact-article" itemscope itemtype="https://schema.org/BlogPosting">
                <?php if ($this->params->get('show_articles_introimage') && !empty($images->image_intro)) : ?>
                    <figure class="contact-article__image">
                        <?php echo LayoutHelper::render('joomla.html.image', array_merge($layoutAttr, ['itemprop' => 'thumbnail'])); ?>
                        <?php if (isset($images->image_intro_caption) && $images->image_intro_caption !== '') : ?>
                            <figcaption class="caption"><?php echo $this->escape($images->image_intro_caption); ?></figcaption>
                        <?php endif; ?>
                    </figure>
                <?php endif; ?>
                <div class="contact-article__content" itemprop="abstract">
                    <h4 class="article-title" itemprop="name">
                        <?php echo HTMLHelper::_('link', Route::_(RouteHelper::getArticleRoute($article->slug, $article->catid, $article->language)), $this->escape($article->title)); ?>
                    </h4>

                    <?php if ($this->params->get('show_articles_introtext')) : ?>
                        <?php echo $article->introtext; ?>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    <?php else : ?>
        <ul class="list-unstyled">
            <?php foreach ($this->item->articles as $article) : ?>
                <li>
                    <?php echo HTMLHelper::_('link', Route::_(RouteHelper::getArticleRoute($article->slug, $article->catid, $article->language)), htmlspecialchars($article->title, ENT_COMPAT, 'UTF-8')); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
<?php endif; ?>
