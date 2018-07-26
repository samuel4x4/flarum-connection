# User feature
The user feature regroup all the functions related to users. It also include adding a user to a group.

It include the following features :
* getUserByUserName(string $username, ?int $user = null): \Http\Promise\Promise
* getUser(int $userId = null,?int $user = null)
* updateEmail(string $email, string $username, int $userId = null, ?int $user = null): \Http\Promise\Promise
* updatePassword(string $password, string $username, int $userId = null,?int $user = null): Http\Promise\Promise
* addToGroup(int $userId, int $groupId,?int $user = null): \Http\Promise\Promise
* removeFromGroup(int $userId, int $groupId,?int $user = null): \Http\Promise\Promise
* deleteUser(int $userId): \Http\Promise\Promise

# Get User by user name
This asynchronous function search a user using it's username.

It requires :
* The username to search for
* The id of the user that will search for the user

It return either :
* An exception
* A user

# Get User 
This asynchronous function search a user using it's id

It requires :
* The id to search for
* The id of the user that will search for the user

It return either :
* An exception
* A user

# Update email 
This asynchronous function update the email of the user

It requires :
* The new email
* The current username 
* The id of the user that will do the operation

It return either :
* An exception
* A user

# Update email 
This asynchronous function update the password of the user

It requires :
* The new email
* The current username 
* The id of the user that will do the operation

It return either :
* An exception
* A user

# Add user to a group
This asynchronous function add user to a group

It requires :
* The id of the user
* The id of the group
* The id of the user that will realize the operation

It return either :
* An exception
* A boolean indicating the result of the operation


# Remove user from group
This asynchronous function remove user from a group

It requires :
* The id of the user
* The id of the group
* The id of the user that will realize the operation

It return either :
* An exception
* A boolean indicating the result of the operation


# Delete user
This asynchronous function will delete the user

It requires :
* The id of the user to delete
* The id of the user that will do the operation

It return either :
* An exception
* A boolean indicating the result of the operation