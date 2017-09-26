<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Exception;

/**
 * To be thrown if the gateway does not support an action (list, get, create, etc...)
 */
class ActionNotImplementedException extends \DomainException implements Exception
{
}
