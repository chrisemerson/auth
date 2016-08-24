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

    function it_successfully_verifies_a_correct_password()
    {
        $this->verifyPassword('test_password', '9FB7FE1217AED442B04C0F5E43B5D5A7D3287097')->shouldReturn(true);
    }

    function it_fails_to_verify_an_incorrect_password()
    {
        $this->verifyPassword('wrong_password', '9FB7FE1217AED442B04C0F5E43B5D5A7D3287097')->shouldReturn(false);
    }
}
