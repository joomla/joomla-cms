<?php
namespace Page\Acceptance\Administrator;

class CategoryManagerPage
{
    // include url of current page
    public static $url = '/administrator/index.php?option=com_categories&view=categories&extension=com_content';

    public static $categoryTitleField = ['id' => 'jform_title'];

    public static $filterSearch = ['id' => 'filter_search'];

    public static $iconSearch = ['class' => 'icon-search'];

    public static $invalidTitle = ['class' => 'alert-error'];



}
