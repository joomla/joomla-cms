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
 * @since       1.5
 */
Class PlgContentGravatar extends JPlugin
{
    protected $autoloadLanguage = true;
    protected $defaultsize=100;
    protected $GRAVATAR_SERVER="http://www.gravatar.com/avatar/";
    protected $default="http://www.gravatar.com/";
    protected $GRAVATAR_SECURE_SERVER="https://secure.gravatar.com/avatar";
    protected $securedefault="https://secure.gravatar.com/";
    protected $uri;


    public function onContentBeforeDisplay($context, &$row, &$params, $page=0)
    {
            $array=JURI::getInstance()->getScheme(); 
            $size=$this->params->get('size',$this->defaultsize);
            $GRAVATAR_SERVER=$this->params->get('avatar_http',$this->GRAVATAR_SERVER);
            $default=$this->params->get('profile_http',$this->default);
            $GRAVATAR_SECURE_SERVER=  $this->params->get('avatar_https',$this->GRAVATAR_SECURE_SERVER);
            $securedefault=  $this->params->get('profile_https',$this->securedefault);
        
            if($context=='com_content.featured')
            {
                $emailid=$row->author_email;
            
            
            if ($array=='http')
            {
                $html[]=  $this->buildHTML($GRAVATAR_SERVER,$default,$emailid,$size);
            }
            
            if($array=='https')
            {
               $html[]=  $this->buildHTML($GRAVATAR_SECURE_SERVER,$securedefault,$emailid,$size);
            }
        }
        return implode("</br> ", $html);
    }
    
    public function buildHTML($avatar,$gravatar_profile,$email,$size)
    {
                $gravurl=$avatar.md5( strtolower( trim( $email ) ) )."?d=".urlencode($this->default )."&s=".$size;
                $str=  @file_get_contents("$gravatar_profile".md5($email).".php");
                $profile=  unserialize($str);
                 
                if ( is_array( $profile ) && isset( $profile['entry'] ) )
                {      
             
                $name=$profile['entry'][0]['displayName'];   //Displaying My name
                $myemail=$profile['entry'][0]['emails'][0]['value'];    //Displaying my email
                $im_accounts=$profile['entry'][0]['ims'][0]['value'];   //Displaying my Ims accounts
                
                
                $html[] = '<form="well">';
                $html[] = JHtml::_('image', $gravurl, JText::_('MY_AVATAR'), null, true);
                //$html[] = '</span>';
                
                
                $html[]='<label class="label label-info">';
                $html[]= "My Gravatar Name: ".$name;
                $html[]='</label>';
                
                $html[]='<label class="label label-info">';
                $html[]= "My public Email: ".$myemail;
                $html[]='</label>';
                
                $html[]='<label class="label label-info">';
                $html[]= "My IM account id: ".$im_accounts;
                $html[]='</label>';
                $html[] = '</form>';
             
                }
                 else
                {
            
                
                $default_url="$avatar".md5( strtolower( trim( $email ) ) );
                $selection=  $this->params->get('default','identicon');
                $default_url=$default_url."?d=".$selection."&s=".$size;
               
             
                $html[] = '<span class="gravatar">';
                $html[] = JHtml::_('image', $default_url, JText::_('MY_AVATAR'), null, true);
                $html[] = '</span>';
             
             
                } 
                
         return implode("</br> ", $html);
        
    }
        
}
