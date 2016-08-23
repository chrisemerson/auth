<?php declare(strict_types = 1);

namespace spec\CEmerson\AceAuth\PasswordHashingStrategies;

use PhpSpec\ObjectBehavior;

class MD5PasswordHashingStrategySpec extends ObjectBehavior
{
    use PasswordHashingStrategyTestTrait;

    function it_returns_the_hash_of_a_given_password()
    {
        $this->hashPassword('test_password')->shouldReturnCI('16EC1EBB01FE02DED9B7D5447D3DFC65');
    }

    function verification_of_a_correct_password_succeeds()
    {
        $this->verifyPassword('test_password', '16EC1EBB01FE02DED9B7D5447D3DFC65')->shouldReturn(true);
    }

    function verification_of_an_incorrect_password_fails()
    {
        $this->verifyPassword('wrong_password', '16EC1EBB01FE02DED9B7D5447D3DFC65')->shouldReturn(false);
    }
}
