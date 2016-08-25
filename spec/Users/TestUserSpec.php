<?php declare(strict_types = 1);

namespace spec\CEmerson\Auth\Users;

use CEmerson\Auth\PasswordHashingStrategies\PasswordHashingStrategy;
use PhpSpec\ObjectBehavior;

class TestUserSpec extends ObjectBehavior
{
    function let(PasswordHashingStrategy $passwordHashingStrategy)
    {
        $this->beConstructedWith($passwordHashingStrategy);
    }

    function it_uses_the_password_hashing_strategy_to_check_the_users_password(
        PasswordHashingStrategy $passwordHashingStrategy
    ) {
        $passwordHashingStrategy
            ->verifyPassword('test_password', 'test_password_hash')
            ->shouldBeCalled()
            ->willReturn(false);

        $this->verifyPassword('test_password')->shouldReturn(false);
    }
}
