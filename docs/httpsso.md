# HTTP SSO
The HTTP SSO is supported through the main Flarum connector class.

It provide helpers for an application using Symfony/HTTPFoundations requests & responses in order to set & remove cookies and to check if a user is connected

3 features are available :
* login(string $login, string $password): \GuzzleHttp\Promise\promiseinterface
* isConnected(Request $request, bool $doubleCheck): bool
* logout(Response $resp): void

## Login
The login is an asynchronous function returning a Guzzle promise. It requires the login & password of a user.
It will return either :
* An exception that will be throwed (on resolution of the promise)
* A HTTPFoundation cookie that will need to be set

## Is connected
The is Connected feature take as parameter a HTTP foundation request & a boolean to indicate wether or not to cross check the cookie.
If the double check option is true, then it will request the API to see if the cookie had not been invalidated by Flarum.
It returns a boolean

## Logout
The logout take a response as reference parameter.
It will trigger the invalidation of the cookie.

