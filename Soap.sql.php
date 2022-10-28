<?php
$title = 'Database SQL-to-CSV Methods';
require_once 'includes/header.php';
require_once 'includes/SOAPincludes.php';
$devices = getData("j_device", ["key" => "id", "sort" => "name"]);
$servers = getData("j_serverio", ["key" => "id", "sort" => "name"]);
$datas = getData("j_data", ["key" => "id", "sort" => "address"]);
$events = getData("j_event", ["key" => "id"]);
$eventFileSql = getData("j_eventfilesql", ["key" => "id"]);
$schedEvents = getData("j_schedevent", ["key" => "name"]);
$durationTypes = getData("j_eventfilesqldurationtype", ["key" => "id", "sort" => "name"]);
$operators = ['==' => 'Equals', '!=' => 'NOT Equals','>' => 'Greater Than', '<' => 'Less Than', '<=' => 'Less Than OR Equal', '>=' => 'Greater Than OR Equal'];
$stdOut .= '<div class="soaptask">Should be refactored, removing condition and operator</div>';
$stdOut .= '<form class="wmo oddRow" action="soap.sql.php" method="get">
		<h3>Add SQL to I/O' . soapDescription('This method adds a SQL Event to the I/O Structure', ['server', 'device', 'data', 'condition', 'operator', 'table', 'query', 'path', 'duration']) . '</h3>
		<div class="soaptask">Duration is a HashMap unsupported data type</div>
		<input type="hidden" name="method" value="ioSqlAdd"/>
		Server: ' . createSelect($servers, 0) . '
		Device: ' . createSelect($devices, 1) . '
		Data: ' . createSelect($datas, 2) . '
		Condition: <input type="text" name="soap[arg3]"/>
		Operator: ' . createSelect($operators, 4) . '
		Table: <input type="text" name="soap[arg5]"/>
		Query: <input type="text" name="soap[arg6]"/>
		Path: <input type="text" name="soap[arg7]"/>
		Duration: <input type="text" name="soap[arg8]"/>
		<input ' . $disabled . 'type="submit" value="Add SQL!"/>
	</form>';
$stdOut .= '<form class="wmo evenRow" action="soap.sql.php" method="get">
		<h3>Remove SQL from I/O' . soapDescription('This method removes a SQL Event from the I/O Structure', ['server', 'device', 'data', 'condition', 'operator', 'event']) . '</h3>
		<input type="hidden" name="method" value="ioSqlRemove"/>
		Server: ' . createSelect($servers, 0) . '
		Device: ' . createSelect($devices, 1) . '
		Data: ' . createSelect($datas, 2) . '
		Condition: <input type="text" name="soap[arg3]"/>
		Operator: ' . createSelect($operators, 4) . '
		Event: ' . createSelect($events, 5) . '
		<input ' . $disabled . 'type="submit" value="Remove SQL!"/>
	</form>';
$stdOut .= '<form class="wmo oddRow" action="soap.sql.php" method="get">
		<h3>Update SQL in I/O' . soapDescription('This method updates a SQL Event in the I/O Structure', ['server', 'device', 'data', 'condition', 'operator', 'event', 'table', 'query', 'path']) . '</h3>
		<input type="hidden" name="method" value="ioSqlUpdate"/>
		Server: ' . createSelect($servers, 0) . '
		Device: ' . createSelect($devices, 1) . '
		Data: ' . createSelect($datas, 2) . '
		Condition: <input type="text" name="soap[arg3]"/>
		Operator: ' . createSelect($operators, 4) . '
		Event: ' . createSelect($events, 5) . '
		Table: <input type="text" name="soap[arg6]"/>
		Query: <input type="text" name="soap[arg7]"/>
		Path: <input type="text" name="soap[arg8]"/>
		<input ' . $disabled . 'type="submit" value="Update SQL!"/>
	</form>';
$stdOut .= '<form class="wmo evenRow" action="soap.sql.php" method="get">
		<h3>Add SQL Duration to I/O' . soapDescription('This method adds a Duration to a SQL Event', ['server', 'device', 'data', 'condition', 'operator', 'file sql', 'duration']) . '</h3>
		<div class="soaptask">Duration is a HashMap unsupported data type</div>
		<input type="hidden" name="method" value="ioSqlDurationAdd"/>
		Server: ' . createSelect($servers, 0) . '
		Device: ' . createSelect($devices, 1) . '
		Data: ' . createSelect($datas, 2) . '
		Condition: <input type="text" name="soap[arg3]"/>
		Operator: ' . createSelect($operators, 4) . '
		Event File SQL: ' . createSelect($eventFileSql, 5) . '
		Duration: <input type="text" name="soap[arg6]"/>
		<input ' . $disabled . 'type="submit" value="Add SQL Duration!"/>
	</form>';
$stdOut .= '<form class="wmo oddRow" action="soap.sql.php" method="get">
		<h3>Remove SQL Duration from I/O' . soapDescription('This method removes a Duration from a SQL Event', ['server', 'device', 'data', 'condition', 'operator', 'file sql']) . '</h3>
		<input type="hidden" name="method" value="ioSqlDurationRemove"/>
		Server: ' . createSelect($servers, 0) . '
		Device: ' . createSelect($devices, 1) . '
		Data: ' . createSelect($datas, 2) . '
		Condition: <input type="text" name="soap[arg3]"/>
		Operator: ' . createSelect($operators, 4) . '
		Event File SQL: ' . createSelect($eventFileSql, 5) . '
		<input ' . $disabled . 'type="submit" value="Remove SQL Duration!"/>
	</form>';
