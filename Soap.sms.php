<?php
$title = 'Database SMS Methods';
require_once 'includes/header.php';
require_once 'includes/SOAPincludes.php';
$devices = getData("j_device", ["key" => "id", "sort" => "name"]);
$servers = getData("j_serverio", ["key" => "id", "sort" => "name"]);
$datas = getData("j_data", ["key" => "id", "sort" => "address"]);
$schedEvents = getData("j_schedevent", ["key" => "name"]);
$groups = getData("j_addressesgroup", ["key" => "addressgroup"], true);
$events = getData("j_event", ["key" => "id"]);
$operators = ['==' => 'Equals', '!=' => 'NOT Equals','>' => 'Greater Than', '<' => 'Less Than', '<=' => 'Less Than OR Equal', '>=' => 'Greater Than OR Equal'];
$stdOut .= '<div class="soaptask">Should be refactored, removing condition and operator</div>';
$stdOut .= '<form class="wmo oddRow" action="soap.sms.php" method="get">
		<h3>Add SMS to I/O' . soapDescription('This method adds a SMS Event to the I/O Structure', ['server', 'device', 'data', 'condition', 'operator', 'sms message', 'address group']) . '</h3>
		<input type="hidden" name="method" value="ioSmsAdd"/>
		Server: ' . createSelect($servers, 0) . '
		Device: ' . createSelect($devices, 1) . '
		Data: ' . createSelect($datas, 2) . '
		Condition: <input type="text" name="soap[arg3]"/>
		Operator: ' . createSelect($operators, 4) . '
		Message: <input type="text" name="soap[arg5]"/>
		Group: ' . createSelect($groups, 6) . '
		<input ' . $disabled . 'type="submit" value="Add SMS!"/>
	</form>';
$stdOut .= '<form class="wmo evenRow" action="soap.sms.php" method="get">
		<h3>Remove SMS from I/O' . soapDescription('This method removes a SMS Event from the I/O Structure', ['server', 'device', 'data', 'condition', 'operator', 'event']) . '</h3>
		<input type="hidden" name="method" value="ioSmsRemove"/>
		Server: ' . createSelect($servers, 0) . '
		Device: ' . createSelect($devices, 1) . '
		Data: ' . createSelect($datas, 2) . '
		Condition: <input type="text" name="soap[arg3]"/>
		Operator: ' . createSelect($operators, 4) . '
		Event: ' . createSelect($events, 5) . '
		<input ' . $disabled . 'type="submit" value="Remove SMS!"/>
	</form>';
$stdOut .= '<form class="wmo oddRow" action="soap.sms.php" method="get">
		<h3>Update SMS in I/O' . soapDescription('This method updates a SMS Event in the I/O Structure', ['server', 'device', 'data', 'condition', 'operator', 'event', 'address group', 'sms message']) . '</h3>
		<input type="hidden" name="method" value="ioSmsUpdate"/>
		Server: ' . createSelect($servers, 0) . '
		Device: ' . createSelect($devices, 1) . '
		Data: ' . createSelect($datas, 2) . '
		Condition: <input type="text" name="soap[arg3]"/>
		Operator: ' . createSelect($operators, 4) . '
		Event: ' . createSelect($events, 5) . '
		Group: ' . createSelect($groups, 6) . '
		Message: <input type="text" name="soap[arg7]"/>
		<input ' . $disabled . 'type="submit" value="Update SMS!"/>
	</form>';
$stdOut .= '<form class="wmo evenRow" action="soap.sms.php" method="get">
		<h3>Add SMS to Schedule' . soapDescription('This method adds a SMS Event to the Schedule', ['schedule name', 'subject', 'email message', 'address group']) . '</h3>
		<input type="hidden" name="method" value="schedSmsAdd"/>
		ScheduledEvent: ' . createSelect($schedEvents, 0) . '
		Message: <input type="text" name="soap[arg1]"/>
		Group: ' . createSelect($groups, 2) . '
		<input ' . $disabled . 'type="submit" value="Add SMS!"/>
	</form>';
$stdOut .= '<form class="wmo oddRow" action="soap.sms.php" method="get">
		<h3>Remove SMS from Schedule' . soapDescription('This method removes a SMS Event from the Schedule', ['schedule name', 'event']) . '</h3>
		<input type="hidden" name="method" value="schedSmsRemove"/>
		ScheduledEvent: ' . createSelect($schedEvents, 0) . '
		Event: ' . createSelect($events, 1) . '
		<input ' . $disabled . 'type="submit" value="Remove SMS!"/>
	</form>';
$stdOut .= '<form class="wmo evenRow" action="soap.sms.php" method="get">
		<h3>Update SMS in Schedule' . soapDescription('This method updates a SMS Event in the Schedule', ['schedule name', 'event', 'address group', 'subject', 'email message']) . '</h3>
		<input type="hidden" name="method" value="schedSmsUpdate"/>
		ScheduledEvent: ' . createSelect($schedEvents, 0) . '
		Event: ' . createSelect($events, 1) . '
		Group: ' . createSelect($groups, 2) . '
		Message: <input type="text" name="soap[arg3]"/>
		<input ' . $disabled . 'type="submit" value="Update SMS!"/>
	</form>';
require_once 'includes/footer.php'; ?>