<?php declare(strict_types = 1);

namespace spec\CEmerson\AceAuth\PasswordHashingStrategies;

use PhpSpec\ObjectBehavior;

class PHPPasswordHashingAPISpec extends ObjectBehavior
{
    const TEST_PASSWORD = 'test_password';
    const WRONG_PASSWORD = 'wrong_password';

    function it_hashes_a_password_and_verifies_the_same_password()
    {
        $passwordHash = $this->hashPassword(self::TEST_PASSWORD);

        $this->verifyPassword(self::TEST_PASSWORD, $passwordHash)->shouldReturn(true);
    }

    function it_fails_verification_of_a_different_password()
    {
        $passwordHash = $this->hashPassword(self::TEST_PASSWORD);

        $this->verifyPassword(self::WRONG_PASSWORD, $passwordHash)->shouldReturn(false);
    }
}
