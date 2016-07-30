<?php
namespace Page\Acceptance\Site;

class Frontendlogin
{
    public static $profile = '/index.php?option=com_users&view=profile';

    public static $modlgnUsername = ['id' => 'modlgn-username'];
    
    public static $modlgnPasswd = ['id' => 'modlgn-passwd'];

    public static $btnGroup = ['class' => 'btn-group'];

    public static $btnPrimaryValidate = ['class' => 'btn-primary'];

    public static $mdlLogin = ['xpath' => ".//*[@id='aside']/div[2]/h3"];


}
