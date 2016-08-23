<?php declare(strict_types = 1);

namespace spec\CEmerson\AceAuth\PasswordHashingStrategies;

use PhpSpec\ObjectBehavior;

class SHA256PasswordHashingStrategySpec extends ObjectBehavior
{
    use PasswordHashingStrategyTestTrait;

    function it_returns_the_hash_of_a_given_password()
    {
        $this->hashPassword('test_password')->shouldReturnCI('10A6E6CC8311A3E2BCC09BF6C199ADECD5DD59408C343E926B129C4914F3CB01');
    }

    function verification_of_a_correct_password_succeeds()
    {
        $this->verifyPassword('test_password', '10A6E6CC8311A3E2BCC09BF6C199ADECD5DD59408C343E926B129C4914F3CB01')->shouldReturn(true);
    }

    function verification_of_an_incorrect_password_fails()
    {
        $this->verifyPassword('wrong_password', '10A6E6CC8311A3E2BCC09BF6C199ADECD5DD59408C343E926B129C4914F3CB01')->shouldReturn(false);
    }
}
