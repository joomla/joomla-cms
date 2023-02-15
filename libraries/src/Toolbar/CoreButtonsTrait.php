<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Toolbar\Button\ConfirmButton;
use Joomla\CMS\Toolbar\Button\CustomButton;
use Joomla\CMS\Toolbar\Button\HelpButton;
use Joomla\CMS\Toolbar\Button\LinkButton;
use Joomla\CMS\Toolbar\Button\PopupButton;
use Joomla\CMS\Toolbar\Button\SeparatorButton;
use Joomla\CMS\Toolbar\Button\StandardButton;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Enhance Toolbar class to add more pre-defined methods.
 *
 * @since  4.0.0
 */
trait CoreButtonsTrait
{
    /**
     * Writes a divider between dropdown menu items.
     *
     * @param   string  $text  The text of button.
     *
     * @return  SeparatorButton
     *
     * @since  4.0.0
     */
    public function divider(string $text = ''): SeparatorButton
    {
        return $this->separatorButton('divider', $text);
    }

    /**
     * Writes a preview button for a given option (opens a popup window).
     *
     * @param   string  $url        The name of the popup file (excluding the file extension)
     * @param   string  $text       The text of button.
     * @param   bool    $newWindow  Whether to open the preview in _blank or just a modal
     *
     * @return  PopupButton|LinkButton
     *
     * @since   4.0.0
     */
    public function preview(string $url, string $text = 'JGLOBAL_PREVIEW', $newWindow = false)
    {
        if ($newWindow === true) {
            $button = $this->linkButton('link', $text)
                ->url($url)
                ->attributes(['target' => '_blank'])
                ->icon('icon-eye');
        } else {
            $button = $this->popupButton('preview', $text)
                ->url($url)
                ->iframeWidth(640)
                ->iframeHeight(480)
                ->icon('icon-eye');
        }

        return $button;
    }

    /**
     * Writes a jooa11y accessibility checker button for a given option (opens a popup window).
     *
     * @param   string  $url        The url to open
     * @param   string  $text       The text of button.
     * @param   bool    $newWindow  Whether to open the preview in _blank or just a modal
     *
     * @return  PopupButton|LinkButton
     *
     * @since   4.1.0
     */
    public function jooa11y(string $url, string $text = 'JGLOBAL_JOOA11Y', $newWindow = false)
    {
        if ($newWindow === true) {
            $button = $this->linkButton('jooa11y-link', $text)
                ->url($url)
                ->attributes(['target' => '_blank'])
                ->icon('icon-universal-access');
        } else {
            $button = $this->popupButton('jooa11y-preview', $text)
                ->url($url)
                ->iframeWidth(640)
                ->iframeHeight(480)
                ->icon('icon-universal-access');
        }

        return $button;
    }

    /**
     * Writes a help button for a given option (opens a popup window).
     *
     * @param   string  $ref           The name of the popup file (excluding the file extension for an xml file).
     * @param   bool    $useComponent  Use the help file in the component directory.
     * @param   string  $url           Use this URL instead of any other.
     * @param   string  $component     Name of component to get Help (null for current component)
     *
     * @return  HelpButton
     *
     * @since   4.0.0
     */
    public function help($ref, $useComponent = false, $url = null, $component = null): HelpButton
    {
        return $this->helpButton('help', 'JTOOLBAR_HELP')
            ->ref($ref)
            ->useComponent($useComponent)
            ->url($url)
            ->component($component);
    }

    /**
     * Writes a cancel button that will go back to the previous page without doing
     * any other operation.
     *
     * @param   string  $text  The text of button.
     *
     * @return  LinkButton
     *
     * @since   4.0.0
     */
    public function back(string $text = 'JTOOLBAR_BACK'): LinkButton
    {
        return $this->link('back', $text)
            ->url('javascript:history.back();');
    }

    /**
     * Creates a button to redirect to a link.
     *
     * @param   string  $text  Button text.
     * @param   string  $url   The link url.
     *
     * @return  LinkButton
     *
     * @since   4.0.0
     */
    public function link(string $text, string $url): LinkButton
    {
        return $this->linkButton('link', $text)
            ->url($url);
    }

    /**
     * Writes a media_manager button.
     *
     * @param   string  $directory  The subdirectory to upload the media to.
     * @param   string  $text       An override for the alt text.
     *
     * @return  PopupButton
     *
     * @since   4.0.0
     */
    public function mediaManager(string $directory, string $text = 'JTOOLBAR_UPLOAD'): PopupButton
    {
        return $this->popupButton('upload', $text)
            ->iframeWidth(800)
            ->iframeHeight(520)
            ->url('index.php?option=com_media&tmpl=component&task=popupUpload&folder=' . $directory);
    }

