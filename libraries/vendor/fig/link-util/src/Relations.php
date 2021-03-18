<?php

/**
 * Standard relation names.
 *
 * This file is auto-generated.  Do not edit directly.  Edit or re-run `rebuild-rels.php` if necessary.
 */

declare(strict_types=1);

namespace Fig\Link;

/**
 * Standard relation names.
 *
 * This interface provides convenience constants for standard relationships defined by IANA. They are not required,
 * but are useful for avoiding typos and similar such errors.
 *
 * This interface may be referenced directly like so:
 *
 * Relations::REL_UP
 *
 * Or you may implement this interface in your class and then refer to the constants locally:
 *
 * static::REL_UP
 */
interface Relations
{
    /**
     * Refers to a resource that is the subject of the link's context.
     *
     * @see https://tools.ietf.org/html/rfc6903
     */
    const REL_ABOUT = 'about';

    /**
     * Refers to a substitute for this context
     *
     * @see http://www.w3.org/TR/html5/links.html#link-type-alternate
     */
    const REL_ALTERNATE = 'alternate';

    /**
     * Used to reference alternative content that uses the AMP profile of the HTML format.
     *
     * @see https://amp.dev/documentation/guides-and-tutorials/learn/spec/amphtml/
     */
    const REL_AMPHTML = 'amphtml';

    /**
     * Refers to an appendix.
     *
     * @see https://www.w3.org/TR/html401/
     */
    const REL_APPENDIX = 'appendix';

    /**
     * Refers to an icon for the context. Synonym for icon.
     *
     * @see https://developer.apple.com/library/archive/documentation/AppleApplications/Reference/SafariWebContent/ConfiguringWebApplications/ConfiguringWebApplications.html#//apple_ref/doc/uid/TP40002051-CH3-SW3
     */
    const REL_APPLE_TOUCH_ICON = 'apple-touch-icon';

    /**
     * Refers to a launch screen for the context.
     *
     * @see https://developer.apple.com/library/archive/documentation/AppleApplications/Reference/SafariWebContent/ConfiguringWebApplications/ConfiguringWebApplications.html#//apple_ref/doc/uid/TP40002051-CH3-SW3
     */
    const REL_APPLE_TOUCH_STARTUP_IMAGE = 'apple-touch-startup-image';

    /**
     * Refers to a collection of records, documents, or other materials of historical interest.
     *
     * @see http://www.w3.org/TR/2011/WD-html5-20110113/links.html#rel-archives
     */
    const REL_ARCHIVES = 'archives';

    /**
     * Refers to the context's author.
     *
     * @see http://www.w3.org/TR/html5/links.html#link-type-author
     */
    const REL_AUTHOR = 'author';

    /**
     * Identifies the entity that blocks access to a resource following receipt of a legal demand.
     *
     * @see https://tools.ietf.org/html/rfc7725
     */
    const REL_BLOCKED_BY = 'blocked-by';

    /**
     * Gives a permanent link to use for bookmarking purposes.
     *
     * @see http://www.w3.org/TR/html5/links.html#link-type-bookmark
     */
    const REL_BOOKMARK = 'bookmark';

    /**
     * Designates the preferred version of a resource (the IRI and its contents).
     *
     * @see https://tools.ietf.org/html/rfc6596
     */
    const REL_CANONICAL = 'canonical';

    /**
     * Refers to a chapter in a collection of resources.
     *
     * @see https://www.w3.org/TR/html401/
     */
    const REL_CHAPTER = 'chapter';

    /**
     * Indicates that the link target is preferred over the link context for the purpose of permanent citation.
     *
     * @see https://tools.ietf.org/html/rfc8574
     */
    const REL_CITE_AS = 'cite-as';

    /**
     * The target IRI points to a resource which represents the collection resource for the context IRI.
     *
     * @see https://tools.ietf.org/html/rfc6573
     */
    const REL_COLLECTION = 'collection';

    /**
     * Refers to a table of contents.
     *
     * @see https://www.w3.org/TR/html401/
     */
    const REL_CONTENTS = 'contents';

    /**
     * The document linked to was later converted to the document that contains this link relation. For example, an RFC can
     * have a link to the Internet-Draft that became the RFC; in that case, the link relation would be "convertedFrom".
     *
     * This relation is different than "predecessor-version" in that "predecessor-version" is for items in a version control
     * system. It is also different than "previous" in that this relation is used for converted resources, not those that are
     * part of a sequence of resources.
     *
     * @see https://tools.ietf.org/html/rfc7991
     */
    const REL_CONVERTEDFROM = 'convertedFrom';

