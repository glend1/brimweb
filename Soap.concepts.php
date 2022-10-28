<?php
$title = 'BrimJava Concepts';
require_once 'includes/header.php';
require_once 'includes/SOAPincludes.php';
$stdOut .= '<div>BrimJava is a Java server application it is responsable for all automation on BrimWeb and consists of 3 types of triggers;
<ul>
	<li>Schedule</li>
	<li>I/O</li>
	<li>SOAP Web Service</li>
</ul>
These triggers can cause 3 types of Events;
<ul>
	<li>E-Mail message</li>
	<li>SMS message</li>
	<li>SQL query printed as a CSV</li>
</ul></div><br />
<div>The I/O is structured, individually each are collections;
<ul>
	<li>Servers: Responsable for fault tolerance between Hosts</li>
	<li>Hosts: The connection to TopServer</li>
	<li>Datas: The Address and associated properties</li>
	<li>Structures: The trigger condition</li>
	<li>Events: The event to trigger</li>
</ul>
The Schedule is structured, individually each are collections;
<ul>
	<li>Schedules: The time an event is triggered</li>
	<li>Events: the event to trigger</li>
</ul>
</div><br />
<div>Access to each of BrimJava\'s methods are provided, with a description, via the other BrimJava/SOAP pages. Each method is a SOAP web service and is usable by any programming language. BrimJava accepts ' . count($args) . ' arguments;
<ul>';
foreach ($args as $key => $value) {
	$stdOut .= '<li>' . ucwords($key) . ': ' . $value . '</li>';
};
$stdOut .= '</ul></div><br />
<div>
	<ul>
	<li><a href="Soap.io.php">Database I/O Methods</a><div class="hinttext">Displays the forms for BrimJava\'s I/O Database management.</div></li>
	<li><a href="Soap.sched.php">Database Scheduler Methods</a><div class="hinttext">Displays the forms for BrimJava\'s Scheduler Database management.</div></li>
	<li><a href="Soap.email.php">Database E-Mail Methods</a><div class="hinttext">Displays the forms for BrimJava\'s E-mail Database management.</div></li>
	<li><a href="Soap.sms.php">Database SMS Methods</a><div class="hinttext">Displays the forms for BrimJava\'s SMS Database management.</div></li>
	<li><a href="Soap.sql.php">Database SQL-to-CSV Methods</a><div class="hinttext">Displays the forms for BrimJava\'s SQL-to-CSV Database management.</div></li>
	<li><a href="Soap.ag.php">Database Address Group Methods</a><div class="hinttext">Displays the forms for BrimJava\'s Address Group Database management.</div></li>
	<li><a href="Soap.raw.php">Raw Methods</a><div class="hinttext">Displays the forms for the base/raw SOAP methods.</div></li>
	</ul>
</div>';
require_once 'includes/footer.php'; ?>