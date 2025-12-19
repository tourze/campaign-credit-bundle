<?php

declare(strict_types=1);

namespace CampaignCreditBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

/**
 * Campaign Credit Bundle 扩展配置
 *
 * 自动加载 services.yaml 配置文件
 */
final class CampaignCreditExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}
