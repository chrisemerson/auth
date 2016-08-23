<?php
namespace spec\CEmerson\AceAuth\PasswordHashingStrategies;

trait PasswordHashingStrategyTestTrait
{
    function it_hashes_a_password_and_verifies_the_same_password()
    {
        $passwordHash = $this->hashPassword('test_password');

        $this->verifyPassword('test_password', $passwordHash)->shouldReturn(true);
    }

    function it_fails_verification_of_a_different_password_after_hashing()
    {
        $passwordHash = $this->hashPassword('test_password');

        $this->verifyPassword('wrong_password', $passwordHash)->shouldReturn(false);
    }

    public function getMatchers()
    {
        return [
            'returnCI' => function ($subject, $value) {
                return strcasecmp($subject, $value) == 0;
            }
        ];
    }
}
