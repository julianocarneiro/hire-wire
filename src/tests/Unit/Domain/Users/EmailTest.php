<?php

namespace Tests\Unit\Domain\Users;

use App\Domain\Users\Exceptions\InvalidEmailException;
use App\Domain\Users\ValueObjects\Email;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function test_it_normalises_case(): void
    {
        $email = Email::fromString(' User@Test.COM ');

        $this->assertSame('user@test.com', $email->toString());
    }

    public function test_it_rejects_empty_string(): void
    {
        $this->expectException(InvalidEmailException::class);

        Email::fromString('   ');
    }

    public function test_it_rejects_invalid_format(): void
    {
        $this->expectException(InvalidEmailException::class);

        Email::fromString('not-an-email');
    }

    public function test_equals_is_case_insensitive(): void
    {
        $a = Email::fromString('a@b.com');
        $b = Email::fromString('A@B.COM');

        $this->assertTrue($a->equals($b));
    }
}
