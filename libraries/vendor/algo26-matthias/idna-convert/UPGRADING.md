# Upgrading from previous versions

## 3.0

The library has been broken down into various specific classes, thus more closely following SOLID principles.

As such the single class `IdnaConvert` has been broken down into `ToIdn` and `ToUnicode` respectively. Their naming reflects 
the format of the outcome, so it's more clear to distinguish, what you need. This should be easier to grasp then the old method names `encode()` and `decode()`.
Usually you will only need one conversion direction per script run, so why bother loading and parsing all the other unused code, then?  

Also the handling of host names (simple labels like `my-hostname` or FQHNs like `some-host.my-domain.example`) is now separated from 
that of email addresses and URLs. 
Both classes offer the same set of public methods:

| Method                  |                                     |
|-------------------------|-------------------------------------|
| `convert()`             |  To convert host names              | 
| `convertEmailAddress()` |  To convert email addresses         | 
| `convertUrl()`          |  To convert the host name of an URL | 

There's no "strict mode" anymore, this is achieved by the separate methods above. The IDN version is selected when instantiating the class, no more setting during runtime.
Also the encoding (for the Unicode side of things) is now **always UTF-8**. Use `TransCodeUnicode` or `EncodingHelper` for converting to and from various encodings to UTF-8.

All actual sub classes like that for NamePrep and the actual Punycode transformation are put in their own namespaces under `Algo26\IdnaConvert`, e.g. `Algo26\IdnaConvert\NamePrep`. 
Interfaces and Exceptions also have their own namespace to declutter the class structure even more. 

The class `EncodingHelper` is now called separated into the two classes `ToUtf8` and `FromUtf8` respectively and lies under the namespace `Algo26\idnaConvert\EncodingHelper`.
The class `UnicodeTranscoder` is now called `TransCodeUnicode` under the namespace `Algo26\idnaConvert\TransCodeUnicode`.

All examples are updated to reflect the new usage. See the ReadMe for more details.

Also the **minimum PHP version is now 7.2**.

## 2.0
The library has been handed over to actively maintained GitHub and Packagist accounts. This lead to a change in the namespace.
Replace all occurrences of 
`Mso\IdnaConvert` or `PhlyLabs\IdnaConvert` to `Algo26\IdnaConvert`.
There's no further changes to the class signatures. 

## 1.0
**BC break:**
As of version 1.0.0 the class closely follows the PSRs PSR-1, PSR-2 and PSR-4 of the PHP-FIG. 
As such the classes' naming has been changed, a namespace has been introduced and the default IDN version has changed from 2003 to 2008 and minimum PHP engine version raised to 5.6.0.

## 0.8.0
As of version 0.8.0 the class fully supports IDNA 2008. 
Thus the aforementioned parameter is deprecated and replaced by a parameter to switch between the standards. See the updated example 5 in the ReadMe.

## 0.6.4
**BC break:** 
As of version 0.6.4 the class per default allows the German ligature ß to be encoded as the DeNIC, the registry for .DE allows domains containing ß.  

## 0.6.0
**ATTENTION:** As of version 0.6.0 this class is written in the OOP style of PHP 5. 
Since PHP 4 is no longer actively maintained, you should switch to PHP 5 as fast as possible. 
We expect to see no compatibility issues with the upcoming PHP 6, too.




