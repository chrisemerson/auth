<?php declare(strict_types = 1);

namespace spec\CEmerson\Auth\PasswordHashingStrategies;

use CEmerson\Auth\Providers\Local\PasswordHashingStrategies\PasswordHashingStrategy;
use PhpSpec\ObjectBehavior;

class SaltedPasswordHashingStrategyDecoratorSpec extends ObjectBehavior
{
    function let(PasswordHashingStrategy $passwordHashingStrategy)
    {
        $this->beConstructedWith($passwordHashingStrategy);

        $this->setSalt('test_salt');
    }

    function it_calls_the_hash_function_of_the_decorated_class_with_the_salted_password(
        PasswordHashingStrategy $passwordHashingStrategy
    ) {
        $passwordHashingStrategy->hashPassword('test_passwordtest_salt')->shouldBeCalled()->willReturn('test_hash');

        $this->hashPassword('test_password')->shouldReturn('test_hash');
    }

    function it_calls_the_verify_function_of_the_decorated_class_with_the_salted_password(
        PasswordHashingStrategy $passwordHashingStrategy
    ) {
        $passwordHashingStrategy->verifyPassword('test_passwordtest_salt', 'test_hash')->shouldBeCalled()->willReturn(true);

        $this->verifyPassword('test_password', 'test_hash')->shouldReturn(true);
    }
}
