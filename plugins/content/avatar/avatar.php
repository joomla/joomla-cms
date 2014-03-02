<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.joomla
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// Stopping Unauthorized access 
defined('_JEXEC') or die; 

/**
 * Joomla Content plugin
 *
 * @package    Joomla.Plugin
 * @subpackage Content.joomla
 * @since      3.2
 */


Class PlgContentAvatar extends JPlugin
{
        // Load the Language files and Initialize Avatar Servers
        protected $autoloadLanguage = true;
        protected $defaultsize = 100;
        protected $GRAVATAR_SERVER = 'http://www.gravatar.com/avatar/';
        protected $default = 'http://www.gravatar.com/';
        protected $GRAVATAR_SECURE_SERVER = 'https://secure.gravatar.com/avatar';
        protected $securedefault = 'https://secure.gravatar.com/';
        protected $uri;

    /**
     * This function gets triggered before rendering content element
     *
     * @param type $context    The context of the content being passed to the plugin.
     * @param type &$row       The article object.
     * @param type &$params    The article params.
     * @param type $limitstart An integer that determines the "page" of the content that is to be generated.
     * 
     * @return type  HTML
     */
    public function onContentBeforeDisplay($context, &$row, &$params, $limitstart=0)
    {     
            // Get the scheme http or https
            $array = JURI::getInstance()-> getScheme(); 
            $app = JFactory::getApplication();
            $http = JHttpFactory::getHttp();
           
        if ($app->isSite()) {
            $size = $this->params->get('size', $this->defaultsize);
            $GRAVATAR_SERVER = $this->params->get('avatar_http', $this->GRAVATAR_SERVER);
            $default = $this->params->get('profile_http', $this->default);
            $GRAVATAR_SECURE_SERVER = $this->params->get('avatar_https', $this->GRAVATAR_SECURE_SERVER);
            $securedefault = $this->params->get('profile_https', $this->securedefault);
       
            $id = $row->created_by;
            $user = JFactory::getUser($id);
            $emailid = $user->email;
            $html = ($array == 'http'? $this->buildHTML($GRAVATAR_SERVER, $default, $emailid, $size, $http): $this->buildHTML($GRAVATAR_SECURE_SERVER, $securedefault, $emailid, $size, $http));
        
            return implode("<br /> ", $html);
        } else {
            return;
        }
    }
        /**
         * Function which builds the html of avatar and the profile.
         * @param type $avatar           URL to get the avatar.
         * @param type $gravatar_profile URL to get the profile information.
         * @param type $email            Email address of the author.
         * @param type $size             Size of the avatar. 
         * @param type $http             The JHTTP object.
         * Build The HTML avatar and the profile
         * @return type  HTML 
         */
    public function buildHTML($avatar, $gravatar_profile, $email, $size, $http)
    {
        $hashedemail = md5(strtolower(trim($email)));
        $selection = $this->params->get('default_avatar', '404');
        $grav_url = $avatar . $hashedemail . '?d=' . $selection . '&s=' . $size;
        $style = $this->params->get('style', '');
        $alignment = $this->params->get('alignment', '');
        // Show the Image
        $html[] = '<div class="avatar">';
        $html[] = JHtml::_('image', $grav_url, JText::_('PLG_CONTENT_AVATAR'), 'class = "img-avatar hasPopover' . ' ' . $style . ' ' . $alignment . ' " ', true);
        $html[] = '</div>';
                
        // Perform the operation only if the show profile is selected
        if ($this->params->get('show_profileinfo') == '1') {
            // Get the HTTP object to reference Profile information
            $response_profile = $http->get($gravatar_profile . $hashedemail . '.php');
            // If profile is found then perform further information 
            if ($response_profile->code == 302 || $response_profile->code == 200) {
                // If curl is on fetch information using JHttp Object
                if ($this->params->get('check_curl') == '1') {
                    $str = $response_profile->body;
                    $profile = unserialize($str);
                } else {
                    $str = file_get_contents($gravatar_profile . $hashedemail . '.php');
                    $profile = unserialize($str);
                }
                // If the profile is not null
                if (is_array($profile) && isset($profile['entry'])) {
                    // Reference the array to get details    
                    $name = $profile['entry'][0]['displayName'];   
                    $myemail = $profile['entry'][0]['emails'][0]['value'];    
                    $im_accounts = $profile['entry'][0]['ims'];
                    $verified_accounts=$profile['entry'][0]['accounts'];
                    $phone_numbers=$profile['entry'][0]['phoneNumbers'];
                    $about_me = $profile['entry'][0]['aboutMe'];
                    $blogs = $profile['entry'][0]['urls'];
                    $current_location = $profile['entry'][0]['currentLocation'];
                    $contact_numbers='';
                    $im_accounts_id='';
                    $blog_details='';
                    $verified_accountdetails='';
                            
                    foreach ($verified_accounts as $verified_account) {
                                
                            $verified_accountdetails.= '<dd>' . '<a href="' . '' . $verified_account['url'] .'">' . $verified_account['display']. '</a>' . '</dd>';
                    }
                            
                    foreach ($im_accounts as $ims ) {
                                
                            $im_accounts_id.= '<dt>'. $ims['type'] . '</dt>' . '<dd>' . $ims['value'] . '</dd>';
                    }
                    
                    foreach ($phone_numbers as $phone_number) {
                                
                            $contact_numbers.= '<dt>' . $phone_number['type']. '</dt>' . ' ' .'<dd>' . $phone_number['value'] . '</dd>';
                    }
                    
                    foreach ($blogs as $blog) {
                                
                            $blog_details.='<dt>'.$blog['title'] . '</dt>' .'<dd>' . '<a href="'.''.$blog['value'].'">' . $blog['value']. '</a>' . '</dd>';
                    }
                            
                            // Select the element which has HasPopOver id or class and set it up for the Pop Over
                            $html[] = JHtmlBootstrap::popover(
                                '.hasPopover', array('animation'=>true, 'trigger'=>'click', 'placement'=>'right', 'container'=>'body', 'html'=> true, 'content'=>
                                '<div class="avatar popover-content">' .
                                '<dl>' .
                                '<dt>' . JText::_('PLG_CONTENT_AVATAR_MY_NAME') . '</dt>' .
                                '<dd>' . $name . '</dd>' .
                                '<dt>' . JText::_('PLG_CONTENT_AVATAR_MY_PUBLIC_EMAIL') . '</dt>'.
                                '<dd>' . '<a href="'.'' . $email .'">' . $myemail . '</a>' . '</dd>' .
                                '<dt>' . JText::_('PLG_CONTENT_AVATAR_IM_ACCOUNT') . '</dt>' 
                                       . $im_accounts_id . 
                                '<dt>' . JText::_('PLG_CONTENT_AVATAR_CONTACT'). '</dt>'  
                                       . $contact_numbers .
                                '<dt>' . JText::_('PLG_CONTENT_AVATAR_ABOUT_ME') . '</dt>' .
                                '<dd>' . $about_me . '</dd>' .
                                '<dt>' . JText::_('PLG_CONTENT_AVATAR_CURRENT_LOCATION') . '</dt>' .
                                '<dd>' . $current_location . '</dd>' .
                                '<dt>' . JText::_('PLG_CONTENT_AVATAR_BLOGS') . '</dt>' 
                                       . $blog_details .
                                '<dt>' . JText::_('PLG_CONTENT_AVATAR_VERIFIED') . '</dt>' 
                                       . $verified_accountdetails.
                                '</dl>'.
                                '</div>')
                            );
                            $doc = JFactory::getDocument();
                            JHtml::_('jquery.framework', false);
                            $doc->addScriptDeclaration(
                                '
                            jQuery(document).ready(function () { 
                                   
                                });         
                                      '
                            );
                }
            }
        }
                
                return $html;
    }
}
