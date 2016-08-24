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

    function it_successfully_verifies_a_correct_password()
    {
        $this->verifyPassword('test_password', 'test_password')->shouldReturn(true);
    }

    function it_fails_to_verify_an_incorrect_password()
    {
        $this->verifyPassword('wrong_password', 'test_password')->shouldReturn(false);
    }
}
