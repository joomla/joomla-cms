<?php

namespace enshrined\svgSanitize;

use enshrined\svgSanitize\data\AllowedAttributes;
use enshrined\svgSanitize\data\AllowedTags;
use enshrined\svgSanitize\data\AttributeInterface;
use enshrined\svgSanitize\data\TagInterface;
use enshrined\svgSanitize\data\XPath;
use enshrined\svgSanitize\ElementReference\Resolver;
use enshrined\svgSanitize\ElementReference\Subject;

/**
 * Class Sanitizer
 *
 * @package enshrined\svgSanitize
 */
class Sanitizer
{

    /**
     * @var \DOMDocument
     */
    protected $xmlDocument;

    /**
     * @var array
     */
    protected $allowedTags;

    /**
     * @var array
     */
    protected $allowedAttrs;

    /**
     * @var
     */
    protected $xmlLoaderValue;

    /**
     * @var bool
     */
    protected $minifyXML = false;

    /**
     * @var bool
     */
    protected $removeRemoteReferences = false;

    /**
     * @var int
     */
    protected $useThreshold = 1000;

    /**
     * @var bool
     */
    protected $removeXMLTag = false;

    /**
     * @var int
     */
    protected $xmlOptions = LIBXML_NOEMPTYTAG;

    /**
     * @var array
     */
    protected $xmlIssues = array();

    /**
     * @var Resolver
     */
    protected $elementReferenceResolver;

    /**
     * @var int
     */
    protected $useNestingLimit = 15;

    /**
     *
     */
    function __construct()
    {
        // Load default tags/attributes
        $this->allowedAttrs = array_map('strtolower', AllowedAttributes::getAttributes());
        $this->allowedTags = array_map('strtolower', AllowedTags::getTags());
    }

    /**
     * Set up the DOMDocument
     */
    protected function resetInternal()
    {
        $this->xmlDocument = new \DOMDocument();
        $this->xmlDocument->preserveWhiteSpace = false;
        $this->xmlDocument->strictErrorChecking = false;
        $this->xmlDocument->formatOutput = !$this->minifyXML;
    }

    /**
     * Set XML options to use when saving XML
     * See: DOMDocument::saveXML
     *
     * @param int  $xmlOptions
     */
    public function setXMLOptions($xmlOptions)
    {
        $this->xmlOptions = $xmlOptions;
    }

    /**
     * Get XML options to use when saving XML
     * See: DOMDocument::saveXML
     *
     * @return int
     */
    public function getXMLOptions()
    {
        return $this->xmlOptions;
    }

    /**
     * Get the array of allowed tags
     *
     * @return array
     */
    public function getAllowedTags()
    {
        return $this->allowedTags;
    }

    /**
     * Set custom allowed tags
     *
     * @param TagInterface $allowedTags
     */
    public function setAllowedTags(TagInterface $allowedTags)
    {
        $this->allowedTags = array_map('strtolower', $allowedTags::getTags());
    }

    /**
     * Get the array of allowed attributes
     *
     * @return array
     */
    public function getAllowedAttrs()
    {
        return $this->allowedAttrs;
    }

    /**
     * Set custom allowed attributes
     *
     * @param AttributeInterface $allowedAttrs
     */
    public function setAllowedAttrs(AttributeInterface $allowedAttrs)
    {
        $this->allowedAttrs = array_map('strtolower', $allowedAttrs::getAttributes());
    }

    /**
     * Should we remove references to remote files?
     *
     * @param bool $removeRemoteRefs
     */
    public function removeRemoteReferences($removeRemoteRefs = false)
    {
        $this->removeRemoteReferences = $removeRemoteRefs;
    }

    /**
     * Get XML issues.
     *
     * @return array
     */
    public function getXmlIssues() {
        return $this->xmlIssues;
    }


