<?php
$title = 'Database E-Mail Methods';
require_once 'includes/header.php';
require_once 'includes/SOAPincludes.php';
$devices = getData("j_device", ["key" => "id", "sort" => "name"]);
$servers = getData("j_serverio", ["key" => "id", "sort" => "name"]);
$datas = getData("j_data", ["key" => "id", "sort" => "address"]);
$groups = getData("j_addressesgroup", ["key" => "addressgroup"], true);
$events = getData("j_event", ["key" => "id"]);
$schedEvents = getData("j_schedevent", ["key" => "name"]);
$operators = ['==' => 'Equals', '!=' => 'NOT Equals','>' => 'Greater Than', '<' => 'Less Than', '<=' => 'Less Than OR Equal', '>=' => 'Greater Than OR Equal'];
$stdOut .= '<div class="soaptask">Should be refactored, removing condition and operator</div>';
$stdOut .= '<form class="wmo oddRow" action="soap.email.php" method="get">
		<h3>Add Email to I/O' . soapDescription('This method adds an E-Mail event to the I/O Structure', ['server', 'device', 'data', 'condition', 'operator', 'subject', 'email message', 'address group']) . '</h3>
		<input type="hidden" name="method" value="ioEmailAdd"/>
		Server: ' . createSelect($servers, 0) . '
		Device: ' . createSelect($devices, 1) . '
		Data: ' . createSelect($datas, 2) . '
		Condition: <input type="text" name="soap[arg3]"/>
		Operator: ' . createSelect($operators, 4) . '
		Subject: <input type="text" name="soap[arg5]"/>
		Message: <input type="text" name="soap[arg6]"/>
		Group: ' . createSelect($groups, 7) . '
		<input ' . $disabled . 'type="submit" value="Add Email!"/>
	</form>';
$stdOut .= '<form class="wmo evenRow" action="soap.email.php" method="get">
		<h3>Remove Email from I/O' . soapDescription('This method removes an E-Mail event from the I/O Structure then cleans up after itself', ['server', 'device', 'data', 'condition', 'operator', 'event']) . '</h3>
		<input type="hidden" name="method" value="ioEmailRemove"/>
		Server: ' . createSelect($servers, 0) . '
		Device: ' . createSelect($devices, 1) . '
		Data: ' . createSelect($datas, 2) . '
		Condition: <input type="text" name="soap[arg3]"/>
		Operator: ' . createSelect($operators, 4) . '
		Event: ' . createSelect($events, 5) . '
		<input ' . $disabled . 'type="submit" value="Remove Email!"/>
	</form>';
$stdOut .= '<form class="wmo oddRow" action="soap.email.php" method="get">
		<h3>Update Email in I/O' . soapDescription('This method updates an existing E-Mail event in the I/O Structure', ['server', 'device', 'data', 'condition', 'operator', 'event', 'address group', 'subject', 'email message']) . '</h3>
		<input type="hidden" name="method" value="ioEmailUpdate"/>
		Server: ' . createSelect($servers, 0) . '
		Device: ' . createSelect($devices, 1) . '
		Data: ' . createSelect($datas, 2) . '
		Condition: <input type="text" name="soap[arg3]"/>
		Operator: ' . createSelect($operators, 4) . '
		Event: ' . createSelect($events, 5) . '
		Group: ' . createSelect($groups, 6) . '
		Subject: <input type="text" name="soap[arg7]"/>
		Message: <input type="text" name="soap[arg8]"/>
		<input ' . $disabled . 'type="submit" value="Update Email!"/>
	</form>';
$stdOut .= '<form class="wmo evenRow" action="soap.email.php" method="get">
		<h3>Add Email to Schedule' . soapDescription('This method adds an E-Mail event to the Schedule structure', ['schedule name', 'subject', 'email message', 'address group']) . '</h3>
		<input type="hidden" name="method" value="schedEmailAdd"/>
		ScheduledEvent: ' . createSelect($schedEvents, 0) . '
		Subject: <input type="text" name="soap[arg1]"/>
		Message: <input type="text" name="soap[arg2]"/>
		Group: ' . createSelect($groups, 3) . '
		<input ' . $disabled . 'type="submit" value="Add Email!"/>
	</form>';
$stdOut .= '<form class="wmo oddRow" action="soap.email.php" method="get">
		<h3>Remove Email from Schedule' . soapDescription('This method removes an E-Mail event from the Schedule structure', ['schedule name', 'event']) . '</h3>
		<input type="hidden" name="method" value="schedEmailRemove"/>
		ScheduledEvent: ' . createSelect($schedEvents, 0) . '
		Event: ' . createSelect($events, 1) . '
		<input ' . $disabled . 'type="submit" value="Remove Email!"/>
	</form>';
$stdOut .= '<form class="wmo evenRow" action="soap.email.php" method="get">
		<h3>Update Email in Schedule' . soapDescription('This method updates an E-Mail event in the Schedule structure', ['schedule name', 'event', 'address group', 'subject', 'email message']) . '</h3>
		<input type="hidden" name="method" value="schedEmailUpdate"/>
		ScheduledEvent: ' . createSelect($schedEvents, 0) . '
		Event: ' . createSelect($events, 1) . '
		Group: ' . createSelect($groups, 2) . '
		Subject: <input type="text" name="soap[arg3]"/>
		Message: <input type="text" name="soap[arg4]"/>
		<input ' . $disabled . 'type="submit" value="Update Email!"/>
	</form>';
require_once 'includes/footer.php'; ?>