<?php

namespace App\Packages\Features\Tests\CommandUseCases;

use App\Packages\Features\CommandUseCases\UseCommand\User\UpdateUserCommand;
use Faker\Factory as FakerFactory;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class UpdateUserCommandTest extends TestCase
{
    private function validData(array $overrides = []): array
    {
        $faker = FakerFactory::create();

        return array_merge([
            'id'                => $faker->unique()->randomNumber(5),
            'first_name'        => $faker->firstName(),
            'last_name'         => $faker->lastName(),
            'email'             => $faker->unique()->safeEmail(),
            'age_range'         => $faker->randomElement(['teens', '20s', '30s', '40s', '50s', '60plus']),
            'subscription_tier' => $faker->randomElement(['free', 'premium']),
        ], $overrides);
    }

    public function test_fromArray_creates_command_with_valid_data(): void
    {
        $data    = $this->validData();
        $command = UpdateUserCommand::fromArray($data);

        $this->assertSame((int) $data['id'], $command->id);
        $this->assertSame($data['first_name'], $command->firstName);
        $this->assertSame($data['last_name'], $command->lastName);
        $this->assertSame($data['email'], $command->email);
        $this->assertSame($data['age_range'], $command->ageRange);
        $this->assertSame($data['subscription_tier'], $command->subscriptionTier);
        $this->assertNull($command->subscriptionExpiresAt);
    }

    public function test_fromArray_sets_subscription_expires_at_when_provided(): void
    {
        $command = UpdateUserCommand::fromArray($this->validData([
            'subscription_expires_at' => '2027-01-01 00:00:00',
        ]));

        $this->assertSame('2027-01-01 00:00:00', $command->subscriptionExpiresAt);
    }

    public function test_fromArray_throws_when_id_is_missing(): void
    {
        $data = $this->validData();
        unset($data['id']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required key: id');

        UpdateUserCommand::fromArray($data);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('optionalKeyProvider')]
    public function test_fromArray_allows_optional_key_to_be_omitted(string $optionalKey): void
    {
        $data = $this->validData();
        unset($data[$optionalKey]);

        $command = UpdateUserCommand::fromArray($data);

        $this->assertNull($command->{$this->toCommandProperty($optionalKey)});
    }

    public static function optionalKeyProvider(): array
    {
        return [
            ['first_name'],
            ['last_name'],
            ['email'],
            ['age_range'],
            ['subscription_tier'],
        ];
    }

    private function toCommandProperty(string $key): string
    {
        return lcfirst(str_replace('_', '', ucwords($key, '_')));
    }
}
