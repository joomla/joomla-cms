<?php


namespace enshrined\svgSanitize\data;


/**
 * Class AllowedAttributes
 *
 * @package enshrined\svgSanitize\data
 */
class AllowedAttributes implements AttributeInterface
{

    /**
     * Returns an array of attributes
     *
     * @return array
     */
    public static function getAttributes()
    {
        return array(
            // HTML
            'accept','action','align','alt','autocomplete','background','bgcolor',
            'border','cellpadding','cellspacing','checked','cite','class','clear','color',
            'cols','colspan','coords','datetime','default','dir','disabled',
            'download','enctype','face','for','headers','height','hidden','high','href',
            'hreflang','id','ismap','label','lang','list','loop', 'low','max',
            'maxlength','media','method','min','multiple','name','noshade','novalidate',
            'nowrap','open','optimum','pattern','placeholder','poster','preload','pubdate',
            'radiogroup','readonly','rel','required','rev','reversed','rows',
            'rowspan','spellcheck','scope','selected','shape','size','span',
            'srclang','start','src','step','style','summary','tabindex','title',
            'type','usemap','valign','value','width','xmlns',

            // SVG
            'accent-height','accumulate','additivive','alignment-baseline',
            'ascent','azimuth','baseline-shift','bias','clip','clip-path',
            'clip-rule','color','color-interpolation','color-interpolation-filters',
            'color-profile','color-rendering','cx','cy','d','dy','dy','direction',
            'display','divisor','dur','elevation','end','fill','fill-opacity',
            'fill-rule','filter','flood-color','flood-opacity','font-family',
            'font-size','font-size-adjust','font-stretch','font-style','font-variant',
            'font-weight','image-rendering','in','in2','k1','k2','k3','k4','kerning',
            'letter-spacing','lighting-color','local','marker-end','marker-mid',
            'marker-start','max','mask','mode','min','offset','operator','opacity',
            'order','orient','overflow','paint-order','path','points','r','rx','ry','radius',
            'restart','scale','seed','shape-rendering','stop-color','stop-opacity',
            'stroke-dasharray','stroke-dashoffset','stroke-linecap','stroke-linejoin',
            'stroke-miterlimit','stroke-opacity','stroke','stroke-width','transform',
            'text-anchor','text-decoration','text-rendering','u1','u2','viewbox',
            'visibility','word-spacing','wrap','writing-mode','x','x1','x2','y',
            'y1','y2','z',

            // MathML
            'accent','accentunder','bevelled','close','columnsalign','columnlines',
            'columnspan','denomalign','depth','display','displaystyle','fence',
            'frame','largeop','length','linethickness','lspace','lquote',
            'mathbackground','mathcolor','mathsize','mathvariant','maxsize',
            'minsize','movablelimits','notation','numalign','open','rowalign',
            'rowlines','rowspacing','rowspan','rspace','rquote','scriptlevel',
            'scriptminsize','scriptsizemultiplier','selection','separator',
            'separators','stretchy','subscriptshift','supscriptshift','symmetric',
            'voffset',

            // XML
            'xlink:href','xml:id','xlink:title','xml:space',


            // Camel Case
            "allowReorder", "attributeName", "attributeType", "autoReverse", "baseFrequency",
            "baseProfile", "calcMode", "clipPathUnits", "contentScriptType", "contentStyleType",
            "diffuseConstant", "edgeMode", "externalResourcesRequired", "filterRes",
            "filterUnits", "glyphRef", "gradientTransform", "gradientUnits", "kernelMatrix",
            "kernelUnitLength", "keyPoints", "keySplines", "keyTimes", "lengthAdjust",
            "limitingConeAngle", "markerHeight", "markerUnits", "markerWidth", "maskContentUnits",
            "maskUnits", "numOctaves", "pathLength", "patternContentUnits", "patternTransform",
            "patternUnits", "pointsAtX", "pointsAtY", "pointsAtZ", "preserveAlpha",
            "preserveAspectRatio", "primitiveUnits", "refX", "refY", "repeatCount",
            "repeatDur", "requiredExtensions", "requiredFeatures", "specularConstant",
            "specularExponent", "spreadMethod", "startOffset", "stdDeviation", "stitchTiles",
            "surfaceScale", "systemLanguage", "tableValues", "targetX", "targetY", "textLength",
            "viewBox", "viewTarget", "xChannelSelector", "yChannelSelector", "zoomAndPan",
        );
    }
}