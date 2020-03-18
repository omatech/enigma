<?php

namespace Omatech\Enigma\Tests;

use SodiumException;

class EncryptTest extends TestCase
{
    /** @test */
    public function encrypt_with_enigma_helper(): void
    {
        $value = 'value to encrypt';
        $encryptedValue = encryptEnigma('test', 'test', $value);

        $this->assertNotFalse(strpos($encryptedValue, 'nacl:'));
    }

    /** @test */
    public function decrypt_with_enigma_helper(): void
    {
        $value = 'value to encrypt';
        $encryptedValue = encryptEnigma('test', 'test', $value);

        $this->assertNotFalse(strpos($encryptedValue, 'nacl:'));
        $decryptedValue = decryptEnigma('test', 'test', $encryptedValue);

        $this->assertSame($value, $decryptedValue);
    }

    /** @test */
    public function invalid_encryption_decryption_with_enigma_helper(): void
    {
        $this->expectException(SodiumException::class);

        $value = 'value to encrypt';
        $encryptedValue = encryptEnigma('test', 'test', $value);

        $this->assertNotFalse(strpos($encryptedValue, 'nacl:'));
        decryptEnigma('test2', 'test2', $encryptedValue);
    }
}
