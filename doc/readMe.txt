The Spark Rally-plugin is a program used for test optimization. The program includes features such as viewing rally user stories, drag and drop, user story search.

2014-11-20-2014-00
	1. No Release and No Owner Option
	2. HTML editor

2014-11-6-2014--01
	Daily Build Update 2:
	1. Added different Submit buttons.

2014-11-6-2014--00
	Daily Build Update:
	1. Added create user story visualization to the frontend.
	2. Add/edit forms now auto fill release, owner, and state fields.
	3. PHP file now returns blocked and icon fields alongside the id field for newly created user stories. This is so the front end has needed info for data visualization
	4. Removed dismissable "X" from angular strap alerts for demo. The "X" was appearing to close to the text when the width of the alert is set to auto. This may be re-added in the future if a method can be found to move the "X".
	5. Edit modal set to close when submitted for demo.

2014-11-5-2014--01
	Daily Build Update 2:
	1. PHP file now returns id of newly created (nodes created on the front-end). This will allow the frontend to create a node with 100% needed information on the frontend.

2014-11-5-2014--00
	Daily Build Update:
	1. Moved undo button to the right of the logout button. This is so if the user repeatedly clicks the undo button and it dissapears, then they won't accidently logout

2014-11-4-2014--00
	Daily Build Update:
	1. Re-added multi tree to code for upcoming demo
	2. Increased alert duration from 3 seconds to 5 seconds.
	3. Undo button now is hidden when no undo action is available instead of disabled.

2014-11-3-2014--00
	Daily Build Update:
	1. Fixed blinking login bug where when user refreshed the page with saved credentials the login form would be visible for a split second.


2014-10-31-2014--00
	Daily Build Update:
	1. Fixed a resource call issue with add and edit.
	2. Reformatted php files (phpkit now includes custom functions so phpIndex has less duplication of code)

2014-10-30-2014--00
	Daily Build Update:
	1. Restyled edit form/modal
	2. modified a phptoolkit function

2014-10-29-2014--01
	Daily Build Update 2:
	1. Added functionality so user is required to enter a title when adding a new user story.
	2. Added edit form/modal. Some edit functionality implemented (incomplete)

2014-10-29-2014-00:
	Daily Build Update:
	1. Reworked message system. Angular Strap $alerts are used instead of custom "message-center"
	2. Added error catching to add userstory resource call


2014-10-24-2014-00:
	Daily Build Update:
	1.Added more comments to the indexPHP file

2014-10-23-2014-00:
	Daily Build Update:
	1.Added Create User Story functionality
	2.Added Delete User Story functionality
	3.Session Timeout 
	4.Commented indexPHP file
	5.Commented front-end code

2014-09-29-2014-00:
	Daily Build Update:
	Undo bug fixed. This bug was caused by the user performing an action, undoing, performing the same action, and undoing again. This caused the second undo to no move the node as needed.

2014-09-25-2014-00:
	Daily Build Update:
	Undo feature fully implemented(front end visually and back end)
	Fixed popover opening/closing issue

2014-09-24-2014-00:
	Daily Build Update:
	DragnDrop/Undo feature fixed (Back-end syntax was causing a bug)
	Fixed the php GET algorithm for user story trees that include a root parent with a release tag (Working now)
	Dragndrop now can be used to drag a user story to a root parent position
	

2014-09-08-2014-00:
	This is a build so Ibraheem can work on an error catch for if a release has no user stories
	
2014-09-02-2014-00:
	1. Added popover with functionality
	2. Added modal with functionality

2014-08-19-2014-01:
	1. Added enable drag and drop

2014-08-19-2014--00: (Troubleshoot build)
	1. Popover added with images (button functionality not included)

2014-08-13-2014:
	1. New logout feature
	2. New automatic login if user refreshes the page
	3. Very primitive popover for jstree nodes. (Note: Node must be clicked twice to activate popover. Will be updated in the future)
	4. Back-end files separated for organization/future updates
	5. Iteration switch case added to back-end
	6. Back-end files include more comments
	

Included in this build:
    Index.html - contains all of the view properties for the project
    poverover.html - contains the code for the popover
    modal.html - contains the code for the user story info modal
    /php
	indexPHP.php - contains the back-end code for communicating with the front-end and transforming the data needed by the front-end    
	phpkit.php - contains the php toolkit used for the RESTful API commands that connect the back-end and the Rally servers
    /js
        App.js - contains the module for the project
        Controllers.js - contains the logic behind the views on the index.html page. This includes the Project Name/ Release List dynamic dependent drop-down lists and the login controller.
        Directives.js - contains the jstree format setup. 
        Factory.js - contains the services used by the front-end.
    /lib
        contains libraries and images used by front-end.
    /css
        styles.css - contains the styling for the front-end.