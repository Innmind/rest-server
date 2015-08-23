<?php

namespace Innmind\Rest\Server\Tests;

use Innmind\Rest\Server\Validator;
use Innmind\Rest\Server\Definition\Resource as Definition;
use Innmind\Rest\Server\Definition\Property;
use Innmind\Rest\Server\Resource;
use Symfony\Component\Validator\Validation;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    protected $v;
    protected $d;
    protected $d2;

    public function setUp()
    {
        $this->v = new Validator(Validation::createValidator());
        $this->d2 = new Definition('inner');
        $this->d2
            ->addProperty(
                (new Property('foo'))
                    ->setType('string')
                    ->addAccess('READ')
            )
            ->addProperty(
                (new Property('bar'))
                    ->setType('string')
                    ->addAccess('UPDATE')
            );
        $this->d = new Definition('foo');
        $this->d
            ->addProperty(
                (new Property('foo'))
                    ->setType('resource')
                    ->addAccess('READ')
                    ->addAccess('UPDATE')
                    ->addOption('resource', $this->d2)
            )
            ->addProperty(
                (new Property('bar'))
                    ->setType('string')
                    ->addAccess('READ')
                    ->addAccess('UPDATE')
            )
            ->addProperty(
                (new Property('baz'))
                    ->setType('array')
                    ->addAccess('READ')
                    ->addOption('inner_type', 'string')
            )
            ->addProperty(
                (new Property('date'))
                    ->setType('date')
                    ->addAccess('READ')
                    ->addAccess('UPDATE')
            );
    }

    public function testValidate()
    {
        $inner = new Resource;
        $inner
            ->setDefinition($this->d2)
            ->set('foo', 'foo');
        $r = new Resource;
        $r
            ->setDefinition($this->d)
            ->set('bar', 'bar')
            ->set('baz', ['baz'])
            ->set('foo', $inner)
            ->set('date', '2015-12-31');

        $violations = $this->v->validate($r, 'READ');
        $this->assertSame(0, $violations->count());
        $inner->set('bar', 'bar');
        $violations = $this->v->validate($r, 'UPDATE');
        $this->assertSame(2, $violations->count());
        $this->assertSame(
            'Array[foo][foo]:' . "\n" . '    This field was not expected. (code 2)',
            (string) $violations->get(0)
        );
        $this->assertSame(
            'Array[baz]:' . "\n" . '    This field was not expected. (code 2)',
            (string) $violations->get(1)
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage You can only validate access for READ, CREATE or UPDATE
     */
    public function testThrowIfValidatingUnknownAccess()
    {
        $this->v->validate(new Resource, 'foo');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage You can only validate a resource or a collection of ones
     */
    public function testThrowIfNotValidatingResources()
    {
        $this->v->validate([], 'READ');
    }
}