    /**
     * Refers to a copyright statement that applies to the link's context.
     *
     * @see https://www.w3.org/TR/html401/
     */
    const REL_COPYRIGHT = 'copyright';

    /**
     * The target IRI points to a resource where a submission form can be obtained.
     *
     * @see https://tools.ietf.org/html/rfc6861
     */
    const REL_CREATE_FORM = 'create-form';

    /**
     * Refers to a resource containing the most recent item(s) in a collection of resources.
     *
     * @see https://tools.ietf.org/html/rfc5005
     */
    const REL_CURRENT = 'current';

    /**
     * Refers to a resource providing information about the link's context.
     *
     * @see http://www.w3.org/TR/powder-dr/#assoc-linking
     */
    const REL_DESCRIBEDBY = 'describedby';

    /**
     * The relationship A 'describes' B asserts that resource A provides a description of resource B. There are no constraints
     * on the format or representation of either A or B, neither are there any further constraints on either resource.
     *
     * This link relation type is the inverse of the 'describedby' relation type. While 'describedby' establishes a relation
     * from the described resource back to the resource that describes it, 'describes' established a relation from the
     * describing resource to the resource it describes. If B is 'describedby' A, then A 'describes' B.
     *
     * @see https://tools.ietf.org/html/rfc6892
     */
    const REL_DESCRIBES = 'describes';

    /**
     * Refers to a list of patent disclosures made with respect to material for which 'disclosure' relation is specified.
     *
     * @see https://tools.ietf.org/html/rfc6579
     */
    const REL_DISCLOSURE = 'disclosure';

    /**
     * Used to indicate an origin that will be used to fetch required resources for the link context, and that the user agent
     * ought to resolve as early as possible.
     *
     * @see https://www.w3.org/TR/resource-hints/
     */
    const REL_DNS_PREFETCH = 'dns-prefetch';

    /**
     * Refers to a resource whose available representations are byte-for-byte identical with the corresponding representations
     * of the context IRI.
     *
     * This relation is for static resources. That is, an HTTP GET request on any duplicate will return the same
     * representation. It does not make sense for dynamic or POSTable resources and should not be used for them. 
     *
     * @see https://tools.ietf.org/html/rfc6249
     */
    const REL_DUPLICATE = 'duplicate';

    /**
     * Refers to a resource that can be used to edit the link's context.
     *
     * @see https://tools.ietf.org/html/rfc5023
     */
    const REL_EDIT = 'edit';

    /**
     * The target IRI points to a resource where a submission form for editing associated resource can be obtained.
     *
     * @see https://tools.ietf.org/html/rfc6861
     */
    const REL_EDIT_FORM = 'edit-form';

    /**
     * Refers to a resource that can be used to edit media associated with the link's context.
     *
     * @see https://tools.ietf.org/html/rfc5023
     */
    const REL_EDIT_MEDIA = 'edit-media';

    /**
     * Identifies a related resource that is potentially large and might require special handling.
     *
     * @see https://tools.ietf.org/html/rfc4287
     */
    const REL_ENCLOSURE = 'enclosure';

    /**
     * Refers to a resource that is not part of the same site as the current context.
     *
     * @see https://html.spec.whatwg.org/multipage/links.html#link-type-external
     */
    const REL_EXTERNAL = 'external';

    /**
     * An IRI that refers to the furthest preceding resource in a series of resources.
     *
     * This relation type registration did not indicate a reference. Originally requested by Mark Nottingham in December 2004. 
     *
     * @see https://tools.ietf.org/html/rfc8288
     */
    const REL_FIRST = 'first';

    /**
     * Refers to a glossary of terms.
     *
     * @see https://www.w3.org/TR/html401/
     */
    const REL_GLOSSARY = 'glossary';

    /**
     * Refers to context-sensitive help.
     *
     * @see http://www.w3.org/TR/html5/links.html#link-type-help
     */
    const REL_HELP = 'help';

    /**
     * Refers to a resource hosted by the server indicated by the link context.
     *
     * This relation is used in CoRE where links are retrieved as a "/.well-known/core" resource representation, and is the
     * default relation type in the CoRE Link Format.
     *
     * @see https://tools.ietf.org/html/rfc6690
     */
    const REL_HOSTS = 'hosts';

