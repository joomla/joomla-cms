<?php
require_once('Crypto.php');
  try {
      $key = Crypto::CreateNewRandomKey();
      // WARNING: Do NOT encode $key with bin2hex() or base64_encode(),
      // they may leak the key to the attacker through side channels.
  } catch (CryptoTestFailedException $ex) {
      die('Cannot safely create a key');
  } catch (CannotPerformOperationException $ex) {
      die('Cannot safely create a key');
  }

  $message = "ATTACK AT DAWN";
  try {
      $ciphertext = Crypto::Encrypt($message, $key);
  } catch (CryptoTestFailedException $ex) {
      die('Cannot safely perform encryption');
  } catch (CannotPerformOperationException $ex) {
      die('Cannot safely perform decryption');
  }

  try {
      $decrypted = Crypto::Decrypt($ciphertext, $key);
  } catch (InvalidCiphertextException $ex) { // VERY IMPORTANT
      // Either:
      //   1. The ciphertext was modified by the attacker,
      //   2. The key is wrong, or
      //   3. $ciphertext is not a valid ciphertext or was corrupted.
      // Assume the worst.
      die('DANGER! DANGER! The ciphertext has been tampered with!');
  } catch (CryptoTestFailedException $ex) {
      die('Cannot safely perform encryption');
  } catch (CannotPerformOperationException $ex) {
      die('Cannot safely perform decryption');
  }
?>
