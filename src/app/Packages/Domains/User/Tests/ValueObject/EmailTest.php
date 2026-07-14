<?php

namespace App\Packages\Domains\User\Tests\ValueObject;

use App\Packages\Domains\User\ValueObject\Email;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function test_value_returns_email_string(): void
    {
        $email = new Email('user@example.com');

        $this->assertSame('user@example.com', $email->value());
    }

    public function test_constructor_throws_on_invalid_email(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');

        new Email('not-an-email');
    }

    public function test_isEqual_returns_true_when_emails_match(): void
    {
        $email      = new Email('user@example.com');
        $otherEmail = new Email('user@example.com');

        $this->assertTrue($email->isEqual($otherEmail));
    }

    public function test_isEqual_returns_false_when_emails_differ(): void
    {
        $email      = new Email('user@example.com');
        $otherEmail = new Email('other@example.com');

        $this->assertFalse($email->isEqual($otherEmail));
    }
}
