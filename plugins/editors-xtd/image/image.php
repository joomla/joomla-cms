<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.image
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Editor Image button
 *
 * @since  1.5
 */
class PlgButtonImage extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * Display the button.
     *
     * @param   string   $name    The name of the button to display.
     * @param   string   $asset   The name of the asset being edited.
     * @param   integer  $author  The id of the author owning the asset being edited.
     *
     * @return  CMSObject|false
     *
     * @since   1.5
     */
    public function onDisplay($name, $asset, $author)
    {
        $app       = Factory::getApplication();
        $doc       = $app->getDocument();
        $user      = Factory::getUser();
        $extension = $app->input->get('option');

        // For categories we check the extension (ex: component.section)
        if ($extension === 'com_categories') {
            $parts     = explode('.', $app->input->get('extension', 'com_content'));
            $extension = $parts[0];
        }

        $asset = $asset !== '' ? $asset : $extension;

        if (
            $user->authorise('core.edit', $asset)
            || $user->authorise('core.create', $asset)
            || (count($user->getAuthorisedCategories($asset, 'core.create')) > 0)
            || ($user->authorise('core.edit.own', $asset) && $author === $user->id)
            || (count($user->getAuthorisedCategories($extension, 'core.edit')) > 0)
            || (count($user->getAuthorisedCategories($extension, 'core.edit.own')) > 0 && $author === $user->id)
        ) {
            $doc->getWebAssetManager()
                ->useScript('webcomponent.media-select')
                ->useScript('webcomponent.field-media')
                ->useStyle('webcomponent.media-select');

            $doc->addScriptOptions('xtdImageModal', [
                $name . '_ImageModal',
            ]);

            if (count($doc->getScriptOptions('media-picker')) === 0) {
                $imagesExt = array_map(
                    'trim',
                    explode(
                        ',',
                        ComponentHelper::getParams('com_media')->get(
                            'image_extensions',
                            'bmp,gif,jpg,jpeg,png,webp'
                        )
                    )
                );
                $audiosExt = array_map(
                    'trim',
                    explode(
                        ',',
                        ComponentHelper::getParams('com_media')->get(
                            'audio_extensions',
                            'mp3,m4a,mp4a,ogg'
                        )
                    )
                );
                $videosExt = array_map(
                    'trim',
                    explode(
                        ',',
                        ComponentHelper::getParams('com_media')->get(
                            'video_extensions',
                            'mp4,mp4v,mpeg,mov,webm'
                        )
                    )
                );
                $documentsExt = array_map(
                    'trim',
                    explode(
                        ',',
                        ComponentHelper::getParams('com_media')->get(
                            'doc_extensions',
                            'doc,odg,odp,ods,odt,pdf,ppt,txt,xcf,xls,csv'
                        )
                    )
                );

                $doc->addScriptOptions('media-picker', [
                    'images'    => $imagesExt,
                    'audios'    => $audiosExt,
                    'videos'    => $videosExt,
                    'documents' => $documentsExt
                ]);
            }

            Text::script('JFIELD_MEDIA_LAZY_LABEL');
            Text::script('JFIELD_MEDIA_ALT_LABEL');
            Text::script('JFIELD_MEDIA_ALT_CHECK_LABEL');
            Text::script('JFIELD_MEDIA_ALT_CHECK_DESC_LABEL');
            Text::script('JFIELD_MEDIA_CLASS_LABEL');
            Text::script('JFIELD_MEDIA_FIGURE_CLASS_LABEL');
            Text::script('JFIELD_MEDIA_FIGURE_CAPTION_LABEL');
            Text::script('JFIELD_MEDIA_LAZY_LABEL');
            Text::script('JFIELD_MEDIA_SUMMARY_LABEL');
            Text::script('JFIELD_MEDIA_EMBED_CHECK_DESC_LABEL');
            Text::script('JFIELD_MEDIA_DOWNLOAD_CHECK_DESC_LABEL');
            Text::script('JFIELD_MEDIA_DOWNLOAD_CHECK_LABEL');
            Text::script('JFIELD_MEDIA_EMBED_CHECK_LABEL');
            Text::script('JFIELD_MEDIA_WIDTH_LABEL');
            Text::script('JFIELD_MEDIA_TITLE_LABEL');
            Text::script('JFIELD_MEDIA_HEIGHT_LABEL');
            Text::script('JFIELD_MEDIA_UNSUPPORTED');
            Text::script('JFIELD_MEDIA_DOWNLOAD_FILE');

            $link = 'index.php?option=com_media&view=media&tmpl=component&e_name=' . $name . '&asset=' . $asset . '&mediatypes=0,1,2,3' . '&author=' . $author;

            $button = new CMSObject();
            $button->modal   = true;
            $button->link    = $link;
            $button->text    = Text::_('PLG_IMAGE_BUTTON_IMAGE');
            $button->name    = $this->_type . '_' . $this->_name;
            $button->icon    = 'pictures';
            $button->iconSVG = '<svg width="24" height="24" viewBox="0 0 512 512"><path d="M464 64H48C21.49 64 0 85.49 0 112v288c0 26.51 21.49 48'
                . ' 48 48h416c26.51 0 48-21.49 48-48V112c0-26.51-21.49-48-48-48zm-6 336H54a6 6 0 0 1-6-6V118a6 6 0 0 1 6-6h404a6 6'
                . ' 0 0 1 6 6v276a6 6 0 0 1-6 6zM128 152c-22.091 0-40 17.909-40 40s17.909 40 40 40 40-17.909 40-40-17.909-40-40-40'
                . 'zM96 352h320v-80l-87.515-87.515c-4.686-4.686-12.284-4.686-16.971 0L192 304l-39.515-39.515c-4.686-4.686-12.284-4'
                . '.686-16.971 0L96 304v48z"></path></svg>';
            $button->options = [
                'height'          => '400px',
                'width'           => '800px',
                'bodyHeight'      => '70',
                'modalWidth'      => '80',
                'tinyPath'        => $link,
                'confirmCallback' => 'Joomla.getImage(Joomla.selectedMediaFile, \'' . $name . '\', this)',
                'confirmText'     => Text::_('PLG_IMAGE_BUTTON_INSERT'),
            ];

            return $button;
        }

        return false;
    }
}
