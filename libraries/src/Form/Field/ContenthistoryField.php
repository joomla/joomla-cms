<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Field to select Content History from a modal list.
 *
 * @since  3.2
 */
class ContenthistoryField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.2
     */
    public $type = 'ContentHistory';

    /**
     * Layout to render the label
     *
     * @var  string
     */
    protected $layout = 'joomla.form.field.contenthistory';

    /**
     * Get the data that is going to be passed to the layout
     *
     * @return  array
     */
    public function getLayoutData()
    {
        // Get the basic field data
        $data = parent::getLayoutData();

        $itemId = $this->element['data-typeAlias'] . '.' . $this->form->getValue('id');
        $label  = Text::_('JTOOLBAR_VERSIONS');

        $link = 'index.php?option=com_contenthistory&amp;view=history&amp;layout=modal&amp;tmpl=component&amp;field='
            . $this->id . '&amp;item_id=' . $itemId . '&amp;' . Session::getFormToken() . '=1';

        $extraData = [
            'item' => $itemId,
            'label' => $label,
            'link' => $link,
        ];

        return array_merge($data, $extraData);
    }

    /**
     * Method to get the content history field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   3.2
     */
    protected function getInput()
    {
        if (empty($this->layout)) {
            throw new \UnexpectedValueException(sprintf('%s has no layout assigned.', $this->name));
        }

        return $this->getRenderer($this->layout)->render($this->getLayoutData());
    }
}
