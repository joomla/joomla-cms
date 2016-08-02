<?php
namespace Page\Acceptance\Administrator;

class MenuManagerPage
{
    // include url of current page
    public static $url = 'administrator/index.php?option=com_menus&view=item&layout=edit&menutype=mainmenu';

    public static $menuFieldTitle = ['id' => 'jform_title'];

    public static $selectMenutype = ['class' =>'btn-primary'];

    public static $selectMenuTypeArticle = ['link' => 'Articles'];

    public static $singleArticle = ['xpath' => "//div[@id='collapseTypes']//a[contains(text()[normalize-space()], 'Single Article')]"];

    public static $selectArticle = ['class' => 'btn hasTooltip'];

    public static $chooseArticle = ['link' => ' Test_article'];

}
