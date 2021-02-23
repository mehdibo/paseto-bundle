<?php

namespace Mehdibo\Bundle\PasetoBundle\Tests\DependencyInjection;

use Mehdibo\Bundle\PasetoBundle\DependencyInjection\MehdiboPasetoExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MehdiboPasetoExtensionTest extends TestCase
{
    private ContainerBuilder $container;
    private MehdiboPasetoExtension $extension;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->extension = new MehdiboPasetoExtension();
    }

    public function testLoadValidConfig(): void
    {
        $key = bin2hex(random_bytes(32));
        $configs = [
            'mehdibo_paseto' => [
                'secret_keys' => [
                    'symmetric_key' => $key,
                    'asymmetric_key' => $key,
                ]
            ]
        ];
        $this->extension->load($configs, $this->container);
        $this->assertTrue($this->container->hasParameter('mehdibo_paseto.secret_keys.symmetric_key'));
        $this->assertTrue($this->container->hasParameter('mehdibo_paseto.secret_keys.asymmetric_key'));
        $this->assertEquals(
            hex2bin($key),
            $this->container->getParameter('mehdibo_paseto.secret_keys.symmetric_key')
        );
        $this->assertEquals(
            hex2bin($key),
            $this->container->getParameter('mehdibo_paseto.secret_keys.asymmetric_key')
        );
    }

    /**
     * @dataProvider invalidSymmetricKeys
     */
    public function testLoadInvalidSymmetricKey(string $key, string $expectedError): void
    {
        $configs = [
            'mehdibo_paseto' => [
                'secret_keys' => [
                    'symmetric_key' => $key,
                ]
            ]
        ];
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage($expectedError);
        $this->extension->load($configs, $this->container);
    }

    /**
     * @dataProvider invalidAsymmetricKeys
     */
    public function testLoadInvalidAsymmetricKey(string $key, string $expectedError): void
    {
        $configs = [
            'mehdibo_paseto' => [
                'secret_keys' => [
                    'asymmetric_key' => $key,
                ]
            ]
        ];
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage($expectedError);
        $this->extension->load($configs, $this->container);
    }

    /**
     * @return array<string, string[]>
     */
    public function invalidSymmetricKeys(): array
    {
        return [
            'Non-hex string' => [
                "This is not a hex",
                "'mehdibo_paseto.secret_keys.symmetric_key' must be a hex encoded key"],
            'Invalid hex' => [
                "16236",
                "Hexadecimal input string must have an even length"
            ],
            'Insufficient length' => [
                bin2hex(random_bytes(10)),
                "'mehdibo_paseto.secret_keys.symmetric_key' must be 32 bytes long, 10 found"
            ]
        ];
    }

    /**
     * @return array<string, string[]>
     */
    public function invalidAsymmetricKeys(): array
    {
        return [
            'Non-hex string' => [
                "This is not a hex",
                "'mehdibo_paseto.secret_keys.asymmetric_key' must be a hex encoded key"
            ],
            'Invalid hex' => [
                "16236",
                "Hexadecimal input string must have an even length"
            ],
            'Insufficient length' => [
                bin2hex(random_bytes(10)),
                "'mehdibo_paseto.secret_keys.asymmetric_key' must be 32 or 64 bytes long, 10 found"
            ]
        ];
    }
}
