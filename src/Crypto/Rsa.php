<?php
namespace WechatPay\GuzzleMiddleware\Crypto;

const sha256WithRSAEncryption = 'sha256WithRSAEncryption';

/**
 * Provides some methods for the RSA `sha256WithRSAEncryption` with `OPENSSL_PKCS1_OAEP_PADDING`.
 */
class Rsa
{
    /**
     * Encrypts text with `OPENSSL_PKCS1_OAEP_PADDING`.
     *
     * @param string $plaintext - Cleartext to encode.
     * @param resource|array|string $publicKey - A PEM encoded public key.
     *
     * @return string - The base64-encoded ciphertext.
     */
    public static function encrypt(string $plaintext, $publicKey): string
    {
        if (!\openssl_public_encrypt($plaintext, $encrypted, $publicKey, \OPENSSL_PKCS1_OAEP_PADDING)) {
            throw new \UnexpectedValueException('Encrypting the input $plaintext failed, please checking your $publicKey whether or nor correct.');
        }

        return \base64_encode($encrypted);
    }

    /**
     * Verifying the `message` with given `signature` string that uses `sha256WithRSAEncryption`.
     *
     * @param string $message - Content will be `openssl_verify`.
     * @param string $signature - The base64-encoded ciphertext.
     * @param resource|array|string $publicKey - A PEM encoded public key.
     *
     * @return boolean - True is passed, false is failed.
     */
    public static function verify(string $message, string $signature, $publicKey): bool
    {
        if (!\in_array(sha256WithRSAEncryption, \openssl_get_md_methods(true))) {
            throw new \RuntimeException('It looks like the ext-openssl extension missing the `sha256WithRSAEncryption` digest method.');
        }

        if (($result = \openssl_verify($message, \base64_decode($signature), $publicKey, sha256WithRSAEncryption)) === false) {
            throw new \UnexpectedValueException('Verified the input $message failed, please checking your $publicKey whether or nor correct.');
        }

        return $result === 1;
    }

    /**
     * Creates and returns a `base64_encode` string that uses `sha256WithRSAEncryption`.
     *
     * @param string $message - Content will be `openssl_sign`.
     * @param resource|array|string $privateKey - A PEM encoded private key.
     *
     * @return string - The base64-encoded signature.
     */
    public static function sign(string $message, $privateKey): string
    {
        if (!\in_array(sha256WithRSAEncryption, \openssl_get_md_methods(true))) {
            throw new \RuntimeException('It looks like the ext-openssl extension missing the `sha256WithRSAEncryption` digest method.');
        }

        if (!\openssl_sign($message, $signature, $privateKey, sha256WithRSAEncryption)) {
            throw new \UnexpectedValueException('Signing the input $message failed, please checking your $privateKey whether or nor correct.');
        }

        return \base64_encode($signature);
    }

    /**
     * Decrypts base64 encoded string with `privateKey` with `OPENSSL_PKCS1_OAEP_PADDING`.
     *
     * @param string $ciphertext - Was previously encrypted string using the corresponding public key.
     * @param resource|array|string $privateKey - A PEM encoded private key.
     *
     * @return string - The utf-8 plaintext.
     */
    public static function decrypt(string $ciphertext, $privateKey): string
    {
        if (!\openssl_private_decrypt(\base64_decode($ciphertext), $decrypted, $privateKey, \OPENSSL_PKCS1_OAEP_PADDING)) {
            throw new \UnexpectedValueException('Decrypting the input $ciphertext failed, please checking your $privateKey whether or nor correct.');
        }

        return $decrypted;
    }
}
