<?php

declare(strict_types=1);

namespace CampaignCreditBundle;

use CampaignBundle\CampaignBundle;
use CreditBundle\CreditBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;

/**
 * 活动积分扩展 Bundle
 *
 * 为 campaign-bundle 提供积分类型的奖励处理能力。
 *
 * 功能：
 * - 处理 AwardType::CREDIT 类型的奖励
 * - 自动增加用户积分
 * - 记录积分交易流水号
 * - 支持自定义积分货币类型
 *
 * 依赖：
 * - campaign-bundle：核心活动管理
 * - credit-bundle：积分系统
 */
class CampaignCreditBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            CampaignBundle::class => ['all' => true],
            CreditBundle::class => ['all' => true],
        ];
    }
}