    /**
     * Refers to a hub that enables registration for notification of updates to the context.
     *
     * This relation type was requested by Brett Slatkin.
     *
     * @see https://www.w3.org/TR/websub/
     */
    const REL_HUB = 'hub';

    /**
     * Refers to an icon representing the link's context.
     *
     * @see http://www.w3.org/TR/html5/links.html#link-type-icon
     */
    const REL_ICON = 'icon';

    /**
     * Refers to an index.
     *
     * @see https://www.w3.org/TR/html401/
     */
    const REL_INDEX = 'index';

    /**
     * refers to a resource associated with a time interval that ends before the beginning of the time interval associated with
     * the context resource
     *
     * @see https://www.w3.org/TR/owl-time/#time:intervalAfter
     */
    const REL_INTERVALAFTER = 'intervalAfter';

    /**
     * refers to a resource associated with a time interval that begins after the end of the time interval associated with the
     * context resource
     *
     * @see https://www.w3.org/TR/owl-time/#time:intervalBefore
     */
    const REL_INTERVALBEFORE = 'intervalBefore';

    /**
     * refers to a resource associated with a time interval that begins after the beginning of the time interval associated
     * with the context resource, and ends before the end of the time interval associated with the context resource
     *
     * @see https://www.w3.org/TR/owl-time/#time:intervalContains
     */
    const REL_INTERVALCONTAINS = 'intervalContains';

    /**
     * refers to a resource associated with a time interval that begins after the end of the time interval associated with the
     * context resource, or ends before the beginning of the time interval associated with the context resource
     *
     * @see https://www.w3.org/TR/owl-time/#time:intervalDisjoint
     */
    const REL_INTERVALDISJOINT = 'intervalDisjoint';

    /**
     * refers to a resource associated with a time interval that begins before the beginning of the time interval associated
     * with the context resource, and ends after the end of the time interval associated with the context resource
     *
     * @see https://www.w3.org/TR/owl-time/#time:intervalDuring
     */
    const REL_INTERVALDURING = 'intervalDuring';

    /**
     * refers to a resource associated with a time interval whose beginning coincides with the beginning of the time interval
     * associated with the context resource, and whose end coincides with the end of the time interval associated with the
     * context resource
     *
     * @see https://www.w3.org/TR/owl-time/#time:intervalEquals
     */
    const REL_INTERVALEQUALS = 'intervalEquals';

    /**
     * refers to a resource associated with a time interval that begins after the beginning of the time interval associated
     * with the context resource, and whose end coincides with the end of the time interval associated with the context
     * resource
     *
     * @see https://www.w3.org/TR/owl-time/#time:intervalFinishedBy
     */
    const REL_INTERVALFINISHEDBY = 'intervalFinishedBy';

    /**
     * refers to a resource associated with a time interval that begins before the beginning of the time interval associated
     * with the context resource, and whose end coincides with the end of the time interval associated with the context
     * resource
     *
     * @see https://www.w3.org/TR/owl-time/#time:intervalFinishes
     */
    const REL_INTERVALFINISHES = 'intervalFinishes';

    /**
     * refers to a resource associated with a time interval that begins before or is coincident with the beginning of the time
     * interval associated with the context resource, and ends after or is coincident with the end of the time interval
     * associated with the context resource
     *
     * @see https://www.w3.org/TR/owl-time/#time:intervalIn
     */
    const REL_INTERVALIN = 'intervalIn';

    /**
     * refers to a resource associated with a time interval whose beginning coincides with the end of the time interval
     * associated with the context resource
     *
     * @see https://www.w3.org/TR/owl-time/#time:intervalMeets
     */
    const REL_INTERVALMEETS = 'intervalMeets';

    /**
     * refers to a resource associated with a time interval whose end coincides with the beginning of the time interval
     * associated with the context resource
     *
     * @see https://www.w3.org/TR/owl-time/#time:intervalMetBy
     */
    const REL_INTERVALMETBY = 'intervalMetBy';

    /**
     * refers to a resource associated with a time interval that begins before the beginning of the time interval associated
     * with the context resource, and ends after the beginning of the time interval associated with the context resource
     *
     * @see https://www.w3.org/TR/owl-time/#time:intervalOverlappedBy
     */
    const REL_INTERVALOVERLAPPEDBY = 'intervalOverlappedBy';