    /**
     * Writes a common 'default' button for a record.
     *
     * @param   string  $task  An override for the task.
     * @param   string  $text  An override for the alt text.
     *
     * @return  StandardButton
     *
     * @since   4.0.0
     */
    public function makeDefault(string $task, string $text = 'JTOOLBAR_DEFAULT'): StandardButton
    {
        return $this->standardButton('default', $text)
            ->task($task);
    }

    /**
     * Writes a common 'assign' button for a record.
     *
     * @param   string  $task  The task name of this button.
     * @param   string  $text  The text of this button.
     *
     * @return  StandardButton
     *
     * @since   4.0.0
     */
    public function assign(string $task, string $text = 'JTOOLBAR_ASSIGN'): StandardButton
    {
        return $this->standardButton('assign', $text)
            ->task($task);
    }

    /**
     * Writes the common 'new' icon for the button bar.
     *
     * @param   string  $task  The task name of this button.
     * @param   string  $text  The text of this button.
     *
     * @return  StandardButton
     *
     * @since   4.0.0
     */
    public function addNew(string $task, string $text = 'JTOOLBAR_NEW'): StandardButton
    {
        return $this->standardButton('new', $text)
            ->task($task);
    }

    /**
     * Writes a common 'publish' button.
     *
     * @param   string  $task  The task name of this button.
     * @param   string  $text  The text of this button.
     *
     * @return  StandardButton
     *
     * @since   4.0.0
     */
    public function publish(string $task, string $text = 'JTOOLBAR_PUBLISH'): StandardButton
    {
        return $this->standardButton('publish', $text)
            ->task($task);
    }

    /**
     * Writes a common 'unpublish' button.
     *
     * @param   string  $task  The task name of this button.
     * @param   string  $text  The text of this button.
     *
     * @return  StandardButton
     *
     * @since   4.0.0
     */
    public function unpublish(string $task, string $text = 'JTOOLBAR_UNPUBLISH'): StandardButton
    {
        return $this->standardButton('unpublish', $text)
            ->task($task);
    }

    /**
     * Writes a common 'archive' button.
     *
     * @param   string  $task  The task name of this button.
     * @param   string  $text  The text of this button.
     *
     * @return  StandardButton
     *
     * @since   4.0.0
     */
    public function archive(string $task, string $text = 'JTOOLBAR_ARCHIVE'): StandardButton
    {
        return $this->standardButton('archive', $text)
            ->task($task);
    }

    /**
     * Writes a common 'unarchive' button.
     *
     * @param   string  $task  The task name of this button.
     * @param   string  $text  The text of this button.
     *
     * @return  StandardButton
     *
     * @since   4.0.0
     */
    public function unarchive(string $task, string $text = 'JTOOLBAR_UNARCHIVE'): StandardButton
    {
        return $this->standardButton('unarchive', $text)
            ->task($task);
    }

    /**
     * Writes a common 'edit' button.
     *
     * @param   string  $task  The task name of this button.
     * @param   string  $text  The text of this button.
     *
     * @return  StandardButton
     *
     * @since   4.0.0
     */
    public function edit(string $task, string $text = 'JTOOLBAR_EDIT'): StandardButton
    {
        return $this->standardButton('edit', $text)
            ->task($task);
    }

    /**
     * Writes a common 'editHtml' button.
     *
     * @param   string  $task  The task name of this button.
     * @param   string  $text  The text of this button.
     *
     * @return  StandardButton
     *
     * @since   4.0.0
     */
    public function editHtml(string $task, string $text = 'JTOOLBAR_EDIT_HTML'): StandardButton
    {
        return $this->standardButton('edithtml', $text)
            ->task($task);
    }

    /**
     * Writes a common 'editCss' button.
     *
     * @param   string  $task  The task name of this button.
     * @param   string  $text  The text of this button.
     *
     * @return  StandardButton
     *
     * @since   4.0.0
     */
    public function editCss(string $task, string $text = 'JTOOLBAR_EDIT_CSS'): StandardButton
    {
        return $this->standardButton('editcss', $text)
            ->task($task);
    }

    /**
     * Writes a common 'delete' button.
     *
     * @param   string  $task  The task name of this button.
     * @param   string  $text  The text of this button.
     *
     * @return  ConfirmButton
     *
     * @since   4.0.0
     */
    public function delete(string $task, string $text = 'JTOOLBAR_DELETE'): ConfirmButton
    {
        return $this->confirmButton('delete', $text)
            ->task($task);
    }

    /**
     * Writes a common 'trash' button.
     *
     * @param   string  $task  The task name of this button.
     * @param   string  $text  The text of this button.
     *
     * @return  StandardButton
     *
     * @since   4.0.0
     */
    public function trash(string $task, string $text = 'JTOOLBAR_TRASH'): StandardButton
    {
        return $this->standardButton('trash', $text)
            ->task($task);
    }

