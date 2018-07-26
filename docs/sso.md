# SSO 
The sso library provide the following functions
* login(string $login,string $password):\GuzzleHttp\Promise\promiseinterface
* signup(string $login,string $password,string $email):\Http\Promise\Promise
* isUserConnected(string $cookieSession)

## Login
The login is an asynchronous function returning a Guzzle promise. It requires the login & password of a user.
It will return either :
* An exception that will be throwed (on resolution of the promise)
* A Token object with session & user id

## Is connected
It is as an asynchronous function returning a Guzzle promise. It take as parameter a HTTP foundation request & a boolean to indicate wether or not to cross check the cookie.
If the double check option is true, then it will request the API to see if the cookie had not been invalidated by Flarum.
Once resolved it will return either:
* An exception
* A boolean indicating if the user is connected

## Signup
It is an asynchronous function returning a Guzzle promise.
It require :
* The login of the user
* The password of the user
* The email of the user
Once resolved it will return either :
* An exception
* A user model object
   
