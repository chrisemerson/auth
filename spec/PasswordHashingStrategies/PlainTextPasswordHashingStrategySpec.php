<?php declare(strict_types = 1);

namespace spec\CEmerson\AceAuth\PasswordHashingStrategies;

use CEmerson\AceAuth\Exceptions\PlainTextPasswordStorageNotSupported;
use PhpSpec\ObjectBehavior;

class PlainTextPasswordHashingStrategySpec extends ObjectBehavior
{
    function it_throws_an_exception_when_you_try_to_use_this_class_for_hashing()
    {
        $this->shouldThrow(new PlainTextPasswordStorageNotSupported())->during('hashPassword', ['test_password']);
    }

    function verification_of_a_correct_password_succeeds()
    {
        $this->verifyPassword('test_password', 'test_password')->shouldReturn(true);
    }

    function verification_of_an_incorrect_password_fails()
    {
        $this->verifyPassword('wrong_password', 'test_password')->shouldReturn(false);
    }
}