    /**
     * Writes a save button for a given option.
     * Apply operation leads to a save action only (does not leave edit mode).
     *
     * @param   string  $task  The task name of this button.
     * @param   string  $text  The text of this button.
     *
     * @return  StandardButton
     *
     * @since   4.0.0
     */
    public function apply(string $task, string $text = 'JTOOLBAR_APPLY'): StandardButton
    {
        return $this->standardButton('apply', $text)
            ->task($task)
            ->formValidation(true);
    }

    /**
     * Writes a save button for a given option.
     * Save operation leads to a save and then close action.
     *
     * @param   string  $task  The task name of this button.
     * @param   string  $text  The text of this button.
     *
     * @return  StandardButton
     *
     * @since   4.0.0
     */
    public function save(string $task, string $text = 'JTOOLBAR_SAVE'): StandardButton
    {
        return $this->standardButton('save', $text)
            ->task($task)
            ->formValidation(true);
    }

    /**
     * Writes a save and create new button for a given option.
     * Save and create operation leads to a save and then add action.
     *
     * @param   string  $task  The task name of this button.
     * @param   string  $text  The text of this button.
     *
     * @return  StandardButton
     *
     * @since   4.0.0
     */
    public function save2new(string $task, string $text = 'JTOOLBAR_SAVE_AND_NEW'): StandardButton
    {
        return $this->standardButton('save-new', $text)
            ->task($task)
            ->formValidation(true);
    }

    /**
     * Writes a save as copy button for a given option.
     * Save as copy operation leads to a save after clearing the key,
     * then returns user to edit mode with new key.
     *
     * @param   string  $task  The task name of this button.
     * @param   string  $text  The text of this button.
     *
     * @return  StandardButton
     *
     * @since   4.0.0
     */
    public function save2copy(string $task, string $text = 'JTOOLBAR_SAVE_AS_COPY'): StandardButton
    {
        return $this->standardButton('save-copy', $text)
            ->task($task)
            ->formValidation(true);
    }

    /**
     * Writes a checkin button for a given option.
     *
     * @param   string  $task  The task name of this button.
     * @param   string  $text  The text of this button.
     *
     * @return  StandardButton
     *
     * @since   4.0.0
     */
    public function checkin(string $task, string $text = 'JTOOLBAR_CHECKIN'): StandardButton
    {
        return $this->standardButton('checkin', $text)
            ->task($task);
    }

    /**
     * Writes a cancel button and invokes a cancel operation (eg a checkin).
     *
     * @param   string  $task  The task name of this button.
     * @param   string  $text  The text of this button.
     *
     * @return  StandardButton
     *
     * @since   4.0.0
     */
    public function cancel(string $task, string $text = 'JTOOLBAR_CLOSE'): StandardButton
    {
        return $this->standardButton('cancel', $text)
            ->task($task);
    }

    /**
     * Writes a configuration button and invokes a cancel operation (eg a checkin).
     *
     * @param   string  $component  The name of the component, eg, com_content.
     * @param   string  $text       The text of this button.
     * @param   string  $path       An alternative path for the configuration xml relative to JPATH_SITE.
     *
     * @return  LinkButton
     *
     * @since   4.0.0
     */
    public function preferences(string $component, string $text = 'JTOOLBAR_OPTIONS', string $path = ''): LinkButton
    {
        $component = urlencode($component);
        $path = urlencode($path);

        $uri = (string) Uri::getInstance();
        $return = urlencode(base64_encode($uri));

        return $this->linkButton('options', $text)
            ->url('index.php?option=com_config&amp;view=component&amp;component=' . $component . '&amp;path=' . $path . '&amp;return=' . $return);
    }

    /**
     * Writes a version history
     *
     * @param   string   $typeAlias  The component and type, for example 'com_content.article'
     * @param   integer  $itemId     The id of the item, for example the article id.
     * @param   integer  $height     The height of the popup.
     * @param   integer  $width      The width of the popup.
     * @param   string   $text       The name of the button.
     *
     * @return  CustomButton
     *
     * @since   4.0.0
     */
    public function versions(
        string $typeAlias,
        int $itemId,
        int $height = 800,
        int $width = 500,
        string $text = 'JTOOLBAR_VERSIONS'
    ): CustomButton {
        $lang = Factory::getLanguage();
        $lang->load('com_contenthistory', JPATH_ADMINISTRATOR, $lang->getTag(), true);

        // Options array for Layout
        $options              = [];
        $options['title']     = Text::_($text);
        $options['height']    = $height;
        $options['width']     = $width;
        $options['itemId']    = $typeAlias . '.' . $itemId;

        $layout = new FileLayout('joomla.toolbar.versions');

        return $this->customHtml($layout->render($options), 'version');
    }

    /**
     * Writes a custom HTML to toolbar.
     *
     * @param   string  $html  The HTML string to write.
     * @param   string  $name  The button name.
     *
     * @return  CustomButton
     *
     * @since   4.0.0
     */
    public function customHtml(string $html, string $name = 'custom'): CustomButton
    {
        return $this->customButton($name)
            ->html($html);
    }
}
