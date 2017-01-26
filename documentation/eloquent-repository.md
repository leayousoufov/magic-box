## Eloquent Repository
`Fuzz\MagicBox\EloquentRepository` implements a CRUD repository that cascades through relationships,
whether or not related models have been created yet.

Consider a simple model where a User has many Posts. EloquentRepository's basic usage is as follows:

Create a User with the username Steve who has a single Post with the title Stuff.

```php
$repository = (new EloquentRepository)
    ->setModelClass('User')
    ->setInput([
        'username' => 'steve',
        'nonsense' => 'tomfoolery',
        'posts'    => [
            'title' => 'Stuff',
        ],
    ]);

$user = $repository->save();
```

When `$repository->save()` is invoked, a User will be created with the username "Steve", and a Post will
be created with the `user_id` belonging to that User. The nonsensical "nonsense" property is simply
ignored, because it does not actually exist on the table storing Users.

By itself, EloquentRepository is a blunt weapon with no access controls that should be avoided in any
public APIs. It will clobber every relationship it touches without prejudice. For example, the following
is a BAD way to add a new Post for the user we just created.

```php
$repository
    ->setInput([
        'id' => $user->id,
        'posts'    => [
            ['title' => 'More Stuff'],
        ],
    ])
    ->save();
```

This will delete poor Steve's first post—not the intended effect. The safe(r) way to append a Post
would be either of the following:

```php
$repository
    ->setInput([
        'id' => $user->id,
        'posts'    => [
            ['id' => $user->posts->first()->id],
            ['title' => 'More Stuff'],
        ],
    ])
    ->save();
```

```php
$post = $repository
    ->setModelClass('Post')
    ->setInput([
        'title' => 'More Stuff',
        'user' => [
            'id' => $user->id,
        ],
    ])
    ->save();
```

Generally speaking, the latter is preferred and is less likely to explode in your face.

The public API methods that return models from a repository are:

1. `create`
1. `read`
1. `update`
1. `delete`
1. `save`, which will either call `create` or `update` depending on the state of its input
1. `find`, which will find a model by ID
1. `findOrFail`, which will find a model by ID or throw `\Illuminate\Database\Eloquent\ModelNotFoundException`

The public API methods that return an `\Illuminate\Database\Eloquent\Collection` are:

1. `all`
