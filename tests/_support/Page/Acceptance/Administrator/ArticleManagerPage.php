<?php
namespace Page\Acceptance\Administrator;

use Page\Acceptance\Administrator\AdminPage;

class ArticleManagerPage extends AdminPage
{
    public static $articleTitleField = ['id' => 'jform_title'];

    public static $articleContentField = ['id' => 'jform_articletext'];

    public static $toggleEditor = "Toggle editor";
    
    public static $filterSearch = ['id' => 'filter_search'];

    public static $iconSearch = ['class' => 'icon-search'];
    
    public static $url = "/administrator/index.php?option=com_content&view=articles";

}
