<?php declare(strict_types = 1);

namespace spec\CEmerson\Auth\PasswordHashingStrategies\PHPPasswordAPI;

use PhpSpec\ObjectBehavior;

class PHPPasswordAPIWrapperImplementationSpec extends ObjectBehavior
{
    function it_hashes_a_password_and_verifies_the_same_password()
    {
        $passwordHash = $this->hash('test_password', PASSWORD_DEFAULT);

        $this->verify('test_password', $passwordHash)->shouldReturn(true);
    }

    function it_fails_verification_of_a_different_password_after_hashing()
    {
        $passwordHash = $this->hash('test_password', PASSWORD_DEFAULT);

        $this->verify('wrong_password', $passwordHash)->shouldReturn(false);
    }
}