$stdOut .= '<form class="wmo evenRow" action="soap.sql.php" method="get">
		<h3>Update SQL Duration in I/O' . soapDescription('This method updates a Duration in a SQL Event', ['server', 'device', 'data', 'condition', 'operator', 'file sql', 'duration type', 'duration value']) . '</h3>
		<input type="hidden" name="method" value="ioSqlDurationUpdate"/>
		Server: ' . createSelect($servers, 0) . '
		Device: ' . createSelect($devices, 1) . '
		Data: ' . createSelect($datas, 2) . '
		Condition: <input type="text" name="soap[arg3]"/>
		Operator: ' . createSelect($operators, 4) . '
		Event File SQL: ' . createSelect($eventFileSql, 5) . '
		Type: ' . createSelect($durationTypes, 6) . '
		Value: <input type="text" name="soap[arg7]"/>
		<input ' . $disabled . 'type="submit" value="Update SQL Duration!"/>
	</form>';
$stdOut .= '<form class="wmo oddRow" action="soap.sql.php" method="get">
		<h3>Add SQL to Schedule' . soapDescription('This method adds a Duration to a SQL Event', ['schedule name', 'table', 'query', 'path', 'duration']) . '</h3>
		<div class="soaptask">Duration is a HashMap unsupported data type</div>
		<input type="hidden" name="method" value="schedSqlAdd"/>
		ScheduledEvent: ' . createSelect($schedEvents, 0) . '
		Table: <input type="text" name="soap[arg1]"/>
		Query: <input type="text" name="soap[arg2]"/>
		Path: <input type="text" name="soap[arg3]"/>
		Duration: <input type="text" name="soap[arg4]"/>
		<input ' . $disabled . 'type="submit" value="Add SQL!"/>
	</form>';
$stdOut .= '<form class="wmo evenRow" action="soap.sql.php" method="get">
		<h3>Remove SQL from Schedule' . soapDescription('This method removes a Duration from a SQL Event', ['schedule name', 'event']) . '</h3>
		<input type="hidden" name="method" value="schedSqlRemove"/>
		ScheduledEvent: ' . createSelect($schedEvents, 0) . '
		Event: ' . createSelect($events, 1) . '
		<input ' . $disabled . 'type="submit" value="Remove SQL!"/>
	</form>';
$stdOut .= '<form class="wmo oddRow" action="soap.sql.php" method="get">
		<h3>Update SQL in Schedule' . soapDescription('This method updates a Duration in a SQL Event', ['schedule name', 'event', 'table', 'query', 'path']) . '</h3>
		<input type="hidden" name="method" value="schedSqlUpdate"/>
		ScheduledEvent: ' . createSelect($schedEvents, 0) . '
		Event: ' . createSelect($events, 1) . '
		Table: <input type="text" name="soap[arg2]"/>
		Query: <input type="text" name="soap[arg3]"/>
		Path: <input type="text" name="soap[arg4]"/>
		<input ' . $disabled . 'type="submit" value="Update SQL!"/>
	</form>';
$stdOut .= '<form class="wmo evenRow" action="soap.sql.php" method="get">
		<h3>Add SQL Duration to Schedule' . soapDescription('This method adds a Duration to a SQL Event', ['schedule name', 'file sql', 'duration']) . '</h3>
		<div class="soaptask">Duration is a HashMap unsupported data type</div>
		<input type="hidden" name="method" value="schedSqlDurationAdd"/>
		ScheduledEvent: ' . createSelect($schedEvents, 0) . '
		Event File SQL: ' . createSelect($eventFileSql, 1) . '
		Duration: <input type="text" name="soap[arg2]"/>
		<input ' . $disabled . 'type="submit" value="Add SQL Duration!"/>
	</form>';
$stdOut .= '<form class="wmo oddRow" action="soap.sql.php" method="get">
		<h3>Remove SQL Duration from Schedule' . soapDescription('This method removes a Duration from a SQL Event', ['schedule name', 'file sql']) . '</h3>
		<input type="hidden" name="method" value="schedSqlDurationRemove"/>
		ScheduledEvent: ' . createSelect($schedEvents, 0) . '
		Event File SQL: ' . createSelect($eventFileSql, 1) . '
		<input ' . $disabled . 'type="submit" value="Remove SQL Duration!"/>
	</form>';
$stdOut .= '<form class="wmo evenRow" action="soap.sql.php" method="get">
		<h3>Update SQL Duration in Schedule' . soapDescription('This method updates a Duration in a SQL Event', ['schedule name', 'file sql', 'duration type', 'duration value']) . '</h3>
		<input type="hidden" name="method" value="schedSqlDurationUpdate"/>
		ScheduledEvent: ' . createSelect($schedEvents, 0) . '
		Event File SQL: ' . createSelect($eventFileSql, 1) . '
		Type: ' . createSelect($durationTypes, 2) . '
		Value: <input type="text" name="soap[arg3]"/>
		<input ' . $disabled . 'type="submit" value="Update SQL Duration!"/>
	</form>';
require_once 'includes/footer.php'; ?>