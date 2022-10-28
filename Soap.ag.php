<?php
$title = 'Database Address Group Methods';
require_once 'includes/header.php';
require_once 'includes/SOAPincludes.php';
$userUsers = getData("users", ["key" => "id", "sort" => "name"]);
$userGroups = getData("grouptable", ["key" => "id", "sort" => "name"]);
$addressesGroups = getData("j_addressesGroup", ["key" => "id"]);
$stdOut .= '<div class="soaptask">Needs a remove single user/group method</div>';
$stdOut .= '<form class="wmo oddRow" action="soap.ag.php" method="get">
		<h3>Add User' . soapDescription('This method adds a User to an Address Group', ['address group', 'user']) . '</h3>
		<input type="hidden" name="method" value="addressGroupAddUser"/>
		ID: <input type="text" name="soap[arg0]"/>
		User: ' . createSelect($userUsers, 1) . '
		<input ' . $disabled . 'type="submit" value="Add User!"/>
	</form>';
$stdOut .= '<form class="wmo evenRow" action="soap.ag.php" method="get">
		<h3>Add Group' . soapDescription('This method adds a Group to an Address Group', ['address group', 'group']) . '</h3>
		<input type="hidden" name="method" value="addressGroupAddGroup"/>
		ID: <input type="text" name="soap[arg0]"/>
		Group: ' . createSelect($userGroups, 1) . '
		<input ' . $disabled . 'type="submit" value="Add Group!"/>
	</form>';
$stdOut .= '<form class="wmo oddRow" action="soap.ag.php" method="get">
		<h3>Remove Address Group' . soapDescription('This method removes an Address Group', ['address group']) . '</h3>
		<input type="hidden" name="method" value="addressGroupRemove"/>
		ID: ' . createSelect($addressesGroups, 0) . '
		<input ' . $disabled . 'type="submit" value="Remove Address Group!"/>
	</form>';
$stdOut .= '<form class="wmo evenRow" action="soap.ag.php" method="get">
		<h3>Update User' . soapDescription('This method updates a User in an Address Group', ['address group', 'address group', 'user']) . '</h3>
		<input type="hidden" name="method" value="addressGroupUpdateUser"/>
		Old ID: ' . createSelect($addressesGroups, 0) . '
		New ID: <input type="text" name="soap[arg1]"/>
		User: ' . createSelect($userUsers, 2) . '
		<input ' . $disabled . 'type="submit" value="Update User!"/>
	</form>';
$stdOut .= '<form class="wmo oddRow" action="soap.ag.php" method="get">
		<h3>Update Group' . soapDescription('This method updates a Group in an Address Group', ['address group', 'address group', 'group']) . '</h3>
		<input type="hidden" name="method" value="addressGroupUpdateGroup"/>
		Old ID: ' . createSelect($addressesGroups, 0) . '
		New ID: <input type="text" name="soap[arg1]"/>
		Group: ' . createSelect($userGroups, 2) . '
		<input ' . $disabled . 'type="submit" value="Update Group!"/>
	</form>';
$stdOut .= '</div>';
require_once 'includes/footer.php'; ?>