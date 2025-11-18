<?php

declare(strict_types=1);

namespace CampaignCreditBundle\Tests\DependencyInjection;

use CampaignCreditBundle\DependencyInjection\CampaignCreditExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

/**
 * @internal
 */
#[CoversClass(CampaignCreditExtension::class)]
final class CampaignCreditExtensionTest extends TestCase
{
    public function testExtendsAutoExtension(): void
    {
        $extension = new CampaignCreditExtension();

        $this->assertInstanceOf(AutoExtension::class, $extension);
    }

    public function testGetConfigDir(): void
    {
        $extension = new CampaignCreditExtension();
        $reflection = new \ReflectionClass($extension);
        $method = $reflection->getMethod('getConfigDir');

        $configDir = $method->invoke($extension);

        $this->assertIsString($configDir);
        $this->assertStringEndsWith('/Resources/config', $configDir);
    }

    public function testExtensionAlias(): void
    {
        $extension = new CampaignCreditExtension();

        $this->assertIsString($extension->getAlias());
        $this->assertNotEmpty($extension->getAlias());
    }
}