    /**
     * refers to a resource associated with a time interval that begins before the end of the time interval associated with the
     * context resource, and ends after the end of the time interval associated with the context resource
     *
     * @see https://www.w3.org/TR/owl-time/#time:intervalOverlaps
     */
    const REL_INTERVALOVERLAPS = 'intervalOverlaps';

    /**
     * refers to a resource associated with a time interval whose beginning coincides with the beginning of the time interval
     * associated with the context resource, and ends before the end of the time interval associated with the context resource
     *
     * @see https://www.w3.org/TR/owl-time/#time:intervalStartedBy
     */
    const REL_INTERVALSTARTEDBY = 'intervalStartedBy';

    /**
     * refers to a resource associated with a time interval whose beginning coincides with the beginning of the time interval
     * associated with the context resource, and ends after the end of the time interval associated with the context resource
     *
     * @see https://www.w3.org/TR/owl-time/#time:intervalStarts
     */
    const REL_INTERVALSTARTS = 'intervalStarts';

    /**
     * The target IRI points to a resource that is a member of the collection represented by the context IRI.
     *
     * @see https://tools.ietf.org/html/rfc6573
     */
    const REL_ITEM = 'item';

    /**
     * An IRI that refers to the furthest following resource in a series of resources.
     *
     * This relation type registration did not indicate a reference. Originally requested by Mark Nottingham in December 2004. 
     *
     * @see https://tools.ietf.org/html/rfc8288
     */
    const REL_LAST = 'last';

    /**
     * Points to a resource containing the latest (e.g., current) version of the context.
     *
     * @see https://tools.ietf.org/html/rfc5829
     */
    const REL_LATEST_VERSION = 'latest-version';

    /**
     * Refers to a license associated with this context.
     *
     * For implications of use in HTML, see: http://www.w3.org/TR/html5/links.html#link-type-license
     *
     * @see https://tools.ietf.org/html/rfc4946
     */
    const REL_LICENSE = 'license';

    /**
     * Refers to further information about the link's context, expressed as a LRDD ("Link-based Resource Descriptor Document")
     * resource. See for information about processing this relation type in host-meta documents. When used elsewhere, it refers
     * to additional links and other metadata. Multiple instances indicate additional LRDD resources. LRDD resources MUST have
     * an "application/xrd+xml" representation, and MAY have others.
     *
     * @see https://tools.ietf.org/html/rfc6415
     */
    const REL_LRDD = 'lrdd';

    /**
     * Links to a manifest file for the context.
     *
     * @see https://www.w3.org/TR/appmanifest/#using-a-link-element-to-link-to-a-manifest
     */
    const REL_MANIFEST = 'manifest';

    /**
     * Refers to a mask that can be applied to the icon for the context.
     *
     * @see https://developer.apple.com/library/archive/documentation/AppleApplications/Reference/SafariWebContent/pinnedTabs/pinnedTabs.html#//apple_ref/doc/uid/TP40002051-CH18-SW1
     */
    const REL_MASK_ICON = 'mask-icon';

    /**
     * The Target IRI points to a Memento, a fixed resource that will not change state anymore.
     *
     * A Memento for an Original Resource is a resource that encapsulates a prior state of the Original Resource.
     *
     * @see https://tools.ietf.org/html/rfc7089
     */
    const REL_MEMENTO = 'memento';

    /**
     * Links to the context's Micropub endpoint.
     *
     * @see https://www.w3.org/TR/micropub/#endpoint-discovery-p-1
     */
    const REL_MICROPUB = 'micropub';

    /**
     * Refers to a module that the user agent is to preemptively fetch and store for use in the current context.
     *
     * @see https://html.spec.whatwg.org/multipage/links.html#link-type-modulepreload
     */
    const REL_MODULEPRELOAD = 'modulepreload';

    /**
     * Refers to a resource that can be used to monitor changes in an HTTP resource. 
     *
     * @see https://tools.ietf.org/html/rfc5989
     */
    const REL_MONITOR = 'monitor';

    /**
     * Refers to a resource that can be used to monitor changes in a specified group of HTTP resources. 
     *
     * @see https://tools.ietf.org/html/rfc5989
     */
    const REL_MONITOR_GROUP = 'monitor-group';

    /**
     * Indicates that the link's context is a part of a series, and that the next in the series is the link target. 
     *
     * @see http://www.w3.org/TR/html5/links.html#link-type-next
     */
    const REL_NEXT = 'next';

