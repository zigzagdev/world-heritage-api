<?php

namespace Tests\Unit\Packages\Features\CommandUseCases\UseCommand;

use App\Packages\Features\CommandUseCases\UseCommand\User\CreateUserCommand;
use Faker\Factory as FakerFactory;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CreateUserCommandTest extends TestCase
{
    private function validData(array $overrides = []): array
    {
        $faker = FakerFactory::create();

        return array_merge([
            'first_name'        => $faker->firstName(),
            'last_name'         => $faker->lastName(),
            'email'             => $faker->unique()->safeEmail(),
            'password'          => $faker->password(8),
            'age_range'         => $faker->randomElement(['teens', '20s', '30s', '40s', '50s', '60plus']),
            'subscription_tier' => $faker->randomElement(['free', 'premium']),
        ], $overrides);
    }

    public function test_fromArray_creates_command_with_valid_data(): void
    {
        $data = $this->validData();

        $command = CreateUserCommand::fromArray($data);

        $this->assertSame($data['first_name'], $command->firstName);
        $this->assertSame($data['last_name'], $command->lastName);
        $this->assertSame($data['email'], $command->email);
        $this->assertSame($data['password'], $command->password);
        $this->assertSame($data['age_range'], $command->ageRange);
        $this->assertSame($data['subscription_tier'], $command->subscriptionTier);
        $this->assertNull($command->subscriptionExpiresAt);
    }

    public function test_fromArray_sets_subscription_expires_at_when_provided(): void
    {
        $command = CreateUserCommand::fromArray($this->validData([
            'subscription_expires_at' => '2027-01-01 00:00:00',
        ]));

        $this->assertSame('2027-01-01 00:00:00', $command->subscriptionExpiresAt);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('missingKeyProvider')]
    public function test_fromArray_throws_when_required_key_is_missing(string $missingKey): void
    {
        $data = $this->validData();
        unset($data[$missingKey]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Missing required key: {$missingKey}");

        CreateUserCommand::fromArray($data);
    }

    public static function missingKeyProvider(): array
    {
        return [
            ['first_name'],
            ['last_name'],
            ['email'],
            ['password'],
            ['age_range'],
            ['subscription_tier'],
        ];
    }
}