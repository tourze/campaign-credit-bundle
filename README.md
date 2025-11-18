# Campaign Credit Bundle

[English](README.md) | [中文](README.zh-CN.md)

积分奖励扩展包，为 `campaign-bundle` 提供积分类型的活动奖励支持。

## 功能

- ✅ 处理 `AwardType::CREDIT` 类型的活动奖励
- ✅ 自动增加用户积分
- ✅ 记录积分交易流水号
- ✅ 支持自定义积分货币类型
- ✅ 完整的日志记录

## 安装

```bash
composer require tourze/campaign-credit-bundle
```

## 使用

### 1. 注册 Bundle

在 `config/bundles.php` 中添加：

```php
return [
    // ...
    CampaignCreditBundle\CampaignCreditBundle::class => ['all' => true],
];
```

### 2. 配置环境变量（可选）

在 `.env` 或 `.env.local` 中配置：

```env
# 默认积分货币代码（默认为 'CREDIT'）
DEFAULT_CREDIT_CURRENCY_CODE=CREDIT

# 积分交易备注（默认使用活动名称）
CAMPAIGN_AWARD_CREDIT_REMARK=活动奖励积分
```

### 3. 创建积分活动奖励

```php
use CampaignBundle\Entity\Campaign;
use CampaignBundle\Entity\Award;
use CampaignBundle\Enum\AwardType;

$campaign = new Campaign();
$campaign->setCode('CREDIT2024');
$campaign->setName('积分奖励活动');

$award = new Award();
$award->setCampaign($campaign);
$award->setEvent('join');
$award->setType(AwardType::CREDIT);
$award->setValue('100'); // 积分数量
$award->setPrizeQuantity(1000);

// 保存到数据库
$entityManager->persist($campaign);
$entityManager->persist($award);
$entityManager->flush();
```

### 4. 自动发放

当用户参与活动并获得奖励时，系统会自动：
1. 获取用户积分账户
2. 增加指定数量的积分
3. 记录交易流水号到 Reward 的 `sn` 字段

## 架构

### 核心类

- `CreditRewardProcessor`：实现 `RewardProcessorInterface`，处理积分发放逻辑

### 处理流程

```
CampaignService::rewardUser()
    ↓
CampaignRewardProcessorService::processRewardByType()
    ↓
RewardProcessorRegistry::getProcessor(AwardType::CREDIT)
    ↓
CreditRewardProcessor::process()
    ↓
TransactionService::increase()
```

### 依赖关系

```
campaign-credit-bundle
├── campaign-bundle (核心活动管理)
└── credit-bundle (积分系统)
```

## 日志

处理器会记录详细的日志信息：

**成功日志**：
```
[info] Credit reward processed successfully
{
  "amount": 100,
  "currency": "CREDIT",
  "user_id": 12345,
  "transaction_id": "CAMPAIGN-10-550e8400-e29b-41d4-a716-446655440000",
  "campaign_id": 1,
  "award_id": 10,
  "remark": "积分奖励活动"
}
```

**失败日志**：
```
[error] Failed to process credit reward
{
  "amount": 100,
  "user_id": 12345,
  "campaign_id": 1,
  "award_id": 10,
  "exception": "...",
  "trace": "..."
}
```

## 交易流水号格式

积分交易流水号格式为：`CAMPAIGN-{award_id}-{uuid}`

示例：`CAMPAIGN-10-550e8400-e29b-41d4-a716-446655440000`

这确保了：
- 唯一性：UUID 保证全局唯一
- 可追溯性：包含 award_id 便于查询
- 一致性：统一的命名规范

## 异常处理

- `\InvalidArgumentException`：积分数量无效时抛出（必须 > 0）
- 其他异常会被记录并重新抛出，由上层处理

## 扩展

如果需要自定义积分发放逻辑，可以创建自己的处理器并设置更高的优先级：

```php
class CustomCreditRewardProcessor implements RewardProcessorInterface
{
    public function supports(AwardType $type): bool
    {
        return AwardType::CREDIT === $type;
    }

    public function process(UserInterface $user, Award $award, Reward $reward): void
    {
        // 自定义逻辑
    }

    public function getPriority(): int
    {
        return 10; // 高于默认的 0
    }
}
```

## 许可证

MIT
