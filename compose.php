<?PHP 
$title = 'Send Mail';
require_once 'includes/header.php';
if (!fCanSee(isset($_SESSION['id']))) {
	$_SESSION['sqlMessage'] = 'You must be logged in to use this page!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if (isset($_GET['id'])) {
	if ($_GET['id'] != '') {
		$queryGet = 'select subject, message, tfrom.name, fromid, tto.name, toid
		from message
		left join users as tfrom on tfrom.ID = fromid
		left join users as tto on tto.ID = toid
		where message.id = ' . $_GET['id'];
		$dataGet = odbc_exec($conn, $queryGet);
		if (odbc_fetch_row($dataGet)) {
			$subject = odbc_result($dataGet, 1);
			$message = odbc_result($dataGet, 2);
			$from = odbc_result($dataGet, 3);
			$fromid = odbc_result($dataGet, 4);
			$to = odbc_result($dataGet, 5);
			$toid = odbc_result($dataGet, 6);
			if (!($_SESSION['id'] == $fromid || $_SESSION['id'] == $toid)) {
				$_SESSION['sqlMessage'] = 'This is not your mail!';
				$_SESSION['uiState'] = 'error';
				fRedirect();
			};
		};
	};
};
if (isset($message)) {
	$message = '<br /><br /><blockquote><cite>' . $from . ' Said</cite>' . $message . '</blockquote>';
};
if (isset($_GET['type'])) {
	switch ($_GET['type']) {
		case 'forward':
			unset($to);
			if (isset($subject)) {
				if (substr($subject, 0, 4) != 'FWD:') {
					$subject = 'FWD: ' . $subject;
				};
			};
			break;
		case 'reply':
			if (isset($subject)) {
				if (substr($subject, 0, 3) != 'RE:') {
					$subject = 'RE: ' . $subject;
				};
			};
			break;
	};
};
if (isset($_GET['to'])) {
	$to = $_GET['to'];
};
$hookReplace['contexticon'] = '<a href="#" data-text="Context Sensitive Menu"  class="menucontext"><span class="icon-briefcase icon-hover-hint icon-large"></span></a>';
$stdOut .= fMailNav('compose') . '<script type="text/javascript">
	$(function() {
	
		function split( val ) {
			return val.split( /,\s*/ );
		}
		function extractLast( term ) {
			return split( term ).pop();
		}
		$( "#recipient" ).autocomplete({
			source: function( request, response ) {
				$.getJSON( "includes/ajax.usernames.php", {
					search: extractLast( request.term )
				}, response );
			},
			search: function() {
			// custom minLength
				var term = extractLast( this.value );
				if ( term.length < 2 ) {
					return false;
				}
			},
			focus: function() {
			// prevent value inserted on focus
				return false;
			},
			select: function( event, ui ) {
				var terms = split( this.value );
				// remove the current input
				terms.pop();
				// add the selected item
				terms.push( ui.item.value );
				// add placeholder to get the comma-and-space at the end
				terms.push( "" );
				this.value = terms.join( ", " );
				return false;
			}
		});
	
		$(".validatetextbutton").click(function() {
			var bValid = true;
			$(".ui-state-error").removeClass( "ui-state-error" );
			bValid = bValid && checkLength( $("#recipient"), "recipient", 1, 255 );
			bValid = bValid && checkLength( $("#subject"), "title", 1, 255 );
			if ($("#bodybody").find("iframe").contents().find("body").text() == "") {
				bValid = false;
				updateTips("The body must contain text");
				$("#bodybody").addClass( "ui-state-error" );
			};
			if (bValid == false) {
				return false;
			};
		});
	});
</script>
<form class="subjectbody" action="includes/processmail.php" method="post">';
if (isset($_GET['id'])) {
	$stdOut .= '<input type="hidden" name="id" value="' . $_GET['id'] . '" />';
};
$stdOut .= '<label for="recipient"><h3>Recipient(s)<span class="required"> * </span></h3></label>
<input type="text" id="recipient" name="recipient"'; 
if (isset($to)) {
	$stdOut .= ' value="' . $to . ', " ';
};
$stdOut .= '/>';
$stdOut .= '<label for="subject"><h3>Subject<span class="required"> * </span></h3></label>
<input type="text" id="subject" name="subject"'; 
if (isset($_GET['id'])) {
	if ($_GET['id'] != '') {
		$stdOut .= ' value="' . $subject . '" ';
	};
};
$stdOut .= '/>
<label for="body"><h3>Body<span class="required"> * </span></h3></label>
<textarea name="body" id="comment">';
if (isset($_GET['id'])) {
	if ($_GET['id'] != '') {
		$stdOut .= $message;
	};
};
$stdOut .= '</textarea>
<input type="submit" class="validatetextbutton" value="Send!" /></form><div class="requiredhint"><span class="required"> * </span>are required fields.</div>';
$hookReplace['help'] .= $helptext['mailcontext'] . '<a href="#">Recipient Field</a><div>As you type the system will attempt to guess the intended recipient and give appropriate suggestions. Once a user has been selected you are given the option to send mail to multiple users, this can be achieved by seperating each username with ",". Note the message will appear in their Brimweb inbox, not their E-Mail inbox.</div>';
require_once 'includes/footer.php'; ?>