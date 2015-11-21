# REST Server

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/rest-server/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Innmind/rest-server/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Innmind/rest-server/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Innmind/rest-server/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/Innmind/rest-server/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Innmind/rest-server/build-status/master)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/2caf0b66-38d9-4aec-bec4-148b11d9877c/big.png)](https://insight.sensiolabs.com/projects/2caf0b66-38d9-4aec-bec4-148b11d9877c)

Smart library to easily build REST APIs in a descriptive way, you tell what your resources are and it will handle the rest.

The approach here is slightly different from other library used to build APIs in the sense that in general you expose your entities directly through the API (minus some fields in some cases), but you don't want to do that in every case. If you keep a layer of abstraction between your entities and the resources you expose allows a greater flexibility, consequently you'll be less enclined of thinking about versioning as you can change your inner architecture without affecting directly whats exposed to the world.

## Installation

Via composer:

```sh
composer require innmind/rest-server
```

## Architecture

It revolves around this principles:

* resources exposed via a configuration file
* mechanism to translate a resource to an entity, and vice versa
* storage facades
* bunch of events to hook at almost any step

The goal is that by default you only need to write configuration to expose your entities, but if you want to build something more advanced you can by hooking in the system to change default behaviour.

## Setup

```php
use Innmind\Rest\Server\Application;
use Symfony\Component\HttpFoundation\Request;

$app = new Application(
    '/path/to/config/file.yml',
    '/path/to/config/services.yml'
);
$response = $app->handle(Request::createFromGlobals());
$response->send();
```

So what happens here?!

First you tell the library to load the definitions of your resources (located at `/path/to/config/file.yml`). Then to load your services (at `/path/to/config/services.yml`), which contains the storages definitions (more on that in a bit).

And in the end you call the mechanism to transform the request into a response (and send it).

### Storages

The storages you give to the setup are facades for doctrine (or neo4j) and implements a simple [`StorageInterface`](StorageInterface.php). To build a `DcotrineStorage` you can do so as follows:

```yml
# /path/to/config/service.yml
services:
    my_doctrine:
        parent: storage.abstract.doctrine
        arguments:
            index_0: @doctrine
        tags:
            - { name: storage, alias: dcotrine }

    doctrine:
        class: ... # check the doctrine website to know how to create an instance
```

To build a `Neo4jStorage` you can have a look at this [fixture](fixtures/services/local.yml) (as you can see it is very similar).

### Configuration file

Now is time to create the yaml file to describe your resources. Here's an example:

```yaml
collections:
    blog:
        storage: doctrine
        resources:
            article:
                id: id
                properties:
                    id:
                        type: int
                        access: [READ]
                    title:
                        type: string
                        access: [READ, CREATE, UPDATE]
                    slug:
                        type: string
                        access: [READ, CREATE]
                    content:
                        type: string
                        access: [READ, CREATE, UPDATE]
                    author:
                        type: resource
                        access: [READ]
                        options:
                            resource: author
                options:
                    class: Some\Entity\Article
            author:
                id: id
                properties:
                    id:
                        type: int
                        access: [READ]
                    name:
                        type: string
                        access: [READ, CREATE, UPDATE]
                options:
                    class: Some\Entity\Author
```

So here we have the resources `article` and `author` regrouped under the collection `blog`, basically this will allow to generates routes for `/blog/article/` and `/blog/author/`. Both resources will be persisted via the storage `doctrine` (you can define a storage per resource also).

The `access`es is a simple way to tell which property is allowed in read, create or update action (read `GET`, `POST`, `PUT` verbs).

This will generates the following routes:

* `GET /blog/article/` return all the articles
* `OPTIONS /blog/article/` expose the structure of an article (almost what you've described in the yaml)
* `GET /blog/article/{id}` return the article for the given `id`
* `POST /blog/article/` create a new article
* `PUT /blog/article/{id}` update the article with the given id
* `DELETE /blog/article/{id}` delete the article with the given id
* `GET /blog/author/`
* `OPTIONS /blog/author/`
* `GET /blog/author/{id}`
* `POST /blog/author/`
* `PUT /blog/author/{id}`
* `DELETE /blog/author/{id}`

### Formats

By default this library allows you to use `json` as input and output format (`url encoded form` is also supported as input format).

If you wish to add a new format, you can do it simply by building a Symfony `Encoder` and declare it as a service in your `yaml` file. On this service you need to add 2 tags: `serializer.encoder` and `format`. Have a look [here](config/service/serializer.yml) to check how the `json` format is declared.

As you saw, the `format` tag need at least 2 attributes: `format` and `mime`. The first is the one you need to use when you implement `EncoderInterface::supportsEncoding`; the other is used to map the `Content-Type` header to this format. In case your format can be declared under several mime types, you simply need to add a `format` tag per mime type.

### Events

The `Application` class used in this library extends the Symfony `Kernel` so by default you can hook on its events (`REQUEST`, `CONTROLLER`, `VIEW`, `RESPONSE`, `TERMINATE` and `EXCEPTION`).

Here is a list of additional events:

* `Events::ROUTE`: allows you to alter a route definition (if you call `$event->stopPropagation()`, it will prevent the route from being added to the router)
* `Events::RESOURCE_BUILD`: use this event when you want to transpose by yourself a `HttpResource` into an entity comprehensible by the storage
* `Events::ENTITY_BUILD`: use this event when you want to return a resource that can't be directly built from a resource (for instance a resource property is not directly mapped to an entity property)
* `Events::PRE_[READ|CREATE|UPDATE|DELETE]`: fired before any action is done in the storage, allowing you to return an alternative content from the expected one
* `Events::POST_[READ|CREATE|UPDATE|DELETE]`: fired right before the storage return the result of the action, could be used to filter the returned content
* `Events::DOCTRINE_READ_QUERY_BUILDER`: fired after the `DoctrineStorage` has built a query builder object and before it is executed, it allows you to add filters to it (like `where` clauses)
* `Events::NEO4J_READ_QUERY_BUILDER`: fired after the `Neo4jStorage` has built a query builder object and before it is executed, it allows you to add filters to it (like `where` clauses)
