<?PHP
$stdOut .= '<div id="clear"></div>';
		$stdOut .= '</div><div id="footer">';
			$stdOut .= '<ul><li>Version: ' . $version . '</li></ul>';
			$stdOut .= '<ul><li><a href="credit.php">Credits</a></li><li><a href="terms.php">Terms and Conditions</a></li><li><a href="cookies.php">Cookie Policy</a></li><li><a href="mailto:glen@cogentautomation.co.uk">Contact</a></li><li><a href="sitemap.php">Site Map</a></li></ul></div><div id="notifications">';
			if (!empty($extraNotifications)) {
				$stdOut .= $extraNotifications;
			};
			if (isset($_SESSION['sqlMessage'])) {
				$stdOut .= '<div class="ui-notif-' . $_SESSION['uiState'] . '"><span class="icon-remove-sign"></span>' . $_SESSION['sqlMessage'] . '</div>';
				unset($_SESSION['uiState']);
				unset($_SESSION['sqlMessage']);
			};
			/*if (isset($sqlMessage)) {
				$stdOut .= $sqlMessage;
			};*/
			if (stristr($_SERVER['HTTP_USER_AGENT'], 'msie') || stristr($_SERVER['HTTP_USER_AGENT'], 'trident')) {
				$stdOut .= '<div class="ui-notif-error"><span class="icon-remove-sign"></span>Internet Explorer detected.<br />Some features of Brimweb are incompatible with Internet Explorer, for the best user experiance use <a href="http://www.mozilla.org/">Mozilla Firefox</a> or <a href="http://www.google.com/chrome/">Google Chrome</a></div>';
			};
			if (!isset($_COOKIE['cookieaccept'])) {
				setcookie('cookieaccept', 1, time() + (60 * 60 * 24 * 14), '/');
				$stdOut .= '<div class="cookies ui-notif-error"><span class="icon-remove-sign"></span>Brimweb uses Internet Cookies to store session data. By continuing to use Brimweb you are agreeing to our use of Cookies. <a href="cookies.php">Click Here</a> for more information.</div>';
			};
			$stdOut .= '<noscript><div class="ui-notif-error"><span class="icon-remove-sign"></span>This website is best viewed with JavaScript turned on</div></noscript></div>';
			if (!isset($_SESSION['id'])) {
				$stdOut .= '<form title="Login" id="login-form" action="includes/login.php" method="POST">
				<label for="user">Username:</label>
				<input type="text" name="user" id="user" size="16">
				<label for="pass">Password:</label>
				<input type="password" name="pass" id="pass" size="16">
				<div><input id="login" type="submit" value="Log in"></div>
				<p><a href="createaccount.php">Need an account?</a><br /><a href="resetpassword.php">Forgot your password?</a><br /><a href="requestactivation.php">Request activation e-mail?</a></p>
				</form>';
			};
		//<h2 id="shifter">SHIFT</h2>
		$stdOut .= '<a name="down"></a><h2 id="release">Beta</h2>
		<script type="text/javascript">
		var shifter = $("#shifter");
		var shiftDown = false;
			$(function() {
				$(document).keydown(function(event) {
					if (event.which == 16) {
						if (shiftDown == false) {
							shiftDown = true;
							shifter.addClass("shiftactive");
						};
					};
				}).keyup(function(event) {
					if (event.which == 16) {
						if (shiftDown == true) {
							shiftDown = false;
							shifter.removeClass("shiftactive");
						};
					};
				});
				$("a").removeAttr("title");
				$(".sceditor-button").bind({
					mouseenter: function(e) {
						if (!($(this).hasClass("disabled"))) {
							$("#tooltip").remove();
							var position = [$(this).offset().left - $(window).scrollLeft(), $(this).offset().top - $(window).scrollTop()];
							var mid = [$(this).outerWidth(true) / 2, $(this).outerHeight(true) / 2];
							var midRel = [position[0] + mid[0], position[1] + mid[1]];
							showTooltipEle(midRel[0], midRel[1], $.ucwords($(this).data("sceditor-command")));
						};
					},
					mouseleave: function() {
						$("#tooltip").remove();
					}
				});
			});
		</script>
	</body>
</html>';
if ($hookReplace['help'] == '') {
	$hookReplace['help'] = 'No help available at this time.';
};
if (isset($_POST)) {
	$bugArray['_POST'] = $_POST;
};
if (isset($_GET)) {
	$bugArray['_GET'] = $_GET;
};
if (isset($_SESSION)) {
	$bugArray['_SESSION'] = $_SESSION;
};
if (isset($_COOKIE)) {
	$bugArray['_COOKIE'] = $_COOKIE;
	unset($bugArray['_COOKIE']['PHPSESSID']);
	unset($bugArray['_COOKIE']['id']);
};
$bugArray['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
$hookReplace['bugform'] = '<form id="bug" action="includes/changewmo.php" method="post"><input type="hidden" name="json" value=\'' . json_encode($bugArray) . '\'/>
<input type="hidden" name="script" value=\'' . $scriptProp['id'] . '\'/>
<input type="hidden" name="version" value=\'' . $version . '\'/>
<label for="foreignwmo" >Frontline ID</label><input id="foreignwmo" type="text" name="ForeignWMO"/>
<label for="priority" >Priority<span class="required"> * </span></label><select id="navpriority" name="priority"><option value="none">None</option>';
$queryWmoPri = 'select id, description from wmoprioritycode where typefk = 2 order by description asc';
$dataWmoPri = odbc_exec($conn, $queryWmoPri);
while (odbc_fetch_row($dataWmoPri)) {
	$hookReplace['bugform'] .= '<option value="' . odbc_result($dataWmoPri, 1) . '">' . odbc_result($dataWmoPri, 2) . '</option>';
};
$hookReplace['bugform'] .= '</select>
<label for="comment" >Comment<span class="required"> * </span></label>
<textarea name="comment" id="comment"></textarea>
<div class="bug-notif">
<span class="subscribe-button"><label for="subscribe-nav">Subscribe:</label> <input name="subscribe" checked type="checkbox" id="subscribe-nav"/></span>
Check <span class="icon-question-sign icon-large"></span> before submitting
</div><div class="centersubmit"><input id="navbug" type="submit" name="add" value="Submit!" /></div><div class="requiredhint"><span class="required"> * </span>are required fields.</div>
</form>';
print(str_replace($hookSearch, $hookReplace, $stdOut));
if (isset($_SESSION['id'])) {
	if ($_SESSION['id'] == 1) {
		if (isset($_GET['debug'])) {
			?><pre><?PHP print_r(${$_GET['debug']}); ?></pre><?PHP
		};
	};
};
odbc_close($conn); ?>
