<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Routing;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Action,
    Identity\Identity,
};
use Innmind\UrlTemplate\Template;
use Innmind\Url\Path;
use Innmind\Immutable\Str;

final class Route
{
    private Action $action;
    private Template $template;
    private Name $name;
    private HttpResource $definition;

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
        $template = Str::of($name->asPath()->toString())
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
            Template::of($template->toString()),
            $name,
            $definition
        );
    }

    public function matches(Path $path): bool
    {
        $pattern = Str::of($this->template->toString())
            ->replace('{+prefix}', '')
            ->replace('{identity}', '.+')
            ->prepend('~^')
            ->append('$~')
            ->toString();

        return Str::of($path->toString())->matches($pattern);
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

    public function identity(Path $path): ?Identity
    {
        $pattern = Str::of($this->template->toString())
            ->replace('{+prefix}', '')
            ->replace('{identity}', '(?<identity>.+)')
            ->prepend('~^')
            ->append('$~')
            ->toString();

        $infos = Str::of($path->toString())->capture($pattern);

        if (!$infos->contains('identity')) {
            return null;
        }

        return new Identity($infos->get('identity')->toString());
    }
}
