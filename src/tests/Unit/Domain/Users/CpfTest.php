<?php

namespace Tests\Unit\Domain\Users;

use App\Domain\Users\Exceptions\InvalidCpfException;
use App\Domain\Users\ValueObjects\Cpf;
use PHPUnit\Framework\TestCase;

class CpfTest extends TestCase
{
    public function test_it_accepts_normalised_valid_cpf(): void
    {
        $cpf = Cpf::fromString('529.982.247-25');

        $this->assertSame('52998224725', $cpf->toString());
    }

    public function test_it_rejects_invalid_length(): void
    {
        $this->expectException(InvalidCpfException::class);

        Cpf::fromString('1234567890');
    }

    public function test_it_rejects_repeated_digits(): void
    {
        $this->expectException(InvalidCpfException::class);

        Cpf::fromString('11111111111');
    }

    public function test_it_rejects_invalid_check_digits(): void
    {
        $this->expectException(InvalidCpfException::class);

        Cpf::fromString('52998224700');
    }

    public function test_equals_compares_digits(): void
    {
        $a = Cpf::fromString('52998224725');
        $b = Cpf::fromString('529.982.247-25');

        $this->assertTrue($a->equals($b));
    }
}