    /**
     * Refers to the immediately following archive resource.
     *
     * @see https://tools.ietf.org/html/rfc5005
     */
    const REL_NEXT_ARCHIVE = 'next-archive';

    /**
     * Indicates that the context’s original author or publisher does not endorse the link target.
     *
     * @see http://www.w3.org/TR/html5/links.html#link-type-nofollow
     */
    const REL_NOFOLLOW = 'nofollow';

    /**
     * Indicates that any newly created top-level browsing context which results from following the link will not be an
     * auxiliary browsing context.
     *
     * @see https://html.spec.whatwg.org/multipage/links.html#link-type-noopener
     */
    const REL_NOOPENER = 'noopener';

    /**
     * Indicates that no referrer information is to be leaked when following the link.
     *
     * @see http://www.w3.org/TR/html5/links.html#link-type-noreferrer
     */
    const REL_NOREFERRER = 'noreferrer';

    /**
     * Indicates that any newly created top-level browsing context which results from following the link will be an auxiliary
     * browsing context.
     *
     * @see https://html.spec.whatwg.org/multipage/links.html#link-type-opener
     */
    const REL_OPENER = 'opener';

    /**
     * Refers to an OpenID Authentication server on which the context relies for an assertion that the end user controls an
     * Identifier.
     *
     * @see https://openid.net/specs/openid-authentication-2_0.html#rfc.section.7.3.3
     */
    const REL_OPENID2_LOCAL_ID = 'openid2.local_id';

    /**
     * Refers to a resource which accepts OpenID Authentication protocol messages for the context.
     *
     * @see https://openid.net/specs/openid-authentication-2_0.html#rfc.section.7.3.3
     */
    const REL_OPENID2_PROVIDER = 'openid2.provider';

    /**
     * The Target IRI points to an Original Resource.
     *
     * An Original Resource is a resource that exists or used to exist, and for which access to one of its prior states may be
     * required. 
     *
     * @see https://tools.ietf.org/html/rfc7089
     */
    const REL_ORIGINAL = 'original';

    /**
     * Refers to a P3P privacy policy for the context.
     *
     * @see https://www.w3.org/TR/P3P/#syntax_link
     */
    const REL_P3PV1 = 'P3Pv1';

    /**
     * Indicates a resource where payment is accepted.
     *
     * This relation type registration did not indicate a reference. Requested by Joshua Kinberg and Robert Sayre. It is meant
     * as a general way to facilitate acts of payment, and thus this specification makes no assumptions on the type of payment
     * or transaction protocol. Examples may include a web page where donations are accepted or where goods and services are
     * available for purchase. rel="payment" is not intended to initiate an automated transaction. In Atom documents, a link
     * element with a rel="payment" attribute may exist at the feed/channel level and/or the entry/item level. For example, a
     * rel="payment" link at the feed/channel level may point to a "tip jar" URI, whereas an entry/ item containing a book
     * review may include a rel="payment" link that points to the location where the book may be purchased through an online
     * retailer. 
     *
     * @see https://tools.ietf.org/html/rfc8288
     */
    const REL_PAYMENT = 'payment';

    /**
     * Gives the address of the pingback resource for the link context.
     *
     * @see http://www.hixie.ch/specs/pingback/pingback
     */
    const REL_PINGBACK = 'pingback';

    /**
     * Used to indicate an origin that will be used to fetch required resources for the link context. Initiating an early
     * connection, which includes the DNS lookup, TCP handshake, and optional TLS negotiation, allows the user agent to mask
     * the high latency costs of establishing a connection.
     *
     * @see https://www.w3.org/TR/resource-hints/
     */
    const REL_PRECONNECT = 'preconnect';

    /**
     * Points to a resource containing the predecessor version in the version history. 
     *
     * @see https://tools.ietf.org/html/rfc5829
     */
    const REL_PREDECESSOR_VERSION = 'predecessor-version';

    /**
     * The prefetch link relation type is used to identify a resource that might be required by the next navigation from the
     * link context, and that the user agent ought to fetch, such that the user agent can deliver a faster response once the
     * resource is requested in the future.
     *
     * @see http://www.w3.org/TR/resource-hints/
     */
    const REL_PREFETCH = 'prefetch';

