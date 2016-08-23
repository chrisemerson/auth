<?php declare(strict_types = 1);

namespace spec\CEmerson\AceAuth\PasswordHashingStrategies;

use PhpSpec\ObjectBehavior;

class SHA1PasswordHashingStrategySpec extends ObjectBehavior
{
    use PasswordHashingStrategyTestTrait;

    function it_returns_the_hash_of_a_given_password()
    {
        $this->hashPassword('test_password')->shouldReturnCI('9FB7FE1217AED442B04C0F5E43B5D5A7D3287097');
    }

    function verification_of_a_correct_password_succeeds()
    {
        $this->verifyPassword('test_password', '9FB7FE1217AED442B04C0F5E43B5D5A7D3287097')->shouldReturn(true);
    }

    function verification_of_an_incorrect_password_fails()
    {
        $this->verifyPassword('wrong_password', '9FB7FE1217AED442B04C0F5E43B5D5A7D3287097')->shouldReturn(false);
    }
}
