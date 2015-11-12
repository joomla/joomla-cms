# Important Security Information

JCrypt is being deprecated in Joomla 4 and is **not generally considered secure**.
Please don't use it for any of your projects.

Use [defuse/php-encryption](https://github.com/defuse/php-encryption) instead.
We bundle version 1.1 as of Joomla 3.5.0.

```php
$message = 'Sensitive field';
$key = Crypto::CreateNewRandomKey(); // Store this for long-term use
$ciphertext = Crypto::encrypt($message, $key);
$decrypted = Crypto::decrypt($ciphertext, $key);
```

If you continue to use JCrypt, you do so *at your own risk*.