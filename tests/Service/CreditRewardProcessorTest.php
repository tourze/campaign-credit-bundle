<?php

declare(strict_types=1);

namespace CampaignCreditBundle\Tests\Service;

use CampaignBundle\Contract\RewardProcessorInterface;
use CampaignBundle\Enum\AwardType;
use CampaignCreditBundle\Service\CreditRewardProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * CreditRewardProcessor 集成测试
 *
 * 测试服务的初始化和依赖注入
 *
 * @internal
 */
#[CoversClass(CreditRewardProcessor::class)]
#[RunTestsInSeparateProcesses]
final class CreditRewardProcessorTest extends AbstractIntegrationTestCase
{
    private CreditRewardProcessor $processor;

    protected function onSetUp(): void
    {
        // 从容器获取 CreditRewardProcessor - 验证依赖注入正确
        $this->processor = self::getService(CreditRewardProcessor::class);
    }

    /**
     * 测试服务可以从容器获取
     */
    public function testServiceCanBeRetrievedFromContainer(): void
    {
        $this->assertInstanceOf(CreditRewardProcessor::class, $this->processor);
    }

    /**
     * 测试实现了 RewardProcessorInterface 接口
     */
    public function testImplementsRewardProcessorInterface(): void
    {
        $this->assertInstanceOf(RewardProcessorInterface::class, $this->processor);
    }

    /**
     * 测试支持 CREDIT 类型奖励
     */
    public function testSupportsCreditsAwardType(): void
    {
        $this->assertTrue($this->processor->supports(AwardType::CREDIT));
    }

    /**
     * 测试不支持 COUPON 类型奖励
     */
    public function testDoesNotSupportCouponAwardType(): void
    {
        $this->assertFalse($this->processor->supports(AwardType::COUPON));
    }

    /**
     * 测试不支持 COUPON_LOCAL 类型奖励
     */
    public function testDoesNotSupportCouponLocalAwardType(): void
    {
        $this->assertFalse($this->processor->supports(AwardType::COUPON_LOCAL));
    }

    /**
     * 测试不支持 SPU_QUALIFICATION 类型奖励
     */
    public function testDoesNotSupportSpuQualificationAwardType(): void
    {
        $this->assertFalse($this->processor->supports(AwardType::SPU_QUALIFICATION));
    }

    /**
     * 测试 getPriority 返回正确的优先级
     */
    public function testGetPriority(): void
    {
        $this->assertEquals(0, $this->processor->getPriority());
    }

    /**
     * 测试服务有 process 方法
     */
    public function testProcessMethodExists(): void
    {
        $reflection = new \ReflectionClass(CreditRewardProcessor::class);

        $this->assertTrue($reflection->hasMethod('process'));
        $this->assertTrue($reflection->getMethod('process')->isPublic());
    }

    /**
     * 测试 process 方法签名
     */
    public function testProcessMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CreditRewardProcessor::class, 'process');
        $parameters = $reflection->getParameters();

        $this->assertCount(3, $parameters);
        $this->assertEquals('user', $parameters[0]->getName());
        $this->assertEquals('award', $parameters[1]->getName());
        $this->assertEquals('reward', $parameters[2]->getName());
    }

    /**
     * 测试 supports 方法签名
     */
    public function testSupportsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CreditRewardProcessor::class, 'supports');
        $parameters = $reflection->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('type', $parameters[0]->getName());
    }

    /**
     * 测试服务是 readonly 类
     */
    public function testServiceIsReadonly(): void
    {
        $reflection = new \ReflectionClass(CreditRewardProcessor::class);
        $this->assertTrue($reflection->isReadOnly());
    }

    /**
     * 测试构造函数依赖
     */
    public function testConstructorDependencies(): void
    {
        $reflection = new \ReflectionClass(CreditRewardProcessor::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);
        $parameters = $constructor->getParameters();

        $this->assertCount(3, $parameters);
        $this->assertEquals('accountService', $parameters[0]->getName());
        $this->assertEquals('transactionService', $parameters[1]->getName());
        $this->assertEquals('logger', $parameters[2]->getName());
    }
}
