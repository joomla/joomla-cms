<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_breadcrumbs
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\WebAsset\WebAssetManager;

?>
<nav class="mod-breadcrumbs__wrapper" aria-label="<?php echo htmlspecialchars($module->title, ENT_QUOTES, 'UTF-8'); ?>">
    <ol class="mod-breadcrumbs breadcrumb px-3 py-2">
        <?php if ($params->get('showHere', 1)) : ?>
            <li class="mod-breadcrumbs__here float-start">
                <?php echo Text::_('MOD_BREADCRUMBS_HERE'); ?>&#160;
            </li>
        <?php else : ?>
            <li class="mod-breadcrumbs__divider float-start">
                <span class="divider icon-location icon-fw" aria-hidden="true"></span>
            </li>
        <?php endif; ?>

        <?php
        // Get rid of duplicated entries on trail including home page when using multilanguage
        for ($i = 0; $i < $count; $i++) {
            if ($i === 1 && !empty($list[$i]->link) && !empty($list[$i - 1]->link) && $list[$i]->link === $list[$i - 1]->link) {
                unset($list[$i]);
            }
        }

        // Find last and penultimate items in breadcrumbs list
        end($list);
        $last_item_key = key($list);
        prev($list);
        $penult_item_key = key($list);

        // Make a link if not the last item in the breadcrumbs
        $show_last = $params->get('showLast', 1);

        $class   = null;

        // Generate the trail
        foreach ($list as $key => $item) :
            if ($key !== $last_item_key) :
                if (!empty($item->link)) :
                    $breadcrumbItem = HTMLHelper::_('link', Route::_($item->link), '<span>' . $item->name . '</span>', ['class' => 'pathway']);
                else :
                    $breadcrumbItem = '<span>' . $item->name . '</span>';
                endif;
                echo '<li class="mod-breadcrumbs__item breadcrumb-item' . $class . '">' . $breadcrumbItem . '</li>';
            elseif ($show_last) :
                // Render last item if required.
                $breadcrumbItem = '<span>' . $item->name . '</span>';
                $class          = ' active';
                echo '<li class="mod-breadcrumbs__item breadcrumb-item' . $class . '">' . $breadcrumbItem . '</li>';
            endif;
        endforeach; ?>
    </ol>
    <?php

    // Structured data as JSON
    $data = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => []
    ];

    // Use an independent counter for positions. E.g. if Heading items in pathway.
    $itemsCounter = 0;

    // If showHome is disabled use the fallback $homeCrumb for startpage at first position.
    if (isset($homeCrumb)) {
        $data['itemListElement'][] = [
                '@type'    => 'ListItem',
                'position' => ++$itemsCounter,
                'item'     => [
                        '@id'  => Route::_($homeCrumb->link, true, Route::TLS_IGNORE, true),
                        'name' => $homeCrumb->name,
                ],
        ];
    }

    foreach ($list as $key => $item) {
        // Only add item to JSON if it has a valid link, otherwise skip it.
        if (!empty($item->link)) {
            $data['itemListElement'][] = [
                    '@type'    => 'ListItem',
                    'position' => ++$itemsCounter,
                    'item'     => [
                            '@id'  => Route::_($item->link, true, Route::TLS_IGNORE, true),
                            'name' => $item->name,
                    ],
            ];
        } elseif ($key === $last_item_key) {
            // Add the last item (current page) to JSON, but without a link.
            // Google accepts items without a URL only as the current page.
            $data['itemListElement'][] = [
                    '@type'    => 'ListItem',
                    'position' => ++$itemsCounter,
                    'item'     => [
                            'name' => $item->name,
                    ],
            ];
        }
    }

    if ($itemsCounter) {
        /** @var WebAssetManager $wa */
        $wa = $app->getDocument()->getWebAssetManager();
        $wa->addInline('script', json_encode($data, JSON_UNESCAPED_UNICODE), [], ['type' => 'application/ld+json']);
    }
    ?>
</nav>