    /**
     * Sanitize the passed string
     *
     * @param string $dirty
     * @return string
     */
    public function sanitize($dirty)
    {
        // Don't run on an empty string
        if (empty($dirty)) {
            return '';
        }

        // Strip php tags
        $dirty = preg_replace('/<\?(=|php)(.+?)\?>/i', '', $dirty);

        $this->resetInternal();
        $this->setUpBefore();

        $loaded = $this->xmlDocument->loadXML($dirty);

        // If we couldn't parse the XML then we go no further. Reset and return false
        if (!$loaded) {
            $this->resetAfter();
            return false;
        }

        $this->removeDoctype();

        // Pre-process all identified elements
        $xPath = new XPath($this->xmlDocument);
        $this->elementReferenceResolver = new Resolver($xPath, $this->useNestingLimit);
        $this->elementReferenceResolver->collect();
        $elementsToRemove = $this->elementReferenceResolver->getElementsToRemove();

        // Grab all the elements
        $allElements = $this->xmlDocument->getElementsByTagName("*");

        // Start the cleaning proccess
        $this->startClean($allElements, $elementsToRemove);

        // Save cleaned XML to a variable
        if ($this->removeXMLTag) {
            $clean = $this->xmlDocument->saveXML($this->xmlDocument->documentElement, $this->xmlOptions);
        } else {
            $clean = $this->xmlDocument->saveXML($this->xmlDocument, $this->xmlOptions);
        }

        $this->resetAfter();

        // Remove any extra whitespaces when minifying
        if ($this->minifyXML) {
            $clean = preg_replace('/\s+/', ' ', $clean);
        }

        // Return result
        return $clean;
    }

    /**
     * Set up libXML before we start
     */
    protected function setUpBefore()
    {
        // Turn off the entity loader
        $this->xmlLoaderValue = libxml_disable_entity_loader(true);

        // Suppress the errors because we don't really have to worry about formation before cleansing
        libxml_use_internal_errors(true);

        // Reset array of altered XML
        $this->xmlIssues = array();
    }

    /**
     * Reset the class after use
     */
    protected function resetAfter()
    {
        // Reset the entity loader
        libxml_disable_entity_loader($this->xmlLoaderValue);
    }

    /**
     * Remove the XML Doctype
     * It may be caught later on output but that seems to be buggy, so we need to make sure it's gone
     */
    protected function removeDoctype()
    {
        foreach ($this->xmlDocument->childNodes as $child) {
            if ($child->nodeType === XML_DOCUMENT_TYPE_NODE) {
                $child->parentNode->removeChild($child);
            }
        }
    }

    /**
     * Start the cleaning with tags, then we move onto attributes and hrefs later
     *
     * @param \DOMNodeList $elements
     * @param array        $elementsToRemove
     */
    protected function startClean(\DOMNodeList $elements, array $elementsToRemove)
    {
        // loop through all elements
        // we do this backwards so we don't skip anything if we delete a node
        // see comments at: http://php.net/manual/en/class.domnamednodemap.php
        for ($i = $elements->length - 1; $i >= 0; $i--) {
            /** @var \DOMElement $currentElement */
            $currentElement = $elements->item($i);

            /**
             * If the element has exceeded the nesting limit, we should remove it.
             *
             * As it's only <use> elements that cause us issues with nesting DOS attacks
             * we should check what the element is before removing it. For now we'll only
             * remove <use> elements.
             */
            if (in_array($currentElement, $elementsToRemove) && 'use' === $currentElement->nodeName) {
                $currentElement->parentNode->removeChild($currentElement);
                $this->xmlIssues[] = array(
                    'message' => 'Invalid \'' . $currentElement->tagName . '\'',
                    'line'    => $currentElement->getLineNo(),
                );
                continue;
            }

            // If the tag isn't in the whitelist, remove it and continue with next iteration
            if (!in_array(strtolower($currentElement->tagName), $this->allowedTags)) {
                $currentElement->parentNode->removeChild($currentElement);
                $this->xmlIssues[] = array(
                    'message' => 'Suspicious tag \'' . $currentElement->tagName . '\'',
                    'line' => $currentElement->getLineNo(),
                );
                continue;
            }

            $this->cleanHrefs($currentElement);

            $this->cleanXlinkHrefs($currentElement);

            $this->cleanAttributesOnWhitelist($currentElement);

            if (strtolower($currentElement->tagName) === 'use') {
                if ($this->isUseTagDirty($currentElement)
                    || $this->isUseTagExceedingThreshold($currentElement)
                ) {
                    $currentElement->parentNode->removeChild($currentElement);
                    $this->xmlIssues[] = array(
                        'message' => 'Suspicious \'' . $currentElement->tagName . '\'',
                        'line' => $currentElement->getLineNo(),
                    );
                    continue;
                }
            }
        }
    }

