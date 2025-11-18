<?php

declare(strict_types=1);

namespace CampaignCreditBundle\Tests\Service;

use CampaignBundle\Contract\RewardProcessorInterface;
use CampaignBundle\Entity\Award;
use CampaignBundle\Entity\Campaign;
use CampaignBundle\Entity\Reward;
use CampaignBundle\Enum\AwardType;
use CampaignCreditBundle\Service\CreditRewardProcessor;
use CreditBundle\Entity\Account;
use CreditBundle\Service\AccountService;
use CreditBundle\Service\TransactionService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @internal
 */
#[CoversClass(CreditRewardProcessor::class)]
final class CreditRewardProcessorTest extends TestCase
{
    private AccountService $accountService;
    private TransactionService $transactionService;
    private LoggerInterface $logger;
    private CreditRewardProcessor $processor;

    protected function setUp(): void
    {
        $this->accountService = $this->createMock(AccountService::class);
        $this->transactionService = $this->createMock(TransactionService::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->processor = new CreditRewardProcessor(
            $this->accountService,
            $this->transactionService,
            $this->logger
        );
    }

    public function testImplementsRewardProcessorInterface(): void
    {
        $this->assertInstanceOf(RewardProcessorInterface::class, $this->processor);
    }

    public function testSupportsCreditsAwardType(): void
    {
        $this->assertTrue($this->processor->supports(AwardType::CREDIT));
    }

    public function testDoesNotSupportOtherAwardTypes(): void
    {
        $this->assertFalse($this->processor->supports(AwardType::COUPON));
        $this->assertFalse($this->processor->supports(AwardType::COUPON_LOCAL));
        $this->assertFalse($this->processor->supports(AwardType::SPU_QUALIFICATION));
    }

    public function testGetPriority(): void
    {
        $this->assertEquals(0, $this->processor->getPriority());
    }

    public function testProcessWithValidAmount(): void
    {
        /** @var MockObject&UserInterface $user */
        $user = $this->createMock(UserInterface::class);
        /** @var MockObject&Campaign $campaign */
        $campaign = $this->createMock(Campaign::class);
        $campaign->method('getName')->willReturn('Test Campaign');
        $campaign->method('getId')->willReturn(1);

        /** @var MockObject&Award $award */
        $award = $this->createMock(Award::class);
        $award->method('getValue')->willReturn('100');
        $award->method('getCampaign')->willReturn($campaign);
        $award->method('getId')->willReturn(1);

        /** @var MockObject&Reward $reward */
        $reward = $this->createMock(Reward::class);

        /** @var MockObject&Account $account */
        $account = $this->createMock(Account::class);
        $this->accountService->expects($this->once())
            ->method('getAccountByUser')
            ->with($user, 'CREDIT')
            ->willReturn($account);

        $this->transactionService->expects($this->once())
            ->method('increase')
            ->with(
                self::matchesRegularExpression('/^CAMPAIGN-1-[0-9a-f-]{36}$/'),
                $account,
                100,
                'Test Campaign'
            );

        $reward->expects($this->once())
            ->method('setSn')
            ->with(self::matchesRegularExpression('/^CAMPAIGN-1-[0-9a-f-]{36}$/'));

        $this->logger->expects($this->once())
            ->method('info')
            ->with('Credit reward processed successfully', self::isArray());

        $this->processor->process($user, $award, $reward);
    }

    public function testProcessThrowsExceptionForInvalidAmount(): void
    {
        /** @var MockObject&UserInterface $user */
        $user = $this->createMock(UserInterface::class);
        /** @var MockObject&Campaign $campaign */
        $campaign = $this->createMock(Campaign::class);
        /** @var MockObject&Award $award */
        $award = $this->createMock(Award::class);
        $award->method('getValue')->willReturn('0');
        $award->method('getCampaign')->willReturn($campaign);

        /** @var MockObject&Reward $reward */
        $reward = $this->createMock(Reward::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('积分数量必须大于 0');

        $this->processor->process($user, $award, $reward);
    }

    public function testProcessLogsErrorOnException(): void
    {
        /** @var MockObject&UserInterface $user */
        $user = $this->createMock(UserInterface::class);
        /** @var MockObject&Campaign $campaign */
        $campaign = $this->createMock(Campaign::class);
        $campaign->method('getName')->willReturn('Test Campaign');
        $campaign->method('getId')->willReturn(1);

        /** @var MockObject&Award $award */
        $award = $this->createMock(Award::class);
        $award->method('getValue')->willReturn('100');
        $award->method('getCampaign')->willReturn($campaign);
        $award->method('getId')->willReturn(1);

        /** @var MockObject&Reward $reward */
        $reward = $this->createMock(Reward::class);

        $this->accountService->expects($this->once())
            ->method('getAccountByUser')
            ->willThrowException(new \RuntimeException('Account service error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Failed to process credit reward', self::isArray());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Account service error');

        $this->processor->process($user, $award, $reward);
    }

    public function testProcessUsesEnvironmentVariableForCurrency(): void
    {
        $_ENV['DEFAULT_CREDIT_CURRENCY_CODE'] = 'POINTS';

        /** @var MockObject&UserInterface $user */
        $user = $this->createMock(UserInterface::class);
        /** @var MockObject&Campaign $campaign */
        $campaign = $this->createMock(Campaign::class);
        $campaign->method('getName')->willReturn('Test Campaign');
        $campaign->method('getId')->willReturn(1);

        /** @var MockObject&Award $award */
        $award = $this->createMock(Award::class);
        $award->method('getValue')->willReturn('50');
        $award->method('getCampaign')->willReturn($campaign);
        $award->method('getId')->willReturn(1);

        /** @var MockObject&Reward $reward */
        $reward = $this->createMock(Reward::class);

        /** @var MockObject&Account $account */
        $account = $this->createMock(Account::class);
        $this->accountService->expects($this->once())
            ->method('getAccountByUser')
            ->with($user, 'POINTS')
            ->willReturn($account);

        $this->transactionService->expects($this->once())
            ->method('increase');

        $reward->expects($this->once())
            ->method('setSn');

        $this->processor->process($user, $award, $reward);

        unset($_ENV['DEFAULT_CREDIT_CURRENCY_CODE']);
    }

    public function testProcessUsesEnvironmentVariableForRemark(): void
    {
        $_ENV['CAMPAIGN_AWARD_CREDIT_REMARK'] = 'Custom Remark';

        /** @var MockObject&UserInterface $user */
        $user = $this->createMock(UserInterface::class);
        /** @var MockObject&Campaign $campaign */
        $campaign = $this->createMock(Campaign::class);
        $campaign->method('getId')->willReturn(1);

        /** @var MockObject&Award $award */
        $award = $this->createMock(Award::class);
        $award->method('getValue')->willReturn('50');
        $award->method('getCampaign')->willReturn($campaign);
        $award->method('getId')->willReturn(1);

        /** @var MockObject&Reward $reward */
        $reward = $this->createMock(Reward::class);

        /** @var MockObject&Account $account */
        $account = $this->createMock(Account::class);
        $this->accountService->expects($this->once())
            ->method('getAccountByUser')
            ->willReturn($account);

        $this->transactionService->expects($this->once())
            ->method('increase')
            ->with(
                self::anything(),
                $account,
                50,
                'Custom Remark'
            );

        $reward->expects($this->once())
            ->method('setSn');

        $this->processor->process($user, $award, $reward);

        unset($_ENV['CAMPAIGN_AWARD_CREDIT_REMARK']);
    }
}
