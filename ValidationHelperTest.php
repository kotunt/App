<?php

namespace Tests\Unit;

use App\Helpers\ValidationHelper;
use PHPUnit\Framework\TestCase;

class ValidationHelperTest extends TestCase
{
    /** @test */
    public function it_returns_true_for_a_valid_6_digit_pin()
    {
        $this->assertTrue(ValidationHelper::isPinValid('123456'));
    }

    /** @test */
    public function it_returns_false_for_a_pin_with_letters()
    {
        $this->assertFalse(ValidationHelper::isPinValid('12345a'));
    }

    /** @test */
    public function it_returns_false_for_a_pin_that_is_too_short()
    {
        $this->assertFalse(ValidationHelper::isPinValid('12345'));
        $this->assertFalse(ValidationHelper::isPinValid(''));
    }
}