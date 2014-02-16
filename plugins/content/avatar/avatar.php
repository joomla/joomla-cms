<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.joomla
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die; // Stopping Unauthorized access 
/**
 * Joomla Content plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Content.joomla
 * @since       3.2
 */

Class PlgContentAvatar extends JPlugin
{
    //Load the Language files and Initialize Avatar Servers
        protected $autoloadLanguage = true;
        protected $defaultsize=100;
        protected $GRAVATAR_SERVER="http://www.gravatar.com/avatar/";
        protected $default="http://www.gravatar.com/";
        protected $GRAVATAR_SECURE_SERVER="https://secure.gravatar.com/avatar";
        protected $securedefault="https://secure.gravatar.com/";
        protected $uri;


        public function onContentBeforeDisplay($context, &$row, &$params, $page=0)
        {       
            //get the scheme http or https
            $array=JURI::getInstance()->getScheme(); 
            //Get input parameters if not use the default values
            $size=$this->params->get('size',$this->defaultsize);
            $GRAVATAR_SERVER=$this->params->get('avatar_http',$this->GRAVATAR_SERVER);
            $default=$this->params->get('profile_http',$this->default);
            $GRAVATAR_SECURE_SERVER=  $this->params->get('avatar_https',$this->GRAVATAR_SECURE_SERVER);
            $securedefault=  $this->params->get('profile_https',$this->securedefault);
            //if the article is featured 
            if($context=='com_content.featured')
            {   //get the email of the Author 
                $emailid=$row->author_email;
                
                $html=($array=='http'? $this->buildHTML($GRAVATAR_SERVER,$default,$emailid,$size): $this->buildHTML($GRAVATAR_SECURE_SERVER,$securedefault,$emailid,$size));
                
            }
                return implode("<br /> ", $html);
        }
    
        public function buildHTML($avatar,$gravatar_profile,$email,$size)
        {
                $gravurl=$avatar.md5( strtolower( trim( $email ) ) )."?d=".urlencode($this->default )."&s=".$size;
                $str=  @file_get_contents("$gravatar_profile".md5($email).".php");
                $profile=  unserialize($str);
                 
                if ( is_array( $profile ) && isset( $profile['entry'] ) )
                {      
                //Reference the array to get details    
                $name=$profile['entry'][0]['displayName'];   
                $myemail=$profile['entry'][0]['emails'][0]['value'];    
                $im_accounts=$profile['entry'][0]['ims'][0]['value'];   
                
                JHtml::_('jquery.framework');
                $html[] = '<form="well span4">';
                $html[] = JHtml::_('image', $gravurl, JText::_('PLG_CONTENT_AVATAR'), null, true);
               
                
                
                $html[]='<label class="label label-info">';
                $html[]= JText::_('PLG_CONTENT_AVATAR_MY_NAME').$name;
                $html[]='</label>';
              
                $html[]='<label class="label label-info">';
                $html[]=JText::_('PLG_CONTENT_AVATAR_MY_PUBLIC_EMAIL').$myemail;
                $html[]='</label>';
                
                $html[]='<label class="label label-info">';
                $html[]= JText::_('PLG_CONTENT_AVATAR_IM_ACCOUNT').$im_accounts;
                $html[]='</label>';
                $html[] = '</form>';
             
                }
                 else
                {
            
                
                $default_url="$avatar".md5( strtolower( trim( $email ) ) );
                $selection=  $this->params->get('default','identicon');
                $default_url=$default_url."?d=".$selection."&s=".$size;
               
             
                $html[] = '<span class="gravatar">';
                $html[] = JHtml::_('image', $default_url, JText::_('PLG_CONTENT_AVATAR'), null, true);
                $html[] = '</span>';
             
             
                } 
                
         
                return $html;
        }
        
}
