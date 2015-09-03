<?php

namespace Innmind\Rest\Server;

class Events
{
    const ROUTE = 'innmind.rest.server.route';
    const RESOURCE_BUILD = 'innmind.rest.server.resource.build';
    const ENTITY_BUILD = 'innmind.rest.server.entity.build';
    const STORAGE_PRE_READ = 'innmind.rest.server.storage.pre.read';
    const STORAGE_POST_READ = 'innmind.rest.server.storage.post.read';
    const STORAGE_PRE_CREATE = 'innmind.rest.server.storage.pre.create';
    const STORAGE_POST_CREATE = 'innmind.rest.server.storage.post.create';
    const STORAGE_PRE_UPDATE = 'innmind.rest.server.storage.pre.update';
    const STORAGE_POST_UPDATE = 'innmind.rest.server.storage.post.update';
    const STORAGE_PRE_DELETE = 'innmind.rest.server.storage.pre.delete';
    const STORAGE_POST_DELETE = 'innmind.rest.server.storage.post.delete';
    const RESPONSE = 'innmind.rest.server.response';
    const REQUEST = 'innmind.rest.server.request';

    const DOCTRINE_READ_QUERY_BUILDER = 'innmind.rest.server.storage.doctrine.read_query_builder';
    const NEO4J_READ_QUERY_BUILDER = 'innmind.rest.server.storage.neo4j.read_query_builder';
}
