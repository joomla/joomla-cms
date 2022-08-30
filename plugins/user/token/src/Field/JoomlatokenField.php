<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  User.token
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\User\Token\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\TextField;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomlatoken field class
 *
 * @since  4.0.0
 */
class JoomlatokenField extends TextField
{
    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     * @since  4.0.0
     */
    protected $layout = 'plugins.user.token.token';

    /**
     * Method to attach a Form object to the field.
     *
     * @param   \SimpleXMLElement  $element   The SimpleXMLElement object representing the `<field>`
     *                                        tag for the form field object.
     * @param   mixed             $value      The form field value to validate.
     * @param   string            $group      The field name group control value. This acts as an
     *                                        array container for the field. For example if the
     *                                        field has name="foo" and the group value is set to
     *                                        "bar" then the full field name would end up being
     *                                        "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @see     FormField::setup()
     * @since   4.0.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $ret = parent::setup($element, $value, $group);

        /**
         * Security and privacy precaution: do not display the token field when the user being
         * edited is not the same as the logged in user. Tokens are conceptually a combination of
         * a username and password, therefore they should be treated in the same mode of
         * confidentiality and privacy as passwords i.e. you can reset them for other users but NOT
         * be able to see them, thus preventing impersonation attacks by a malicious administrator.
         */
        $userId = $this->form->getData()->get('id');

        if ($userId != Factory::getUser()->id) {
            $this->hidden = true;
        }

        return $ret;
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   4.0.0
     */
    protected function getInput()
    {
        // Do not display the token field when the user being edited is not the same as the logged in user
        if ($this->hidden) {
            return '';
        }

        return parent::getInput();
    }

    /**
     * Returns the token formatted suitably for the user to copy.
     *
     * @param   string  $tokenSeed  The token seed data stored in the database
     *
     * @return  string
     * @since   4.0.0
     */
    private function getTokenForDisplay(string $tokenSeed): string
    {
        if (empty($tokenSeed)) {
            return '';
        }

        $algorithm = $this->getAttribute('algo', 'sha256');

        try {
            $siteSecret = Factory::getApplication()->get('secret');
        } catch (\Exception $e) {
            $siteSecret = '';
        }

        // NO site secret? You monster!
        if (empty($siteSecret)) {
            return '';
        }

        $rawToken  = base64_decode($tokenSeed);
        $tokenHash = hash_hmac($algorithm, $rawToken, $siteSecret);
        $userId    = $this->form->getData()->get('id');
        $message   = base64_encode("$algorithm:$userId:$tokenHash");

        if ($userId != Factory::getUser()->id) {
            $message = '';
        }

        return $message;
    }

    /**
     * Get the data for the layout
     *
     * @return  array
     *
     * @since   4.0.0
     */
    protected function getLayoutData()
    {
        $data          = parent::getLayoutData();
        $data['value'] = $this->getTokenForDisplay($this->value);

        return $data;
    }

    /**
     * Get the layout paths
     *
     * @return  array
     *
     * @since   4.0.0
     */
    protected function getLayoutPaths()
    {
        $template = Factory::getApplication()->getTemplate();

        return [
            JPATH_THEMES . '/' . $template . '/html/layouts',
            JPATH_SITE . '/layouts',
        ];
    }
}
