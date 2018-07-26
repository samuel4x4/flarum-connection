# Tags features
The tag feature is handling the tag (that behave in Flarum like forum categories).
It include the following functions :
* addTag(string $name, string $slug, string $description, string $color, bool $isHidden, bool $isRestricted, ?int $user = null): \Http\Promise\Promise
* updateTag(string $name, string $slug, string $description, string $color, bool $isHidden, bool $isRestricted, int $id, ?int $user = null): \Http\Promise\Promise
* getTags(?int $user = null): \Http\Promise\Promise
* deleteTag(int $id, ?int $user = null): \Http\Promise\Promise
* setTagPermission(int $tagId, FlarumPermissions $permissions, ?int $user = null): bool
* getTagOrder(?int $user = null): \Http\Promise\Promise
* setTagOrder(FlarumTagOrder $order,?int $user = null): \GuzzleHttp\Promise\PromiseInterface
 
# Add tag
This asynchronous function add a new tag

It requires 
* The name
* The slug (ie the name that will be used for URI, it should be unique)
* The description of the tag
* The color of the tag (an hexadecimal color like #efefef)
* Should the tag be hidden
* The id of the user that will add the tag

It returns either :
* An exception
* The created tag

# Update tag
This asynchronous function update a tag

It requires 
* The name
* The slug (ie the name that will be used for URI, it should be unique)
* The description of the tag
* The color of the tag (an hexadecimal color like #efefef)
* Should the tag be hidden
* The id of the existing 
* The id of the user that will add the tag

It returns either :
* An exception
* The updated tag

# Get tags
This asynchronous function get all the tags
It requires 
* The id of the user that will retrieve the tag (he will only retrieve the accessible tags)

It returns either :
* An exception
* The list of tags

# Delete tag
This asynchronous function get all the tags

It requires 
* The id of the tag
* The id of the user that will delete the tag (must be an admin)

It returns either :
* An exception
* The list of tags

# Set tag permission
This asynchronous function set the VIEW, CREATE, RESPOND, MODERATE permission for a tag.
It associate existing groups with these 4 capabilities. Once set, these group will have the corresponding permission.
The View permission only allow user to view a forum (CREATE, RESPOND & MODERATE rights include VIEW rights)
The Respond permission allow user to respond to a discussion (CREATE & moderate rights include RESPOND rights)
The create permission allow user to a post a new discussion (Moderate right include CREATE right)
The moderate permission give moderator role of the tag.

It requires 
* The id of the tag
* The permission set (a FlarumPermission Object)
* The id of the user that will update the tag (must be an admin)

It returns either :
* An exception
* A boolean on the success of the operation

# Get Tag order
This asynchronous function return the order the primary tags (the tags that are required for discussion post).

It requires :
* The id of the user that will get the tag order

It return either :
* An exception
* The tag order

# Set Tag order
This asynchronous function set a new tag order. It could also add or remove tags as primary tags.

It requires :
* The tag order (warning, the tag order should ALWAYS be edited after being retrieved from Get Tag order feature, otherwise it will erase all the previous primary tags)
* The id of the user that will get the tag order

It return either :
* An exception
* A boolean on the success of the operation

