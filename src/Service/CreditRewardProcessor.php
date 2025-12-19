<?php

declare(strict_types=1);

namespace CampaignCreditBundle\Service;

use CampaignBundle\Contract\RewardProcessorInterface;
use CampaignBundle\Entity\Award;
use CampaignBundle\Entity\Reward;
use CampaignBundle\Enum\AwardType;
use CreditBundle\Service\AccountService;
use CreditBundle\Service\TransactionService;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

/**
 * 积分奖励处理器
 *
 * 负责处理活动中积分类型的奖励发放。
 *
 * 处理流程：
 * 1. 从 Award 值中获取积分数量
 * 2. 获取用户积分账户
 * 3. 增加用户积分
 * 4. 将交易流水号记录到 Reward 的 sn 字段
 *
 * 环境变量配置：
 * - DEFAULT_CREDIT_CURRENCY_CODE：默认积分货币代码（默认 'CREDIT'）
 * - CAMPAIGN_AWARD_CREDIT_REMARK：积分交易备注（默认使用活动名称）
 *
 * @see \CampaignBundle\Enum\AwardType::CREDIT
 */
#[WithMonologChannel(channel: 'campaign_credit')]
readonly class CreditRewardProcessor implements RewardProcessorInterface
{
    public function __construct(
        private AccountService $accountService,
        private TransactionService $transactionService,
        private LoggerInterface $logger,
    ) {
    }

    public function supports(AwardType $type): bool
    {
        return AwardType::CREDIT === $type;
    }

    public function process(UserInterface $user, Award $award, Reward $reward): void
    {
        $amount = intval($award->getValue());

        if ($amount <= 0) {
            throw new \InvalidArgumentException('积分数量必须大于 0');
        }

        try {
            // 1. 获取积分货币类型（从环境变量或默认值）
            $integralName = $_ENV['DEFAULT_CREDIT_CURRENCY_CODE'] ?? 'CREDIT';
            assert(is_string($integralName), 'Credit currency code must be string');

            // 2. 获取用户积分账户
            $inAccount = $this->accountService->getAccountByUser($user, $integralName);

            // 3. 生成交易备注
            $remark = $_ENV['CAMPAIGN_AWARD_CREDIT_REMARK'] ?? $award->getCampaign()->getName();
            assert(is_string($remark), 'Campaign award credit remark must be string');

            // 4. 生成唯一交易号
            $transactionId = sprintf(
                'CAMPAIGN-%d-%s',
                $award->getId(),
                Uuid::v4()->toRfc4122()
            );

            // 5. 增加积分
            $this->transactionService->increase(
                $transactionId,
                $inAccount,
                $amount,
                $remark,
            );

            // 6. 记录交易流水号
            $reward->setSn($transactionId);

            $this->logger->info('Credit reward processed successfully', [
                'amount' => $amount,
                'currency' => $integralName,
                'user_id' => method_exists($user, 'getId') ? $user->getId() : 'unknown',
                'transaction_id' => $transactionId,
                'campaign_id' => $award->getCampaign()->getId(),
                'award_id' => $award->getId(),
                'remark' => $remark,
            ]);
        } catch (\Throwable $exception) {
            $this->logger->error('Failed to process credit reward', [
                'amount' => $amount,
                'user_id' => method_exists($user, 'getId') ? $user->getId() : 'unknown',
                'campaign_id' => $award->getCampaign()->getId(),
                'award_id' => $award->getId(),
                'exception' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            // 重新抛出异常，让上层处理
            throw $exception;
        }
    }

    public function getPriority(): int
    {
        return 0;
    }
}
