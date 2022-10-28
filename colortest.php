<?PHP 
$title = 'Color Test';
require_once 'includes/header.php';
if (!fCanSee(isset($_SESSION['id']))) {
	$_SESSION['sqlMessage'] = 'You must be logged in to use this page!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
if ($_SESSION['id'] != 1) {
	$_SESSION['sqlMessage'] = 'You do not have permission to perform this action!';
	$_SESSION['uiState'] = 'error';
	fRedirect();
};
$stdOut .= '
<table style="width:100%;text-align:center;">
<tr>
	<td style="background-color:#fff4e6;color:#000000;">#fff4e6</td>
	<td style="background-color:#f1ffe6;color:#000000;">#f1ffe6</td>
	<td style="background-color:#e6fff4;color:#000000;">#e6fff4</td>
	<td style="background-color:#e6f1ff;color:#000000;">#e6f1ff</td>
	<td style="background-color:#f4e6ff;color:#000000;">#f4e6ff</td>
	<td style="background-color:#ffe6f1;color:#000000;">#ffe6f1</td>
</tr>
<tr>
	<td style="background-color:#ffeacc;color:#1a1a1a;">#ffeacc</td>
	<td style="background-color:#e1ffcc;color:#1a1a1a;">#e1ffcc</td>
	<td style="background-color:#ccffea;color:#1a1a1a;">#ccffea</td>
	<td style="background-color:#cce1ff;color:#1a1a1a;">#cce1ff</td>
	<td style="background-color:#eaccff;color:#1a1a1a;">#eaccff</td>
	<td style="background-color:#ffcce1;color:#1a1a1a;">#ffcce1</td>
</tr>
<tr>
	<td style="background-color:#ffdfb2;color:#262626;">#ffdfb2</td>
	<td style="background-color:#d2ffb2;color:#262626;">#d2ffb2</td>
	<td style="background-color:#b2ffdf;color:#262626;">#b2ffdf</td>
	<td style="background-color:#b2d2ff;color:#262626;">#b2d2ff</td>
	<td style="background-color:#dfb2ff;color:#262626;">#dfb2ff</td>
	<td style="background-color:#ffb2d2;color:#262626;">#ffb2d2</td>
</tr>
<tr>
	<td style="background-color:#ffd599;color:#333333;">#ffd599</td>
	<td style="background-color:#c3ff99;color:#333333;">#c3ff99</td>
	<td style="background-color:#99ffd5;color:#333333;">#99ffd5</td>
	<td style="background-color:#99c3ff;color:#333333;">#99c3ff</td>
	<td style="background-color:#d599ff;color:#333333;">#d599ff</td>
	<td style="background-color:#ff99c3;color:#333333;">#ff99c3</td>
</tr>
<tr>
	<td style="background-color:#ffcb80;color:#404040;">#ffcb80</td>
	<td style="background-color:#b4ff80;color:#404040;">#b4ff80</td>
	<td style="background-color:#80ffcb;color:#404040;">#80ffcb</td>
	<td style="background-color:#80b4ff;color:#404040;">#80b4ff</td>
	<td style="background-color:#cb80ff;color:#404040;">#cb80ff</td>
	<td style="background-color:#ff80b4;color:#404040;">#ff80b4</td>
</tr>
<tr>
	<td style="background-color:#ffc066;color:#4d4d4d;">#ffc066</td>
	<td style="background-color:#a5ff66;color:#4d4d4d;">#a5ff66</td>
	<td style="background-color:#66ffc0;color:#4d4d4d;">#66ffc0</td>
	<td style="background-color:#66a5ff;color:#4d4d4d;">#66a5ff</td>
	<td style="background-color:#c066ff;color:#4d4d4d;">#c066ff</td>
	<td style="background-color:#ff66a5;color:#4d4d4d;">#ff66a5</td>
</tr>
<tr>
	<td style="background-color:#ffb54c;color:#595959;">#ffb54c</td>
	<td style="background-color:#95ff4c;color:#595959;">#95ff4c</td>
	<td style="background-color:#4cffb5;color:#595959;">#4cffb5</td>
	<td style="background-color:#4c96ff;color:#595959;">#4c96ff</td>
	<td style="background-color:#b54cff;color:#595959;">#b54cff</td>
	<td style="background-color:#ff4c95;color:#595959;">#ff4c95</td>
</tr>
<tr>
	<td style="background-color:#ffab33;color:#666666;">#ffab33</td>
	<td style="background-color:#87ff33;color:#666666;">#87ff33</td>
	<td style="background-color:#33ffab;color:#666666;">#33ffab</td>
	<td style="background-color:#3387ff;color:#666666;">#3387ff</td>
	<td style="background-color:#ab33ff;color:#666666;">#ab33ff</td>
	<td style="background-color:#ff3387;color:#666666;">#ff3387</td>
</tr>
<tr>
	<td style="background-color:#ffa01a;color:#737373;">#ffa01a</td>
	<td style="background-color:#78ff1a;color:#737373;">#78ff1a</td>
	<td style="background-color:#1affa0;color:#737373;">#1affa0</td>
	<td style="background-color:#1a78ff;color:#737373;">#1a78ff</td>
	<td style="background-color:#a01aff;color:#737373;">#a01aff</td>
	<td style="background-color:#ff1a78;color:#737373;">#ff1a78</td>
</tr>
<tr>
	<td style="background-color:#ff9400;color:#808080;">#ff9400</td>
	<td style="background-color:#6aff00;color:#808080;">#6aff00</td>
	<td style="background-color:#00ff94;color:#808080;">#00ff94</td>
	<td style="background-color:#006aff;color:#808080;">#006aff</td>
	<td style="background-color:#9400ff;color:#808080;">#9400ff</td>
	<td style="background-color:#ff006a;color:#808080;">#ff006a</td>
</tr>
<tr>
	<td style="background-color:#e58500;color:#8c8c8c;">#e58500</td>
	<td style="background-color:#5fe500;color:#8c8c8c;">#5fe500</td>
	<td style="background-color:#00e585;color:#8c8c8c;">#00e585</td>
	<td style="background-color:#005fe5;color:#8c8c8c;">#005fe5</td>
	<td style="background-color:#8500e5;color:#8c8c8c;">#8500e5</td>
	<td style="background-color:#e5005f;color:#8c8c8c;">#e5005f</td>
</tr>
<tr>
	<td style="background-color:#cc7800;color:#999999;">#cc7800</td>
	<td style="background-color:#54cc00;color:#999999;">#54cc00</td>
	<td style="background-color:#00cc78;color:#999999;">#00cc78</td>
	<td style="background-color:#0054cc;color:#999999;">#0054cc</td>
	<td style="background-color:#7800cc;color:#999999;">#7800cc</td>
	<td style="background-color:#cc0054;color:#999999;">#cc0054</td>
</tr>
<tr>
	<td style="background-color:#b36900;color:#a6a6a6;">#b36900</td>
	<td style="background-color:#49b300;color:#a6a6a6;">#49b300</td>
	<td style="background-color:#00b369;color:#a6a6a6;">#00b369</td>
	<td style="background-color:#004ab3;color:#a6a6a6;">#004ab3</td>
	<td style="background-color:#6900b3;color:#a6a6a6;">#6900b3</td>
	<td style="background-color:#b30049;color:#a6a6a6;">#b30049</td>
</tr>
<tr>
	<td style="background-color:#995a00;color:#b3b3b3;">#995a00</td>
	<td style="background-color:#3f9900;color:#b3b3b3;">#3f9900</td>
	<td style="background-color:#00995a;color:#b3b3b3;">#00995a</td>
	<td style="background-color:#003f99;color:#b3b3b3;">#003f99</td>
	<td style="background-color:#5a0099;color:#b3b3b3;">#5a0099</td>
	<td style="background-color:#99003f;color:#b3b3b3;">#99003f</td>
</tr>
<tr>
	<td style="background-color:#804b00;color:#bfbfbf;">#804b00</td>
	<td style="background-color:#348000;color:#bfbfbf;">#348000</td>
	<td style="background-color:#00804b;color:#bfbfbf;">#00804b</td>
	<td style="background-color:#003480;color:#bfbfbf;">#003480</td>
	<td style="background-color:#4b0080;color:#bfbfbf;">#4b0080</td>
	<td style="background-color:#800034;color:#bfbfbf;">#800034</td>
</tr>
<tr>
	<td style="background-color:#663c00;color:#cccccc;">#663c00</td>
	<td style="background-color:#2a6600;color:#cccccc;">#2a6600</td>
	<td style="background-color:#00663c;color:#cccccc;">#00663c</td>
	<td style="background-color:#002a66;color:#cccccc;">#002a66</td>
	<td style="background-color:#3c0066;color:#cccccc;">#3c0066</td>
	<td style="background-color:#66002a;color:#cccccc;">#66002a</td>
</tr>
<tr>
	<td style="background-color:#4d2d00;color:#d9d9d9;">#4d2d00</td>
	<td style="background-color:#204d00;color:#d9d9d9;">#204d00</td>
	<td style="background-color:#004d2d;color:#d9d9d9;">#004d2d</td>
	<td style="background-color:#00204d;color:#d9d9d9;">#00204d</td>
	<td style="background-color:#2d004d;color:#d9d9d9;">#2d004d</td>
	<td style="background-color:#4d0020;color:#d9d9d9;">#4d0020</td>
</tr>
<tr>
	<td style="background-color:#331e00;color:#e5e5e5;">#331e00</td>
	<td style="background-color:#153300;color:#e5e5e5;">#153300</td>
	<td style="background-color:#00331e;color:#e5e5e5;">#00331e</td>
	<td style="background-color:#001533;color:#e5e5e5;">#001533</td>
	<td style="background-color:#1e0033;color:#e5e5e5;">#1e0033</td>
	<td style="background-color:#330015;color:#e5e5e5;">#330015</td>
</tr>
<tr>
	<td style="background-color:#1a1000;color:#f2f2f2;">#1a1000</td>
	<td style="background-color:#0a1a00;color:#f2f2f2;">#0a1a00</td>
	<td style="background-color:#001a10;color:#f2f2f2;">#001a10</td>
	<td style="background-color:#000a1a;color:#f2f2f2;">#000a1a</td>
	<td style="background-color:#10001a;color:#f2f2f2;">#10001a</td>
	<td style="background-color:#1a000a;color:#f2f2f2;">#1a000a</td>
</tr>
</table>';
/*$style = [20,35,36,'var'];
foreach ($style as $val) {
	$stdOut .= '<div class="stylez style' . $val . '">
	<input type="radio" id="radio' . $val . '" value="test"></input><label for="radio' . $val . '">test</label><br /><br />
	<input type="checkbox" id="checkbox' . $val . '" value="test"></input><label for="checkbox' . $val . '">test</label><br /><br />
	<input type="text" value="test"></input><br /><br />
	<input type="button" value="test"></input><br /><br />
	<input type="reset" value="test"></input><br /><br/>
	<select><option>one</option><option>two</option><option>three</option><option>four</option><option>five</option></select>
	</div>';
};*/
$stdOut .= '<blockquote><cite>The wise old man</cite>The quick brown fox jumped over the lazy dog</blockquote>
<code>The quick brown fox jumped over the lazy dog</code>';
for ($i = 1; $i <= (9 * 6); $i++) {
	$stdOut .= '<div class="style style' . $i . '"><span class="style1">The quick brown fox jumped</span> <span class="style2">over the lazy dog.</span><br />style' . $i . '</div>';
};
require_once 'includes/footer.php'; ?>