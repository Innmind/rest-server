<?php

use Innmind\Rest\Server\Definition\{
    Directory,
    HttpResource,
    Gateway,
    Identity,
    Property,
    Access,
    Type\StringType,
};
use Innmind\Immutable\{
    Map,
    Set,
};

return Directory::of(
    'top_dir',
    Set::of(
        Directory::class,
        Directory::of(
            'sub_dir',
            Set::of(Directory::class),
            new HttpResource(
                'res',
                new Gateway('command'),
                new Identity('uuid'),
                Set::of(
                    Property::class,
                    Property::required(
                        'uuid',
                        new StringType,
                        new Access(Access::READ)
                    ),
                    Property::required(
                        'image',
                        new StringType,
                        new Access(Access::READ)
                    )
                )
            )
        )
    ),
    new HttpResource(
        'image',
        new Gateway('command'),
        new Identity('uuid'),
        Set::of(
            Property::class,
            Property::required(
                'uuid',
                new StringType,
                new Access(Access::READ)
            ),
            Property::required(
                'url',
                new StringType,
                new Access(Access::READ, Access::CREATE, Access::UPDATE)
            )
        ),
        null,
        null,
        Map::of('string', 'string')
            ('alternate', 'top_dir.image')
    )
);
