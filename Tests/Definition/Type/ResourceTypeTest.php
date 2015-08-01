<?php

namespace Innmind\Rest\Server\Tests\Definition\Type;

use Innmind\Rest\Server\Definition\Type\ResourceType;
use Innmind\Rest\Server\Definition\TypeInterface;
use Innmind\Rest\Server\Definition\Property;
use Innmind\Rest\Server\Definition\Resource;
use Symfony\Component\Validator\Constraints\Collection;

class ResourceTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConstraints()
    {
        $t = new ResourceType;
        $p = new Property('foo');
        $p->addOption('resource', $r = new Resource('foo'));
        $r->addProperty(
            (new Property('foo'))
                ->setType('string')
        );

        $this->assertSame(
            1,
            count($t->getConstraints($p))
        );
        $this->assertInstanceOf(
            Collection::class,
            $t->getConstraints($p)[0]
        );
        $this->assertSame(
            1,
            count($t->getConstraints($p)[0]->fields)
        );
        $this->assertSame(
            'string',
            $t->getConstraints($p)[0]->fields['foo']->constraints[0]->type
        );
    }
}
