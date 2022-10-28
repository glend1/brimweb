<?PHP $title = 'E-Mail Test';
require_once 'includes/header.php';
if ($_SESSION['id'] != 1) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action.';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$mailMessage = '';
if (isset($_POST['recipient']) && isset($_POST['subject']) && isset($_POST['body'])) {
	$mail = new PHPMailer();
	$mail->isSMTP();
	$mail->SMTPDebug = 2;
	$mail->Debugoutput = function($str, $level) {
		$GLOBALS['mailMessage'] .= $str . '<br />';
	};
	$mail->Host = "172.30.4.15";
	$mail->Port = 25;
	$mail->SMTPAuth = false;
	$mail->setFrom('brimweb@matthey.com', 'Brimweb');
	$mail->addAddress($_POST['recipient']);
	$mail->Subject = $_POST['subject'];
	$mail->msgHTML($_POST['body'], dirname(__FILE__));
	if (!$mail->send()) {
		$_SESSION['sqlMessage'] = 'Email Error: ' . $mail->ErrorInfo;
		$_SESSION['uiState'] = 'active';
	} else {
		$_SESSION['sqlMessage'] = 'Email Sent!';
		$_SESSION['uiState'] = 'active';
	};
};
if ($mailMessage != '') {
	$mailMessage = '<h3>Debug Infomation</h3><div class="mail-message">' . $mailMessage . '</div>';
};
$stdOut .= '<script type="text/javascript">
	$(function() {	
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
</script>' . $mailMessage . '
<form class="subjectbody" action="emailtest.php" method="post">
<label for="recipient"><h3>Recipient<span class="required"> * </span></h3></label>
<input type="text" id="recipient" name="recipient"/>
<label for="subject"><h3>Subject<span class="required"> * </span></h3></label>
<input type="text" id="subject" name="subject"/>
<label for="body"><h3>Body<span class="required"> * </span></h3></label>
<textarea name="body" id="comment"></textarea>
<input type="submit" class="validatetextbutton" value="Send!" /></form><div class="requiredhint"><span class="required"> * </span>are required fields.</div>';
$hookReplace['help'] .= '<a href="#">Sending E-mail</a><div>You are permitted to send an E-Mail to a single address. Debug information will be given to you once an E-Mail sends successfully.</div>';
require_once 'includes/footer.php'; ?>