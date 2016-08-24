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

    function it_successfully_verifies_a_correct_password()
    {
        $this->verifyPassword('test_password', '10A6E6CC8311A3E2BCC09BF6C199ADECD5DD59408C343E926B129C4914F3CB01')->shouldReturn(true);
    }

    function it_fails_to_verify_an_incorrect_password()
    {
        $this->verifyPassword('wrong_password', '10A6E6CC8311A3E2BCC09BF6C199ADECD5DD59408C343E926B129C4914F3CB01')->shouldReturn(false);
    }
}
