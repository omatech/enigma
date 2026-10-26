<?php

namespace Omatech\Enigma\Tests;

use SodiumException;

class EncryptTest extends TestCase
{
    public function test_encrypt_with_enigma_helper(): void
    {
        $value = 'value to encrypt';
        $encryptedValue = encryptEnigma('test', 'test', $value);

        $this->assertNotFalse(strpos($encryptedValue, 'nacl:'));
    }

    public function test_decrypt_with_enigma_helper(): void
    {
        $value = 'value to encrypt';
        $encryptedValue = encryptEnigma('test', 'test', $value);

        $this->assertNotFalse(strpos($encryptedValue, 'nacl:'));
        $decryptedValue = decryptEnigma('test', 'test', $encryptedValue);

        $this->assertSame($value, $decryptedValue);
    }

    public function test_invalid_encryption_decryption_with_enigma_helper(): void
    {
        $this->expectException(SodiumException::class);

        $value = 'value to encrypt';
        $encryptedValue = encryptEnigma('test', 'test', $value);

        $this->assertNotFalse(strpos($encryptedValue, 'nacl:'));
        decryptEnigma('test2', 'test2', $encryptedValue);
    }
}