    /**
     * Only allow attributes that are on the whitelist
     *
     * @param \DOMElement $element
     */
    protected function cleanAttributesOnWhitelist(\DOMElement $element)
    {
        for ($x = $element->attributes->length - 1; $x >= 0; $x--) {
            // get attribute name
            $attrName = $element->attributes->item($x)->name;

            // Remove attribute if not in whitelist
            if (!in_array(strtolower($attrName), $this->allowedAttrs) && !$this->isAriaAttribute(strtolower($attrName)) && !$this->isDataAttribute(strtolower($attrName))) {

                $element->removeAttribute($attrName);
                $this->xmlIssues[] = array(
                    'message' => 'Suspicious attribute \'' . $attrName . '\'',
                    'line' => $element->getLineNo(),
                );
            }

            /**
             * This is used for when a namespace isn't imported properly.
             * Such as xlink:href when the xlink namespace isn't imported.
             * We have to do this as the link is still ran in this case.
             */
            if (false !== strpos($attrName, 'href')) {
                $href = $element->getAttribute($attrName);
                if (false === $this->isHrefSafeValue($href)) {
                    $element->removeAttribute($attrName);
                    $this->xmlIssues[] = array(
                        'message' => 'Suspicious attribute \'href\'',
                        'line'    => $element->getLineNo(),
                    );
                }
            }

            // Do we want to strip remote references?
            if($this->removeRemoteReferences) {
                // Remove attribute if it has a remote reference
                if (isset($element->attributes->item($x)->value) && $this->hasRemoteReference($element->attributes->item($x)->value)) {
                    $element->removeAttribute($attrName);
                    $this->xmlIssues[] = array(
                        'message' => 'Suspicious attribute \'' . $attrName . '\'',
                        'line' => $element->getLineNo(),
                    );
                }
            }
        }
    }

    /**
     * Clean the xlink:hrefs of script and data embeds
     *
     * @param \DOMElement $element
     */
    protected function cleanXlinkHrefs(\DOMElement $element)
    {
        $xlinks = $element->getAttributeNS('http://www.w3.org/1999/xlink', 'href');
        if (false === $this->isHrefSafeValue($xlinks)) {
            $element->removeAttributeNS( 'http://www.w3.org/1999/xlink', 'href' );
            $this->xmlIssues[] = array(
                'message' => 'Suspicious attribute \'href\'',
                'line' => $element->getLineNo(),
            );
        }
    }

    /**
     * Clean the hrefs of script and data embeds
     *
     * @param \DOMElement $element
     */
    protected function cleanHrefs(\DOMElement $element)
    {
        $href = $element->getAttribute('href');
        if (false === $this->isHrefSafeValue($href)) {
            $element->removeAttribute('href');
            $this->xmlIssues[] = array(
                'message' => 'Suspicious attribute \'href\'',
                'line' => $element->getLineNo(),
            );
        }
    }

/**
 * Only allow whitelisted starts to be within the href.
 *
 * This will stop scripts etc from being passed through, with or without attempting to hide bypasses.
 * This stops the need for us to use a complicated script regex.
 *
 * @param $value
 * @return bool
 */
    protected function isHrefSafeValue($value) {

        // Allow fragment identifiers.
        if ('#' === substr($value, 0, 1)) {
            return true;
        }

        // Allow relative URIs.
        if ('/' === substr($value, 0, 1)) {
            return true;
        }

        // Allow HTTPS domains.
        if ('https://' === substr($value, 0, 8)) {
            return true;
        }

        // Allow HTTP domains.
        if ('http://' === substr($value, 0, 7)) {
            return true;
        }

        // Allow known data URIs.
        if (in_array(substr($value, 0, 14), array(
            'data:image/png', // PNG
            'data:image/gif', // GIF
            'data:image/jpg', // JPG
            'data:image/jpe', // JPEG
            'data:image/pjp', // PJPEG
        ))) {
           return true;
        }

        // Allow known short data URIs.
        if (in_array(substr($value, 0, 12), array(
            'data:img/png', // PNG
            'data:img/gif', // GIF
            'data:img/jpg', // JPG
            'data:img/jpe', // JPEG
            'data:img/pjp', // PJPEG
        ))) {
            return true;
        }

        return false;
    }