    /**
     * Refers to a resource that should be loaded early in the processing of the link's context, without blocking rendering.
     *
     * Additional target attributes establish the detailed fetch properties of the link.
     *
     * @see http://www.w3.org/TR/preload/
     */
    const REL_PRELOAD = 'preload';

    /**
     * Used to identify a resource that might be required by the next navigation from the link context, and that the user agent
     * ought to fetch and execute, such that the user agent can deliver a faster response once the resource is requested in the
     * future.
     *
     * @see https://www.w3.org/TR/resource-hints/
     */
    const REL_PRERENDER = 'prerender';

    /**
     * Indicates that the link's context is a part of a series, and that the previous in the series is the link target. 
     *
     * @see http://www.w3.org/TR/html5/links.html#link-type-prev
     */
    const REL_PREV = 'prev';

    /**
     * Refers to a resource that provides a preview of the link's context.
     *
     * @see https://tools.ietf.org/html/rfc6903
     */
    const REL_PREVIEW = 'preview';

    /**
     * Refers to the previous resource in an ordered series of resources. Synonym for "prev".
     *
     * @see https://www.w3.org/TR/html401/
     */
    const REL_PREVIOUS = 'previous';

    /**
     * Refers to the immediately preceding archive resource.
     *
     * @see https://tools.ietf.org/html/rfc5005
     */
    const REL_PREV_ARCHIVE = 'prev-archive';

    /**
     * Refers to a privacy policy associated with the link's context.
     *
     * @see https://tools.ietf.org/html/rfc6903
     */
    const REL_PRIVACY_POLICY = 'privacy-policy';

    /**
     * Identifying that a resource representation conforms
     * to a certain profile, without affecting the non-profile semantics
     * of the resource representation.
     *
     * Profile URIs are primarily intended to be used as
     * identifiers, and thus clients SHOULD NOT indiscriminately access
     * profile URIs.
     *
     * @see https://tools.ietf.org/html/rfc6906
     */
    const REL_PROFILE = 'profile';

    /**
     * Links to a publication manifest. A manifest represents structured information about a publication, such as informative
     * metadata, a list of resources, and a default reading order.
     *
     * @see https://www.w3.org/TR/pub-manifest/#link-relation-type-registration
     */
    const REL_PUBLICATION = 'publication';

    /**
     * Identifies a related resource.
     *
     * @see https://tools.ietf.org/html/rfc4287
     */
    const REL_RELATED = 'related';

    /**
     * Identifies the root of RESTCONF API as configured on this HTTP server. The "restconf" relation defines the root of the
     * API defined in RFC8040. Subsequent revisions of RESTCONF will use alternate relation values to support protocol
     * versioning.
     *
     * @see https://tools.ietf.org/html/rfc8040
     */
    const REL_RESTCONF = 'restconf';

    /**
     * Identifies a resource that is a reply to the context of the link. 
     *
     * @see https://tools.ietf.org/html/rfc4685
     */
    const REL_REPLIES = 'replies';

    /**
     * Refers to a resource that can be used to search through the link's context and related resources.
     *
     * @see http://www.opensearch.org/Specifications/OpenSearch/1.1
     */
    const REL_SEARCH = 'search';

    /**
     * Refers to a section in a collection of resources.
     *
     * @see https://www.w3.org/TR/html401/
     */
    const REL_SECTION = 'section';

    /**
     * Conveys an identifier for the link's context. 
     *
     * @see https://tools.ietf.org/html/rfc4287
     */
    const REL_SELF = 'self';

    /**
     * Indicates a URI that can be used to retrieve a service document.
     *
     * When used in an Atom document, this relation type specifies Atom Publishing Protocol service documents by default.
     * Requested by James Snell. 
     *
     * @see https://tools.ietf.org/html/rfc5023
     */
    const REL_SERVICE = 'service';

    /**
     * Identifies service description for the context that is primarily intended for consumption by machines.
     *
     * @see https://tools.ietf.org/html/rfc8631
     */
    const REL_SERVICE_DESC = 'service-desc';

    /**
     * Identifies service documentation for the context that is primarily intended for human consumption.
     *
     * @see https://tools.ietf.org/html/rfc8631
     */
    const REL_SERVICE_DOC = 'service-doc';

    /**
     * Identifies general metadata for the context that is primarily intended for consumption by machines.
     *
     * @see https://tools.ietf.org/html/rfc8631
     */
    const REL_SERVICE_META = 'service-meta';

