# Important Security Information

If you're going to use JCrypt in any of your extensions, make *sure* you use **CryptoCipher** or **SodiumCipher**; These are the only two which are cryptographically secure.

```php
use Joomla\CMS\Crypt\Cipher\SodiumCipher;

$cipher = new SodiumCipher;
$key    = $cipher->generateKey();
$data   = 'My encrypted data.';

$cipher->setNonce(\Sodium\randombytes_buf(\Sodium\CRYPTO_BOX_NONCEBYTES));

$encrypted = $cipher->encrypt($data, $key);
$decrypted = $cipher->decrypt($encrypted, $key);

if ($decrypted !== $data)
{
	throw new RuntimeException('The data was not decrypted correctly.');
}
```

```php
use Joomla\CMS\Crypt\Cipher\CryptoCipher;

$cipher = new CryptoCipher();
$key = $cipher->generateKey(); // Store this for long-term use

$message = "We're all living on a yellow submarine!";
$ciphertext = $cipher->encrypt($message, $key);
$decrypted = $cipher->decrypt($ciphertext, $key);
```

## Avoid these Ciphers if Possible

* `JCryptCipher3Des`
* `JCryptCipherBlowfish`
* `JCryptCipherMcrypt`
* `JCryptCipherRijndael256`

All of these ciphers are vulnerable to something called a [chosen-ciphertext attack](https://en.wikipedia.org/wiki/Chosen-ciphertext_attack). The only provable way to prevent chosen-ciphertext attacks is to [use authenticated encryption](https://paragonie.com/blog/2015/05/using-encryption-and-authentication-correctly), preferrably in an [Encrypt-then-MAC construction](http://www.thoughtcrime.org/blog/the-cryptographic-doom-principle/).

The only JCrypt cipher that meets the *authenticated encryption* criteria is **`JCryptCipherCrypto`**.

## Absolutely Avoid JCryptCipherSimple

`JCryptCipherSimple` is deprecated and will be removed in Joomla 4. It's vulnerable to a known plaintext attack: If you know any information about the plaintext (e.g. the first character is '<'), an attacker can recover bits of the encryption key with ease.

If an attacker can influence the message, they can actually steal your encryption key. Here's how:

1. Feed `str_repeat('A', 256)` into your application, towards `JCryptCipherSimple`.
2. Observe the output of the cipher (the ciphertext).
3. Run it through this code:

```php
function recoverJcryptCipherSimpleKey($ciphertext, $knownPlaintext)
{
    $key = '';
    for ($i = 0; $i < strlen($knownPlaintext); ++$i) {
      $key.= chr(ord($ciphertext[$i]) ^ ord($knownPlaintext[$i]));
    }
}

$key = recoverJcryptCipherSimpleKey(
    $someEncryptedTextOutput,
    str_repeat('A', 256)
);
```

Given how trivial it is to steal the encryption key from this cipher, you absolutely should not use it.
