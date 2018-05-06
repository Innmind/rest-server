<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Routing;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Action,
};
use Innmind\UrlTemplate\Template;
use Innmind\Url\PathInterface;
use Innmind\Immutable\Str;

final class Route
{
    private $action;
    private $template;
    private $name;
    private $definition;

    public function __construct(
        Action $action,
        Template $template,
        Name $name,
        HttpResource $definition
    ) {
        $this->action = $action;
        $this->template = $template;
        $this->name = $name;
        $this->definition = $definition;
    }

    public static function of(
        Action $action,
        Name $name,
        HttpResource $definition
    ): self {
        $template = Str::of((string) $name->asPath())
            ->prepend('{+prefix}');

        switch ($action) {
            case Action::get():
            case Action::update():
            case Action::remove():
            case Action::link():
            case Action::unlink():
                $template = $template->append('{identity}');
                break;
        }

        return new self(
            $action,
            new Template((string) $template),
            $name,
            $definition
        );
    }

    public function matches(PathInterface $path): bool
    {
        $pattern = (string) Str::of((string) $this->template)
            ->replace('{+prefix}', '')
            ->replace('{identity}', '.+')
            ->prepend('~^')
            ->append('$~');

        return Str::of((string) $path)->matches($pattern);
    }

    public function action(): Action
    {
        return $this->action;
    }

    public function template(): Template
    {
        return $this->template;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function definition(): HttpResource
    {
        return $this->definition;
    }
}