    /**
     * Refers to a resource that is within a context that is sponsored (such as advertising or another compensation agreement).
     *
     * @see https://webmasters.googleblog.com/2019/09/evolving-nofollow-new-ways-to-identify.html
     */
    const REL_SPONSORED = 'sponsored';

    /**
     * Refers to the first resource in a collection of resources.
     *
     * @see https://www.w3.org/TR/html401
     */
    const REL_START = 'start';

    /**
     * Identifies a resource that represents the context's status.
     *
     * @see https://tools.ietf.org/html/rfc8631
     */
    const REL_STATUS = 'status';

    /**
     * Refers to a stylesheet.
     *
     * @see http://www.w3.org/TR/html5/links.html#link-type-stylesheet
     */
    const REL_STYLESHEET = 'stylesheet';

    /**
     * Refers to a resource serving as a subsection in a collection of resources.
     *
     * @see https://www.w3.org/TR/html401/
     */
    const REL_SUBSECTION = 'subsection';

    /**
     * Points to a resource containing the successor version in the version history. 
     *
     * @see https://tools.ietf.org/html/rfc5829
     */
    const REL_SUCCESSOR_VERSION = 'successor-version';

    /**
     * Identifies a resource that provides information about the context's retirement policy. 
     *
     * @see https://tools.ietf.org/html/rfc8594
     */
    const REL_SUNSET = 'sunset';

    /**
     * Gives a tag (identified by the given address) that applies to the current document. 
     *
     * @see http://www.w3.org/TR/html5/links.html#link-type-tag
     */
    const REL_TAG = 'tag';

    /**
     * Refers to the terms of service associated with the link's context.
     *
     * @see https://tools.ietf.org/html/rfc6903
     */
    const REL_TERMS_OF_SERVICE = 'terms-of-service';

    /**
     * The Target IRI points to a TimeGate for an Original Resource.
     *
     * A TimeGate for an Original Resource is a resource that is capable of datetime negotiation to support access to prior
     * states of the Original Resource. 
     *
     * @see https://tools.ietf.org/html/rfc7089
     */
    const REL_TIMEGATE = 'timegate';

    /**
     * The Target IRI points to a TimeMap for an Original Resource.
     *
     * A TimeMap for an Original Resource is a resource from which a list of URIs of Mementos of the Original Resource is
     * available. 
     *
     * @see https://tools.ietf.org/html/rfc7089
     */
    const REL_TIMEMAP = 'timemap';

    /**
     * Refers to a resource identifying the abstract semantic type of which the link's context is considered to be an instance.
     *
     * @see https://tools.ietf.org/html/rfc6903
     */
    const REL_TYPE = 'type';

    /**
     * Refers to a resource that is within a context that is User Generated Content. 
     *
     * @see https://webmasters.googleblog.com/2019/09/evolving-nofollow-new-ways-to-identify.html
     */
    const REL_UGC = 'ugc';

    /**
     * Refers to a parent document in a hierarchy of documents. 
     *
     * This relation type registration did not indicate a reference. Requested by Noah Slater.
     *
     * @see https://tools.ietf.org/html/rfc8288
     */
    const REL_UP = 'up';

    /**
     * Points to a resource containing the version history for the context. 
     *
     * @see https://tools.ietf.org/html/rfc5829
     */
    const REL_VERSION_HISTORY = 'version-history';

    /**
     * Identifies a resource that is the source of the information in the link's context. 
     *
     * @see https://tools.ietf.org/html/rfc4287
     */
    const REL_VIA = 'via';

    /**
     * Identifies a target URI that supports the Webmention protocol. This allows clients that mention a resource in some form
     * of publishing process to contact that endpoint and inform it that this resource has been mentioned.
     *
     * This is a similar "Linkback" mechanism to the ones of Refback, Trackback, and Pingback. It uses a different protocol,
     * though, and thus should be discoverable through its own link relation type.
     *
     * @see http://www.w3.org/TR/webmention/
     */
    const REL_WEBMENTION = 'webmention';

    /**
     * Points to a working copy for this resource.
     *
     * @see https://tools.ietf.org/html/rfc5829
     */
    const REL_WORKING_COPY = 'working-copy';

    /**
     * Points to the versioned resource from which this working copy was obtained. 
     *
     * @see https://tools.ietf.org/html/rfc5829
     */
    const REL_WORKING_COPY_OF = 'working-copy-of';
}