<?php

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Definition\Types;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class Validator
{
    protected $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Validate a resource or a collection of ones
     *
     * @param HttpResourceInterface|Collection $resources
     * @param string $access One of the constants of Access
     *
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    public function validate($resources, $access)
    {
        if (!in_array($access, [Access::READ, Access::CREATE, Access::UPDATE])) {
            throw new \InvalidArgumentException(sprintf(
                'You can only validate access for %s, %s or %s',
                Access::READ,
                Access::CREATE,
                Access::UPDATE
            ));
        }

        if (
            !$resources instanceof HttpResourceInterface &&
            !$resources instanceof Collection
        ) {
            throw new \InvalidArgumentException(
                'You can only validate a resource or a collection of ones'
            );
        }

        $data = $this->buildRawRepresentation($resources);
        $constraint = $this->buildConstraintTree($resources, $access);

        return $this->validator->validate($data, $constraint);
    }

    /**
     * Transform the resources in a tree of arrays
     *
     * @param HttpResourceInterface|Collection $resources
     *
     * @return array
     */
    protected function buildRawRepresentation($resources)
    {
        $data = [];

        if ($resources instanceof Collection) {
            foreach ($resources as $resource) {
                $data[] = $this->buildRawRepresentation($resource);
            }
        } else {
            $definition = $resources->getDefinition();

            foreach ($definition->getProperties() as $prop) {
                if (!$resources->has((string) $prop)) {
                    continue;
                }

                if ($prop->containsResource()) {
                    if ($prop->getType() === 'array') {
                        $data[(string) $prop] = [];
                        foreach ($resources->get((string) $prop) as $subValue) {
                            $data[(string) $prop][] = $this->buildRawRepresentation(
                                $subValue
                            );
                        }
                    } else {
                        $data[(string) $prop] = $this->buildRawRepresentation(
                            $resources->get((string) $prop)
                        );
                    }
                } else {
                    $data[(string) $prop] = $resources->get((string) $prop);
                }
            }
        }

        return $data;
    }

    /**
     * Build the constraints tree in order to validate data
     *
     * @param HttpResourceInterface|Collection $resources
     * @param string $access
     *
     * @return Constraint
     */
    protected function buildConstraintTree($resources, $access)
    {
        if ($resources instanceof Collection) {
            $constraints = [];
            foreach ($resources as $resource) {
                $constraints[] = $this->buildConstraintTree(
                    $resource,
                    $access
                );
            }
            $constraint = new Assert\All(['constraints' => $constraints]);
        } else {
            $fields = [];
            $definition = $resources->getDefinition();

            foreach ($definition->getProperties() as $prop) {
                $fields[(string) $prop] = [];

                if ($prop->hasAccess($access)) {
                    if (!$prop->hasOption('optional')) {
                        $fields[(string) $prop][] = new Assert\NotNull;
                    }
                } else {
                    unset($fields[(string) $prop]);

                    continue;
                }

                if ($prop->containsResource()) {
                    if ($resources->has((string) $prop)) {
                        $fields[(string) $prop][] = $this->buildConstraintTree(
                            $resources->get((string) $prop),
                            $access
                        );
                    }
                } else {
                    $type = Types::get($prop->getType());
                    $fields[(string) $prop] = array_merge(
                        $fields[(string) $prop],
                        $type->getConstraints($prop)
                    );
                }

                if (
                    $prop->hasOption('optional') &&
                    empty($fields[(string) $prop])
                ) {
                    unset($fields[(string) $prop]);
                }
            }

            $constraint = new Assert\Collection(['fields' => $fields]);
        }

        return $constraint;
    }
}
