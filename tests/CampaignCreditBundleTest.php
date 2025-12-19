<?php

declare(strict_types=1);

namespace CampaignCreditBundle\Tests;

use CampaignCreditBundle\CampaignCreditBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * CampaignCreditBundle 测试
 *
 * @internal
 */
#[CoversClass(CampaignCreditBundle::class)]
#[RunTestsInSeparateProcesses]
final class CampaignCreditBundleTest extends AbstractBundleTestCase
{
}
