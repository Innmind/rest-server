<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Translator;

use Innmind\Rest\Server\{
    Translator\SpecificationTranslator,
    Translator\SpecificationTranslatorInterface,
    Specification\Filter
};
use Innmind\Url\QueryInterface;

class SpecificationTranslatorTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            SpecificationTranslatorInterface::class,
            new SpecificationTranslator
        );
    }

    public function testTranslate()
    {
        $translator = new SpecificationTranslator;

        $spec = (new Filter('range', [0, 42]))
            ->and(new Filter('foo', 'bar'));

        $query = $translator->translate($spec);

        $this->assertInstanceOf(QueryInterface::class, $query);
        $this->assertSame(
            'range%5B0%5D=0&range%5B1%5D=42&foo=bar',
            (string) $query
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\SpecificationNotUsableAsQueryException
     */
    public function testThrowWhenOrConditionFound()
    {
        $translator = new SpecificationTranslator;

        $spec = (new Filter('range', [0, 42]))
            ->or(new Filter('foo', 'bar'));

        $translator->translate($spec);
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\SpecificationNotUsableAsQueryException
     */
    public function testThrowWhenNotConditionFound()
    {
        $translator = new SpecificationTranslator;

        $spec = (new Filter('range', [0, 42]))->not();

        $translator->translate($spec);
    }
}
