<?php

/**
 * @package     Jed\Component\Jed\Administrator\Traits
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace Jed\Component\Jed\Administrator\Traits;

use Exception;
use Jed\Component\Jed\Administrator\MediaHandling\ImageSize;
use Jed\Component\Jed\Site\Helper\JedHelper;
use Jed\Component\Jed\Site\Service\Category;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Database\ParameterType;
use Michelf\Markdown;
use SimpleXMLElement;

/**
 * Utilities for working with extensions and extension categories
 *
 * @since   4.0.0
 */
trait ExtensionUtilities
{
    /**
     * Gets first paragraph of description as intro text
     *
     * @param   string  $d
     *
     * @return array
     *
     * @throws Exception
     * @since  4.0.0
     */
    public function splitDescription(string $d): array
    {
        // Remove images
        $d = preg_replace("/\!\[(.*)\]\((.*)\)/", '', $d);
        // Remove links
        $d = preg_replace("/\[(.*)\]\((.*)\)/", '', $d);
        $d = Markdown::defaultTransform($d);

        $clean = (stripslashes(trim($d)));
        $xml   = new SimpleXMLElement('<div>' . $clean . '</div>');
        $ps    = $xml->xpath('//p');

        if (count($ps) > 0) {
            $ret['intro'] = htmlspecialchars_decode($ps[0]->asXml());


            if (count($ps) === 1) {
                // No more text (but might contain non-paragraphed text see JDEV-628
                $ret['body'] = str_replace($d, '', $d);
            } else {
                // Remove first paragraph from the text
                $dom = dom_import_simplexml($ps[0]);
                $dom->parentNode->removeChild($dom);
                $ret['body'] = htmlspecialchars_decode(str_replace('<?xml version="1.0"?>', '', $xml->asXml()));
            }
        } else {
            $seperator = stristr($d, '<br>') ? '<br>' : '<br />';
            $bits      = explode($seperator, $d);

            $o            = array_shift($bits);
            $ret['intro'] = $o;
            $ret['body']  = implode('<br />', $bits);
        }

        return $ret;
    }

    /**
     * Gets current extension category and hierarchy of parents as string
     *
     * @param   int  $category_id
     *
     * @return string
     *
     * @since 4.0.0
     */
    public function getCategoryHierarchy(int $category_id): string
    {
        return LayoutHelper::render('category.hierarchy', [
            'categories' => $this->getCategoryHierarchyStack($category_id),
        ]);
    }

    /**
     * Get a stack of Category tables with the hierarchy leading to the target category (ordered root towards leaf node)
     *
     * @param   int  $catId  The category ID to search for
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public function getCategoryHierarchyStack(int $catId): array
    {
        $stack      = [];
        $catService = new Category();
        $rootNode   = $catService->get('root');
        $cat        = $catService->get($catId);

        do {
            if ($cat === null) {
                return $stack;
            }

            array_unshift($stack, $cat);

            $cat = $cat->getParent();
        } while ($cat !== null && $cat->id != $rootNode->id);

        return $stack;
    }

    /**
     * Get Developer Name from jed_developers table
     *
     * @since 4.0.0
     *
     */
    public function getDeveloperName(int $uid): string
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
                    ->select('a.developer_name')
                    ->from($db->quoteName('#__jed_developers', 'a'))
                    ->where('a.user_id = :uid')
                    ->bind(':uid', $uid, ParameterType::INTEGER);

        return $db->setQuery($query)->loadResult();
    }

    /**
     * Get varied data for extension, i.e. fields for free, fields for paid
     *
     * @param   int       $extension_id
     * @param   int|null  $supply_option_type
     *
     * @return  array
     *
     * @throws  Exception
     * @since   4.0.0
     */
    public function getVariedData(int $extension_id, int $supply_option_type = null): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
                    ->select('supply_options.title AS supply_type, a.*')
                    ->from($db->quoteName('#__jed_extension_varied_data', 'a'))
                    ->leftJoin(
                        $db->quoteName('#__jed_extension_supply_options', 'supply_options')
                        . ' ON ' . $db->quoteName('supply_options.id') . ' = ' . $db->quoteName('a.supply_option_id')
                    )
                    ->where($db->quoteName('extension_id') . ' = :extension_id')
                    ->bind(':extension_id', $extension_id, ParameterType::INTEGER);

        if (($supply_option_type ?? 0) > 0) {
            $query
                ->where($db->quoteName('supply_option_id') . ' = :supply_option_type')
                ->bind(':supply_option_type', $supply_option_type, ParameterType::INTEGER);
        }

        $result = $db->setQuery($query)->loadObjectList();

        foreach ($result as $variedDatum) {
            $supply = $variedDatum->supply_type;

            if (!empty($variedDatum->logo)) {
                $variedDatum->logo = JedHelper::formatImage($variedDatum->logo, ImageSize::LARGE);
            }

            if ($variedDatum->is_default_data == 1 && empty($variedDatum->intro_text)) {
                $split_data = $this->splitDescription($variedDatum->description);

                if (!is_null($split_data)) {
                    $variedDatum->intro_text  = $split_data['intro'];
                    $variedDatum->description = $split_data['body'] . Markdown::defaultTransform($variedDatum->description);
                }
            } else {
                $variedDatum->intro_text  = Markdown::defaultTransform($variedDatum->intro_text);
                $variedDatum->description = Markdown::defaultTransform($variedDatum->description);
            }

            $retval[$supply] = $variedDatum;
        }

        return $retval;
    }
}
