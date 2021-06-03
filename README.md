# Auth

A framework & datastorage independent authentication library for PHP.

## Authentication vs Authorisation

This package performs only authentication - ensuring your application users are who they say they are.

You will need to handle authorisation - what your users can and can't do when logged in - elsewhere in your application.

## Installation

Installation is via [Composer](https://getcomposer.org/):

```bash
$ composer require cemerson/auth
```

or add it by hand to your `composer.json` file.

## How it Works

When your user attempts to log in, you pass the details to the package to verify the username and password, which then sets a cookie on your user's machine that expires when the browser session expires. Optionally, you can enable the "remember me" functionality to set a longer lasting cookie which will persist the login.

The Auth package allows you to choose from a number of different hashing strategies for your password security, or provide your own. In fact, you can use a different hashing strategy for each user, if you need to. This allows you to migrate your users from one strategy to another easily.  See the section on Hashing Strategies for more information.

## Configuration

As this package does not connect directly to your datastorage, you will need to provide some concrete implementations so the authentication mechanisms can access the user object and same session information into the database.

### AuthUserGateway

The `AuthUserGateway` connects `Auth` to your datastorage, allowing it to find the user that is attempting to login. You must implement this interface in your application; this would normally be in your user repository, or similar.

The interface requires only one function:

`findUserByUsername(string $username): AuthUser`

This functioon take the username and returns an instance of `AuthUser`.

### AuthUser

The `AuthUser` interface defines three functions for your user object or entity:

`getPasswordHashingStrategy(): PasswordHashingStrategy`

Here you return an instance of a hashing strategy. This can be one of the implementations provided by the package, or one of your own that implements the `PasswordHashingStrategy` interface.

We recommend you use either the `PasswordLockPasswordHashingStrategy` or the `PHPPasswordHashingAPIStrategy`. See the section on Hashing Strategies for more information.

`public function getUsername(): string`

Returns the username of this user.

`public function getPasswordHash(): string`

Returns the password of the user, hashed using the strategy returned above.

## Usage

You instantiate the `Auth` class with an instance of your `AuthUserGateway` and an instance of the `Session` interface.

`$auth = new Auth($userGateway, $session);`

*You do not need to implement the `Session` interface yourself, the package provides an implementation of this. For more information on how to use that implementation, see the Session section below*

Once you have an instance of the `Auth` class, you can do a number of things.

`$auth->login($username, $password)`

If the username is found, this function compare the provided password with the stored password and return `true` or `false`.

If the username is not found, the function will throw a `UserNotFound` exception.

If the login is successful, this function will also set the cookie on the user's machine.

If a user is already logged in, a `UserAlreadyLoggedIn` exception is thrown.

`$auth->isLoggedIn()`

Returns `true` is the user is already logged in, and `false` otherwise.

`$auth->getCurrentUser()`

Returns the `AuthUser` that is currently logged in.

If you call this function but the user is not logged in, a `NoUserLoggedIn` exception is thrown.

`$auth->logout()`

Logs the current user out, and resets the cookie.

## Hashing Strategies

Each user will have a `PasswordHashingStrategy` that controls how their password is hashed before it is stored in your datastorage layer.

The strategy has two functions:

`function hashPassword(string $password): string`

this returns a hashed version of the password, using the strategy

`function verifyPassword(string $passwordToVerify, string $passwordHash): bool`

This checks that the given password matches the hash.

### Which strategy

The package provides a number of implementations you can use.  If you are starting fresh, we suggest you use either the `PasswordLockPasswordHashingStrategy` or the `PHPPasswordHashingAPIStrategy`.

Using the `PHPPasswordHashingAPIStrategy` is straightforward:

`$strategy = new PHPPasswordHashingAPIStrategy(new PHPPasswordAPIWrapperImplementation());`

If you want to use the `PasswordLockPasswordHashingStrategy`, then you will need to generate and store a key. You *must not* store this key in the same datastore that you keep the hashed passwords.

`$key = Defuse\Crypto\Key::createNewRandomKey();`

Then pass this key to the strategy:

`$strategy = new PasswordLockPasswordHashingStrategy($key);`

The `Key` class provides some functions to help you store this key:

`$string = $key->saveToAsciiSafeString();`

`$key = Defuse\Crypto\Key::loadFromAsciiSafeString($string);`

### Migrating Strategies

Need some info here!

## Session

To use the provided `Session` implementation, you first need an instance of `PHPSessionGateway`. The only argument you need to pass to this is the domain of your site.

`$sessionGateway = new PHPSessionGateway('example.com');`

You also need an instance of `Clock`, which you can get using the provided `WallClock`:

`$clock = new WallClock(new \DateTimeZone('UTC'));`

Use both of these to return a session instance:

`$session = new AuthSession($sessionGateway, $clock);`

## Remember Me

*Expand this section*

- RememberedLoginGateway - implement interface
- RememberedLoginFactory - implement interface
- RememberedLogin - implement interface

RememberLoginService requires:
    - RememberedLoginGateway
    - CookieGateway -> PHPCookieGateway (requires "cookiedomain", secure)
    - Session -> AuthSession ^
    - AuthUserGateway
    - RememberedLoginFactory
    - Clock -> WallClock ^

Remember to cover the third argument to `$auth->login()` here.

## Notes

This library was put together with the help of the following resources:

* https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence

Still to implement:

 - Configurable rate limiting
 - Password strength requirements?
 - Password changing
 - Password reset / Forgotten password feature?
 - Password generation?
 - Two factor authentication?

Not in scope:

 - Roles, groups etc
