# Flarum Connection Library 
## Status
Refactoring required on config & sso

## Installation
Do the composer install in order to install all the libraries

## Configuration
In order to work properly, the following tasks needs to be acheived :
* The portal and the forum needs to share the same root domain (for example : laborange.fr for the portal & forums.laborange.fr for flarum)
* An access token must be created on Flarum in order to allow the library to access to the Flarum API
* Forum must be configured in https with a valid SSL certificate (not all the API will work in http only)
* An admin user should be created and it's id retrieved, it will be the user that will be used by the library to perform admin only task.

Once all these tasks are achieved the library could be initialized with a FlarumConnectorConfig object with the following parameters:
* Address of the forum instance
* Root domain to be used to set the cookie
* The access token defined in Flarum database
* The if of the admin user to use for admin request
* The number of days the session will be kept alive

```
$config = new \FlarumConnection\Models\FlarumConnectorConfig (
                'https://my_forum_adress.mydomain.com', 
                'mydomain.com',
                'AnAccessToken',
                1,
                3000);
```

The library also requires a logger object that needs to be a PSR-2 logger. 

```
$Connector = new FlarumConnector($config,$logger);
```

## Features
### HTTP SSO
* Login (and get a cookie)
* Logout (and remove cookie)
* Is connected
* [Documentation of HTTP SSO](/docs/httpsso.md)

### SSO
* Login
* Creation of an account
* [Documentation of SSO](/docs/sso.md)

### Users
* Update the password of a user
* Update the email of the password
* Associate a user to a group
* Delete of a user
* Get a user by id
* Search for a user
* [Documentation of user](/docs/user.md)


### Tags
* Create a tag
* Delete a tag
* Define specific rights for tag
* Get all the tags
* [Documentation of tags](/docs/tags.md)

### Groups
* Create a group
* Update a group
* Delete a group
* [Documentation of groups](/docs/groups.md)

### Discussions
* Create a discussion
* Update a discussion
* Delete a discussion
* [Documentation of discussions](/docs/discussions.md)

### Posts
* Create a post
* Update a post
* Delete a post
* [Documentation of posts](/docs/post.md)

## Integration hooks
The integration hooks to be done are detailed there [Documentation of integration](/docs/integration.md)]