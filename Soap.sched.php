<?php
$title = 'Database Scheduler Methods';
require_once 'includes/header.php';
require_once 'includes/SOAPincludes.php';
$schedEvents = getData("j_schedevent", ["key" => "name"]);
$stdOut .= '<form class="wmo oddRow" action="soap.sched.php" method="get">
		<h3>Add Schedule' . soapDescription('This method creates a Schedule', ['schedule name', 'start time', 'interval']) . '</h3>
		<div class="soaptask">This lacks time-long conversion for Start Time. Interval lacks required features.</div>
		<input type="hidden" name="method" value="loadSchedAdd"/>
		Name: <input type="text" name="soap[arg0]"/>
		Start Time: <input type="text" name="soap[arg1]"/>
		Interval: <input type="text" name="soap[arg2]"/>
		<input ' . $disabled . 'type="submit" value="Add Schedule!"/>
	</form>';
$stdOut .= '<form class="wmo evenRow" action="soap.sched.php" method="get">
		<h3>Remove Schedule' . soapDescription('This method removes a Scheduled trigger and cleans up after itself', ['schedule name']) . '</h3>
		<input type="hidden" name="method" value="loadSchedRemove"/>
		Name: ' . createSelect($schedEvents, 0) . '
		<input ' . $disabled . 'type="submit" value="Remove Schedule!"/>
	</form>';
$stdOut .= '<form class="wmo oddRow" action="soap.sched.php" method="get">
		<h3>Update Schedule' . soapDescription('This method updates an existing Scheduled Trigger', ['schedule name', 'schedule name', 'start time', 'interval']) . '</h3>
		<div class="soaptask">This lacks time-long conversion for Start Time. Interval lacks required features.</div>
		<input type="hidden" name="method" value="loadSchedUpdate"/>
		Old Name: ' . createSelect($schedEvents, 0) . '
		New Name: <input type="text" name="soap[arg1]"/>
		Start Time: <input type="text" name="soap[arg2]"/>
		Interval: <input type="text" name="soap[arg3]"/>
		<input ' . $disabled . 'type="submit" value="Update Schedule!"/>
	</form>';
require_once 'includes/footer.php'; ?>