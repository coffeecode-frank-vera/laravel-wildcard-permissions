# Laravel Wildcard Permission

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/coffeecode-frank-vera/laravel-wildcard-permissions/blob/main/LICENSE)
[![Latest Stable Version](https://poser.pugx.org/coffeecode-frank-vera/laravel-wildcard-permissions/v)](https://packagist.org/packages/coffeecodemx/wildcard-permissions)
[![Total Downloads](https://poser.pugx.org/coffeecode-frank-vera/laravel-wildcard-permissions/downloads)](https://packagist.org/packages/coffeecodemx/wildcard-permissions)
[![Build Status](https://travis-ci.com/your-username/your-repo.svg?branch=main)](https://travis-ci.com/coffeecode-frank-vera/laravel-wildcard-permissions)

A powerful permission management library for Laravel applications.

## Features

- Role-based access control (RBAC)
- Fine-grained permission management
- Flexible permission assignment
- Easy integration with Laravel's authentication system
- Lightweight and efficient

## Installation

You can install this package via Composer. Run the following command:

```bash
composer require coffeecodemx/wildcard-permissions
```

Then add the service provider in your Laravel project

```php
<?php
return [
    ...
    'providers' => ServiceProvider::defaultProviders()->merge([
            /*
            * Package Service Providers...
            */
            CoffeeCode\WildcardPermissions\WildcardPermissionsServiceProvider::class,
    ...
];
```

Next you need to publish the configuration and migration files with the following command

```bash
php artisan vendor:publish --provider="CoffeeCode\WildcardPermissions\WildcardPermissionsServiceProvider"
```

Now you'll see a new configuration file in /config directory, just do the changes needed for table names and classes.
A recommended change for a basic usage is to change under pivot_names.model_id to you model id which usually is user_id

Now run the migrations

```bash
php artisan migrate
```

Add the traits to the model you wish to have permissions

```php
<?php

namespace App\Models;

use CoffeeCode\WildcardPermissions\Traits\HasPermissions;
use CoffeeCode\WildcardPermissions\Traits\HasRoles;
...

class User extends Authenticatable
{
    use ..., HasRoles, HasPermissions;

    ...
}
```

That's it!

## Usage and examples

### Create new Permission

To create a new permission you just need to run the following command.

```php
$permission = WildcardPermission::create([
    "short_name" => "ModuleA Create Permission",
    "guard_name" => "modulea:create",
    "description" => "Permission in module to create something"
]);
```

Short name and description are very descriptive, however guard_name is the special one, this field will only accepts alphabetic upper and lowecase and : symbol to separate namespaces, modules, you name it!

You can combine multiple namespaces like this:
```
module:submodule:write
```

### Find Permission

Since guard name is unique, you can easyly find a permission using guard name or if you want to find it using other fields just check the following examples.
```php
$permission1 = WildcardPermission::findByGuardName("modulea:create");
$permission2 = WildcardPermission::findByShortName("ModuleA Create Permission");
$permission3 = WildcardPermission::findById(1);
```

### Assign direct permissions to a Model

First your model should have the Trait HasPermissions, then is just

```php
$permission1 = WildcardPermission::findByGuardName("modulea:create");
$permission2 = WildcardPermission::findByGuardName("modulea:read");
$user = User::find(1);
$user->givePermissionTo($permission1, $permission2);
```

And if you want to revoke a permission is using the following

```php
$permission1 = WildcardPermission::findByGuardName("modulea:create");
$permission2 = WildcardPermission::findByGuardName("modulea:read");
$user = User::find(1);
$user->revokePermissionsTo($permission1, $permission2);
```

### Create new Role

Roles are basically a collection of permissions and the way to create a new one is using the following command
```php
$role = Role::create([
    "short_name" => "Module Reader",
    "guard_name" => "module:reader",
    "description" => "Role for readers"
]);
```

Now for assigning or revoking permissions is the same as we do with models

```php
$permission1 = WildcardPermission::findByGuardName("modulea:create");
$permission2 = WildcardPermission::findByGuardName("modulea:read");
$role->givePermissionTo($permission1, $permission2);
```

or revoking permissions

```php
$permission1 = WildcardPermission::findByGuardName("modulea:create");
$permission2 = WildcardPermission::findByGuardName("modulea:read");
$role->revokePermissionsTo($permission1, $permission2);
```

### Assigning roles to a user

First your model should have the Trait HasRoles, then is just

```php
$role1 = Role::findByGuardName("module:reader");
$role2 = Role::findByGuardName("module:writer");
$user->assignRole($role1, $role2);
```

or you can unassign it

```php
$role1 = Role::findByGuardName("module:reader");
$role2 = Role::findByGuardName("module:writer");
$user->unassignRole($role1, $role2);
```

### Check if your user has one or more roles

If you want to check for roles you have 3 operations

```php
$role1 = Role::findByGuardName("module:reader");
$role2 = Role::findByGuardName("module:writer");

$user->assignRole($role1);
// For the 3 operations you can use ID, guard_name or Role object
$user->hasRole($role1);// true
$user->hasRole("module:reader");// true
$user->hasRole(1);// true
$user->hasAnyRole($role1, $role1);// true
$user->hasAllRoles($role1, $role2);// false
```

### Check if the user has permissions

For checking permissions you can search by permission object, by guard_name or by wildcard

```php
$user = User::find(1);
$role = Role::findByGuardName("module:reader"); // Only the :read

$permission1String = "modulea:create";// Permission name
$permission2String = "modulea:read";// Permission name
$permission3String = "moduleb:read";// Permission name

$permission1 = WildcardPermission::findByGuardName($permission1String);
$permission2 = WildcardPermission::findByGuardName($permission2String);
$permission3 = WildcardPermission::findByGuardName($permission3String);

$role->givePermissionTo($permission2);
$role->givePermissionTo($permission3);
$user->givePermissionTo($permission1);
$user->assignRole($role);// User can read in module a and b also create in module a

echo $user->hasPermissionTo($permission1); // true
echo $user->hasPermissionTo($permission2); // true
echo $user->hasDirectPermission($permission1); // true
echo $user->hasDirectPermission($permission2); // false
echo $user->hasPermissionViaRole($permission1); // false
echo $user->hasPermissionViaRole($permission2); // true
echo $user->hasRole($role); // true
echo $user->hasRole("module:reader"); // true
echo $user->hasRole(1); // true
echo $user->hasAllRoles($role, 2); // false
echo $user->hasAnyRole($role, 2); // true

echo $user->hasPermissionTo("modulea:create,read"); // false should have permission to read and create in module A
echo $user->hasPermissionTo("modulea:create|read"); // true should have permission to read or create in module A
echo $user->hasPermissionTo("modulea:*"); // true should have any permission for module A
```