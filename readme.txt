DESCRIPTION
----------------
This module adds a 'trash' for nodes. Nodes can be moved to the trash instead
of being deleted and thus can be restored or permanently deleted later on.

INSTALL
----------------
Install as usual for Drupal modules

USE
----------------
You need to set the trash behaviour per node type (admin/content/types).
The normal 'delete' permission is used to determine who can move nodes to the
trash. To permanently delete nodes, the 'purge trash' permission is needed.

PERMISSIONS
----------------
'view trash': Access to the 'trash' page.
'restore trash': Allows to restore nodes from the trash.
'purge trash': Allows to permanently delete nodes from the trash.

TECHNICAL
----------------
See the file comment in trash.module for technical information.