    /**
     * Removes non-printable ASCII characters from string & trims it
     *
     * @param string $value
     * @return bool
     */
    protected function removeNonPrintableCharacters($value)
    {
        return trim(preg_replace('/[^ -~]/xu','',$value));
    }

    /**
     * Does this attribute value have a remote reference?
     *
     * @param $value
     * @return bool
     */
    protected function hasRemoteReference($value)
    {
        $value = $this->removeNonPrintableCharacters($value);

        $wrapped_in_url = preg_match('~^url\(\s*[\'"]\s*(.*)\s*[\'"]\s*\)$~xi', $value, $match);
        if (!$wrapped_in_url){
            return false;
        }

        $value = trim($match[1], '\'"');

        return preg_match('~^((https?|ftp|file):)?//~xi', $value);
    }

    /**
     * Should we minify the output?
     *
     * @param bool $shouldMinify
     */
    public function minify($shouldMinify = false)
    {
        $this->minifyXML = (bool) $shouldMinify;
    }

    /**
     * Should we remove the XML tag in the header?
     *
     * @param bool $removeXMLTag
     */
    public function removeXMLTag($removeXMLTag = false)
    {
        $this->removeXMLTag = (bool) $removeXMLTag;
    }

    /**
     * Whether `<use ... xlink:href="#identifier">` elements shall be
     * removed in case expansion would exceed this threshold.
     *
     * @param int $useThreshold
     */
    public function useThreshold($useThreshold = 1000)
    {
        $this->useThreshold = (int)$useThreshold;
    }

    /**
     * Check to see if an attribute is an aria attribute or not
     *
     * @param $attributeName
     *
     * @return bool
     */
    protected function isAriaAttribute($attributeName)
    {
        return strpos($attributeName, 'aria-') === 0;
    }

    /**
     * Check to see if an attribute is an data attribute or not
     *
     * @param $attributeName
     *
     * @return bool
     */
    protected function isDataAttribute($attributeName)
    {
        return strpos($attributeName, 'data-') === 0;
    }

    /**
     * Make sure our use tag is only referencing internal resources
     *
     * @param \DOMElement $element
     * @return bool
     */
    protected function isUseTagDirty(\DOMElement $element)
    {
        $href = Helper::getElementHref($element);
        return $href && strpos($href, '#') !== 0;
    }

    /**
     * Determines whether `<use ... xlink:href="#identifier">` is expanded
     * recursively in order to create DoS scenarios. The amount of a actually
     * used element needs to be below `$this->useThreshold`.
     *
     * @param \DOMElement $element
     * @return bool
     */
    protected function isUseTagExceedingThreshold(\DOMElement $element)
    {
        if ($this->useThreshold <= 0) {
            return false;
        }
        $useId = Helper::extractIdReferenceFromHref(
            Helper::getElementHref($element)
        );
        if ($useId === null) {
            return false;
        }
        foreach ($this->elementReferenceResolver->findByElementId($useId) as $subject) {
            if ($subject->countUse() >= $this->useThreshold) {
                return true;
            }
        }
        return false;
    }

    /**
     * Set the nesting limit for <use> tags.
     *
     * @param $limit
     */
    public function setUseNestingLimit($limit)
    {
        $this->useNestingLimit = (int) $limit;
    }
}
