#Post feature
The post feature is handling post. It include the following functions.
* addPost(int $discussionId,string $content,?int $user = null): Promise
* updatePost(string $content,int $postId,?int $user = null): Promise
* getPosts(int $discussionId,?int $user = null): Promise
* deletePost(int $postId, ?int $user = null):

# Add post
This asynchronous function create a new post on a discussion.

It requires 
* The id of the discussion
* The content
* The id of the user that will post (if not set default admin user will be selected)

It returns either :
* An exception
* The created post


# Update post
This asynchronous function update a post of a discussion.

It requires 
* The content
* The id of the post
* The id of the user that will post (if not set default admin user will be selected)

It returns either :
* An exception
* The updated post

# Get posts
This asynchronous function return the list of posts of a discussion.

It requires 
* The content
* The id of the post
* The id of the user that will get the posts and retrieve them according to his rights (if not set default admin user will be selected)

It returns either :
* An exception
* The list of the post of the topic

# Delete post
This asynchronous function delete a post.

It requires 
* The id of the post
* The id of the user that will delete (if not set default admin user will be selected)

It returns either :
* An exception
* A boolean indicating the status of the operation