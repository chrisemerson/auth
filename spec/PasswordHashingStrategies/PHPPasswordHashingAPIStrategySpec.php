<?php declare(strict_types = 1);

namespace spec\CEmerson\AceAuth\PasswordHashingStrategies;

use CEmerson\AceAuth\PasswordHashingStrategies\PHPPasswordAPI\PHPPasswordAPIWrapper;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PHPPasswordHashingAPIStrategySpec extends ObjectBehavior
{
    function let(PHPPasswordAPIWrapper $PHPPasswordAPIWrapper)
    {
        $this->beConstructedWith($PHPPasswordAPIWrapper);
    }

    function it_delegates_calls_to_hash_to_wrapper_class(PHPPasswordAPIWrapper $PHPPasswordAPIWrapper)
    {
        $PHPPasswordAPIWrapper
            ->hash('test_password', Argument::type('int'), Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn('test_hash');

        $this->hashPassword('test_password')->shouldReturn('test_hash');
    }

    function it_delegates_calls_to_verify_to_wrapper_class(PHPPasswordAPIWrapper $PHPPasswordAPIWrapper)
    {
        $PHPPasswordAPIWrapper
            ->verify('test_password', 'test_hash')
            ->shouldBeCalled()
            ->willReturn(true);

        $this->verifyPassword('test_password', 'test_hash')->shouldReturn(true);
    }

    function it_fails_verification_if_wrapper_class_returns_false(PHPPasswordAPIWrapper $PHPPasswordAPIWrapper)
    {
        $PHPPasswordAPIWrapper
            ->verify('test_password', 'test_hash')
            ->shouldBeCalled()
            ->willReturn(false);

        $this->verifyPassword('test_password', 'test_hash')->shouldReturn(false);
    }

    function it_lets_you_set_a_different_algorithm(PHPPasswordAPIWrapper $PHPPasswordAPIWrapper)
    {
        $PHPPasswordAPIWrapper
            ->hash('test_password', 6, Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn('test_hash');

        $this->setAlgorithm(6);

        $this->hashPassword('test_password')->shouldReturn('test_hash');
    }

    function it_lets_you_set_a_different_cost(PHPPasswordAPIWrapper $PHPPasswordAPIWrapper)
    {
        $PHPPasswordAPIWrapper
            ->hash('test_password', Argument::type('int'), Argument::withEntry('cost', 8))
            ->shouldBeCalled()
            ->willReturn('test_hash');

        $this->setCost(8);

        $this->hashPassword('test_password')->shouldReturn('test_hash');
    }
}
