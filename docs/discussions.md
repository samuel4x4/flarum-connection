# Discussion feature
The discussion feature regroup all the functions related to discussion handling :
* postTopic(string $title, string $content, array $tags,int $user = null): \Http\Promise\Promise
* updateTopic(int $id, string $title, string $content, array $tags,?int $user = null): \Http\Promise\Promise
* getDiscussions(string $tag, int $offset = 0,?int $user = null): \Http\Promise\Promise
* deleteTopic => To be done

# Post topic
The post topic asynchronous function create a new topic on the forum.

It requires :
* A title 
* A content
* A list of the id of tags to be associated with the topic
* An optional user parameter to select the user that will post the topic (if empty it will be the designated admin)

It returns :
* An exception
* The created topic

# Update topic
The update topic asynchronous function create a new topic on the forum.

It requires :
* The id of the topic to update
* A title 
* A content
* A list of the id of tags to be associated with the topic
* An optional user parameter to select the user that will post the topic (if empty it will be the designated admin)

It returns :
* An exception
* The updated topic

# Get Discussion
The get discussion asynchronous function return a list of discussion

It requires :
* The tag name of the discussion
* The offset (ie the start position of the request, for example an offset set at 20 will retrieve the discussions from position 20 to 39)
* An optional user parameter to select the user that will retrieve the discussion (the discussion displayed will corresponds to his rights)

