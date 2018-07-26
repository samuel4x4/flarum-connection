# Groups feature
The group feature is handling Flarum group management.

It includes the following functions :
* addGroup(string $nameSingular, string $namePlural, string $color, string $icon, ?int $user = null): \Http\Promise\Promise
* updateGroup(string $nameSingular, string $namePlural, string $color, string $icon, int $id, int $user = null): \Http\Promise\Promise
* getGroups(?int $user = null): \Http\Promise\Promise
* deleteGroup(int $id, int $user = null):\Http\Promise\Promise

#Add Group
This asynchronous function create a group.

It requires 
* The name of the group in singular (ex : admin)
* The name of the group in plural (ex : admins)
* The color of the group icon, it should be an hexadecimal color (could be empty)
* The icon name based on font-awesome icon name (could be empty)
* The id of the user that will add the group (should be an admin) (if not set default admin user will be selected)

It returns either :
* An exception
* The created group
 
 
#Update Group
This asynchronous function update a group.

It requires 
* The name of the group in singular (ex : admin)
* The name of the group in plural (ex : admins)
* The color of the group icon, it should be an hexadecimal color (could be empty)
* The icon name based on font-awesome icon name (could be empty)
* The id of the group to update
* The id of the user that will add the group (should be an admin) (if not set default admin user will be selected)

It returns either :
* An exception
* The created group

#Get groups
This asynchronous function update a group.

It requires 
* The id of the user that will do the request (if not set default admin user will be selected)

It returns either :
* An exception
* The list of visible groups for the user

#Delete group
This asynchronous function delete a group
It requires 
* The id of the group to delete
* The id of the user that will do the request (if not set default admin user will be selected)

It returns either :
* An exception
* A boolean indicating the result of the operation