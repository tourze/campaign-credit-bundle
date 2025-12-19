<?php

declare(strict_types=1);

namespace CampaignCreditBundle\Tests\DependencyInjection;

use CampaignCreditBundle\DependencyInjection\CampaignCreditExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * CampaignCreditExtension 测试
 *
 * @internal
 */
#[CoversClass(CampaignCreditExtension::class)]
final class CampaignCreditExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
}
