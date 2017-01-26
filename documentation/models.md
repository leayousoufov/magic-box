## Model Setup
Models need to implement `Fuzz\MagicBox\Contracts\MagicBoxResource` before MagicBox will allow them to be exposed as a MagicBox resource. This is done so exposure is an explicit process and no more is exposed than is needed.

Models also need to define their own `$fillable` array including attributes and relations that can be filled through this model. For example, if a User has many posts and has many comments but an API consumer should only be able to update comments through a user, the `$fillable` array would look like:

```
protected $fillable = ['username', 'password', 'name', 'comments'];
```

MagicBox will only modify attributes/relations that are explicitly defined.

## Resolving models
Magic Box is great and all, but we don't want to resolve model classes ourselves before we can instantiate a repository...

If you've configured a RESTful URI structure with pluralized resources (i.e. `https://api.mydowmain.com/1.0/users` maps to the User model), you can use `Fuzz\MagicBox\Utility\Modeler` to resolve a model class name from a route name.
