# Integration
## List of integration hooks
The API should be used in the portals on the following location :
* Signup : On signup on the portal, the same action needs to be realized on Flarum 
* Activation : On activation, the user account needs to be activated on the forum, the user should also be added to the client or employee group accordingly
* Removal of a user : When a user is deleted from the portal it should also be deleted on the forum
* Login : On login on the portal, the login operation needs to be realized on the forum
* Logout : On logout on the portal, the logout operation needs to be performed on the forum
* Email update : When a user update his email on the portal, the user needs to be updated on the forum
* Creation of an article : When an admin create a new article with comment on, a new discussion needs to be created on the forum
* Creation of a lot : When an admin create a lot, a group needs to be created on the forum
* Delete of a lot : When an admin delete a lot, the associated group needs to be deleted from the forum
* Add or remove users to lot : When an admin add or remove users to a lot, the add or removed users should be added or removed from the associated group
* Forum block creation : When an admin create a new forum block, a new tag with specific rights needs to be created
* Forum block association to the wall : When an admin associate a forum block to a lot, the rights needs to be adapted by configuring the rights for the good lots

## List of potential integration hooks
* Project cleanup : When a forum goes to archived or terminated status, all users should be purged from the groups
* Password update : When a user update his password on the portal, the user needs to be updated on the forum
* Avatar update : When a user update his avatar, the same avatar needs to be put on the forum
* Orange employee status update : When a user change of status between employee and client, his group needs to be updated
* Update of an article : When an admin update an article, the corresponding discussion needs to updated also
* Display of articles comments : On display of an article with comments, the last articles needs to be displayed with a link to the topic.
* Delete of an article : When an admin delete an article, the corresponding discussion needs to be updated



* Project post removal : After 1 year of terminated or archived status, all the tags and their corresponding discussions should be deleted

