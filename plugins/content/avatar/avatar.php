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
     /**
      * Load the language file on instantiation.
      *
      * @var   boolean
      * @since 3.1
      */
        protected $autoloadLanguage = true;
    /**
     * Default size of the avatar if a size is not set at the backend.
     *
     * @var   integer
     * @since 3.2
     */
        protected $defaultsize = 100;
    /**
     * The URL for the gravatar image.
     *
     * @var   String
     * @since 3.2
     */
        protected $gravatar = 'http://www.gravatar.com/avatar/';
    /**
     * The URL for the gravatar profile.
     *
     * @var   String
     * @since 3.2
      */
        protected $profile = 'http://www.gravatar.com/';
    /**
     * The URL for secure requests which gets the image.
     *
     * @var   String
     * @since 3.2
     */
        protected $securegravatar = 'https://secure.gravatar.com/avatar';
    /**
     * The URL for secure requests which gets the profile.
     *
     * @var   String
     * @since 3.2
     */
        protected $secureprofile = 'https://secure.gravatar.com/';
     
    /**
     * This function gets triggered before rendering content element
     *
     * @param type $context    The context of the content being passed to the plugin.
     * @param type &$row       The article object.
     * @param type &$params    The article params.
     * @param type $limitstart An integer that determines the "page" of the content that is to be generated.
     * 
     * @return type  mixed type string in the front end 
     * 
     * @since 1.6 
     */
    public function onContentBeforeDisplay($context, &$row, &$params, $limitstart=0)
    {     
            // Get the scheme http or https
            $array = JURI::getInstance()-> getScheme(); 
            $app = JFactory::getApplication();
            $http = JHttpFactory::getHttp();
           
        if ($app->isSite())
        {
            $size = $this->params->get('size', $this->defaultsize);
            $gravatar = $this->params->get('avatar_http', $this->gravatar);
            $profile = $this->params->get('profile_http', $this->profile);
            $securegravatar = $this->params->get('avatar_https', $this->securegravatar);
            $secureprofile = $this->params->get('profile_https', $this->secureprofile);
       
            $id = $row->created_by;
            $user = JFactory::getUser($id);
            $emailid = $user->email;
            $html = ($array == 'http'? $this->buildHTML($gravatar, $profile, $emailid, $size, $http): $this->buildHTML($securegravatar, $secureprofile, $emailid, $size, $http));
        
            return implode("<br /> ", $html);
        } 
        else 
        {
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
        if ($this->params->get('show_profileinfo') == '1')
        {
            // Get the HTTP object to reference Profile information
            $response_profile = $http->get($gravatar_profile . $hashedemail . '.php');
            
            // If profile is found then perform further information 
            if ($response_profile->code == 302 || $response_profile->code == 200)
            {
                // If curl is on fetch information using JHttp Object
                if ($this->params->get('check_curl') == '1') 
                {
                    $str = $response_profile->body;
                    $profile = unserialize($str);
                } 
                else 
                {
                    $str = file_get_contents($gravatar_profile . $hashedemail . '.php');
                    $profile = unserialize($str);
                }
                
                // If the profile is not null
                if (is_array($profile) && isset($profile['entry'])) 
                {
                    // If Name Exists
                    if (isset($profile['entry'][0]['displayName'])) 
                    {
                        $name = '<dt>' . JText::_('PLG_CONTENT_AVATAR_MY_NAME') . '</dt>';
                        $name.= '<dd>' . $profile['entry'][0]['displayName'] . '</dd>'; 
                    }
                    
                    // If about me Exists
                    if (isset($profile['entry'][0]['aboutMe'])) 
                    {
                        $aboutme = '<dt>' . JText::_('PLG_CONTENT_AVATAR_ABOUT_ME') . '</dt>'; 
                        $aboutme.= '<dd>' . $profile['entry'][0]['aboutMe'] . '</dd>';
                    }
                    
                    // If the current Location exist
                    if (isset($profile['entry'][0]['currentLocation'])) 
                    {
                        $currentlocation = '<dt>' . JText::_('PLG_CONTENT_AVATAR_CURRENT_LOCATION') . '</dt>'; 
                        $currentlocation.= '<dd>' . $profile['entry'][0]['currentLocation'] . '</dd>';
                    }
                    
                    // If the Email exists
                    if (isset($profile['entry'][0]['emails'])) 
                    {
                        $myemail = '<dt>' . JText::_('PLG_CONTENT_AVATAR_MY_PUBLIC_EMAIL') . '</dt>';
                        $myemail.= '<dd>' . '<a href="' . '' . $profile['entry'][0]['emails'][0]['value'] . '">' . $profile['entry'][0]['emails'][0]['value'] . '</a>' . '</dd>'; 
                    }
                    
                    // If IM accounts exist
                    if (isset($profile['entry'][0]['ims'])) 
                    { 
                        $im_accounts = $profile['entry'][0]['ims'];
                        $imaccounts = '<dt>' . JText::_('PLG_CONTENT_AVATAR_IM_ACCOUNT') . '</dt>';
                        
                        foreach ($im_accounts as $ims ) 
                        {
                                $imaccounts.= '<dt>' . $ims['type'] . '</dt>' . '<dd>' . $ims['value'] . '</dd>';
                        }
                    }
                    
                    // If Verified accounts exist
                    if (isset($profile['entry'][0]['accounts'])) 
                    {
                        $verified_accounts = $profile['entry'][0]['accounts'];
                        $verifiedaccount = '<dt>' . JText::_('PLG_CONTENT_AVATAR_VERIFIED') . '</dt>'; 
                        
                        foreach ($verified_accounts as $verified_account) 
                        {
                                $verifiedaccount.= '<dd>' . '<a href="' . '' . $verified_account['url'] .'">' . $verified_account['display']. '</a>' . '</dd>';
                        }
                    }   
                    
                    // If phone numbers exist
                    if (isset($profile['entry'][0]['phoneNumbers'])) 
                    {
                        $phone_numbers = $profile['entry'][0]['phoneNumbers'];
                        $contactnumbers = '<dt>' . JText::_('PLG_CONTENT_AVATAR_CONTACT'). '</dt>';
                        
                        foreach ($phone_numbers as $phone_number) 
                        {
                                $contactnumbers.= '<dt>' . $phone_number['type']. '</dt>' . ' ' .'<dd>' . $phone_number['value'] . '</dd>';
                        }
                    }
                    
                    // If blogs Exist
                    if (isset($profile['entry'][0]['urls'])) 
                    {
                        $blogs = $profile['entry'][0]['urls'];
                        $blogdetails = '<dt>' . JText::_('PLG_CONTENT_AVATAR_BLOGS') . '</dt>';
                        
                        foreach ($blogs as $blog) 
                        {
                                $blogdetails.= '<dt>' . $blog['title'] . '</dt>' . '<dd>' . '<a href="' . '' . $blog['value'] . '">' . $blog['value'] . '</a>' . '</dd>';
                        }
                    }
                    
                            // Select the element which has HasPopOver id or class and set it up for the Pop Over
                            $html[] = JHtmlBootstrap::popover(
                                '.hasPopover', array('animation'=>true, 'trigger'=>'click', 'placement'=>'right', 'container'=>'body', 'html'=> true, 'content'=>
                                '<div class="avatar popover-content">' .
                                '<dl>' . $name
                                       . $myemail 
                                       . $imaccounts  
                                       . $contactnumbers 
                                       . $aboutme
                                       . $currentlocation  
                                       . $blogdetails 
                                       . $verifiedaccount .
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
