## Filtering
`Fuzz\MagicBox\Filter` handles Eloquent Query Builder modifications based on filter values passed through the `filters` 
parameter.

Tokens and usage:  

|    Token   |           Description           |                     Example                    |
|:----------:|:-------------------------------:|:----------------------------------------------:|
| `^`        | Field starts with               | `https://api.yourdomain.com/1.0/users?filters[name]=^John`                    |
| `$`        | Field ends with                 | `https://api.yourdomain.com/1.0/users?filters[name]=$Smith`                   |
| `~`        | Field contains                  | `https://api.yourdomain.com/1.0/users?filters[favorite_cheese]=~cheddar`      |
| `<`        | Field is less than              | `https://api.yourdomain.com/1.0/users?filters[lifetime_value]=<50`            |
| `>`        | Field is greater than           | `https://api.yourdomain.com/1.0/users?filters[lifetime_value]=>50`            |
| `>=`       | Field is greater than or equals | `https://api.yourdomain.com/1.0/users?filters[lifetime_value]=>=50`           |
| `<=`       | Field is less than or equals    | `https://api.yourdomain.com/1.0/users?filters[lifetime_value]=<=50`           |
| `=`        | Field is equal to               | `https://api.yourdomain.com/1.0/users?filters[username]==Specific%20Username` |
| `!=`       | Field is not equal to           | `https://api.yourdomain.com/1.0/users?filters[username]=!=common%20username`  |
| `[...]`    | Field is one or more of         | `https://api.yourdomain.com/1.0/users?filters[id]=[1,5,10]`                   |
| `![...]`   | Field is not one of             | `https://api.yourdomain.com/1.0/users?filters[id]=![1,5,10]`                  |
| `NULL`     | Field is null                   | `https://api.yourdomain.com/1.0/users?filters[address]=NULL`                  |
| `NOT_NULL` | Field is not null               | `https://api.yourdomain.com/1.0/users?filters[email]=NOT_NULL`                |

### Filtering relations
Assuming we have users and their related tables resembling the following structure:

```php
[
    'username'         => 'Bobby',
    'profile' => [
        'hobbies' => [
            ['name' => 'Hockey'],
            ['name' => 'Programming'],
            ['name' => 'Cooking']
        ]
    ]
]
```

We can filter by users' hobbies with `users?filters[profile.hobbies.name]=^Cook`. Relationships can be of arbitrary 
depth.

### Filter conjuctions
We can use `AND` and `OR` statements to build filters such as `users?filters[username]==Bobby&filters[or][username]==Johnny&filters[and][profile.favorite_cheese]==Gouda`. The PHP array that's built from this filter is:

```php
[
    'username' => '=Bobby',
    'or'       => [
          'username' => '=Johnny',
          'and'      => [
              'profile.favorite_cheese' => '=Gouda',
          ]	
    ]
]
```

and this filter can be read as `select (users with username Bobby) OR (users with username Johnny who's profile.favorite_cheese attribute is Gouda)`.
