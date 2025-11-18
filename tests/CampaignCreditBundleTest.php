<?php

declare(strict_types=1);

namespace CampaignCreditBundle\Tests;

use CampaignCreditBundle\CampaignCreditBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\BundleDependency\BundleDependencyInterface;

/**
 * @internal
 */
#[CoversClass(CampaignCreditBundle::class)]
final class CampaignCreditBundleTest extends TestCase
{
    public function testImplementsBundleDependencyInterface(): void
    {
        $bundle = new CampaignCreditBundle();

        $this->assertInstanceOf(BundleDependencyInterface::class, $bundle);
    }

    public function testBundleCanBeInstantiated(): void
    {
        $bundle = new CampaignCreditBundle();

        $this->assertNotNull($bundle);
        $this->assertInstanceOf(CampaignCreditBundle::class, $bundle);
    }
}
