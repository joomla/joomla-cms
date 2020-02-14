<?php

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldElementList extends JFormFieldList
{
    /**
     * The form field type.
     *
     * @var string
     *
     * @since  11.1
     */
    protected $type = 'ElementList';

    /**
     * Method to get the field options.
     *
     * @return array The field option objects
     *
     * @since   11.1
     */
    protected function getOptions()
    {
        $fieldname = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname);
        $options = array();

        $tags = 'a,abbr,address,area,article,aside,audio,b,bdi,bdo,blockquote,br,button,canvas,caption,cite,code,col,colgroup,data,datalist,dd,del,details,dfn,dialog,div,dl,dt,em,fieldset,figcaption,figure,footer,form,h1,h2,h3,h4,h5,h6,header,hgroup,hr,i,img,input,ins,kbd,keygen,label,legend,li,main,map,mark,menu,menuitem,meter,nav,noscript,ol,optgroup,option,output,p,param,pre,progress,q,rb,rp,rt,rtc,ruby,s,samp,section,select,small,source,span,strong,sub,summary,sup,table,tbody,td,template,textarea,tfoot,th,thead,time,tr,track,u,ul,var,video,wbr';

        foreach (explode(',', $tags) as $option) {
            $value = (string) $option;
            $text = trim((string) $option);

            $tmp = array(
                'value' => $value,
                'text' => JText::alt($text, $fieldname),
                'disable' => false,
                'class' => '',
                'selected' => false,
                'checked' => false,
            );

            // Add the option object to the result set.
            $options[] = (object) $tmp;
        }

        reset($options);

        return $options;
    }
}
