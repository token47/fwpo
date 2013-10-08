<?php
# vim:nowrap:tw=100

$defaulttimeout = '30s';
$defaultservice = 'ssh';

$rulestimeouts = array('10s', '30s', '1m', '5m', '10m', '20m');
$rules = array(
	"ssh"    => "INPUT_TMP -j ACCEPT -p tcp --dport ssh -s %srcip%",
	"imap"   => "INPUT_TMP -j ACCEPT -p tcp --dport imap -s %srcip%",
	"squid"  => "INPUT_TMP -j ACCEPT -p tcp --dport squid -s %srcip%",
	"svn"    => "INPUT_TMP -j ACCEPT -p tcp --dport svn -s %srcip%",
	"ping"   => "INPUT_TMP -j ACCEPT -p icmp -s %srcip%"
);

function print_page($rules, $timeouts, $status, $selectedrule, $selectedtimeout) {
	echo '<html><head><title>F.P.O.</title></head>';
	echo '<body><h3>Firewall Port Opener</h3>';
	echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
	echo 'Select rule: <select name="rulename">';
	foreach($rules as $name => $rule) {
		echo '<option '.($selectedrule==$name? 'selected':'').' value="'.$name.'">'.$name;
	}
	echo '</select>';
	echo ' Time: <select name="timeout">';
	foreach($timeouts as $val) {
		echo '<option '.($selectedtimeout==$val? 'selected':'').' value="'.$val.'">'.$val;
	}
	echo '</select>';
	echo ' <input type="submit" value="Go">';
	echo '</form>';
	if (is_array($status) and count($status) >= 1) {
		foreach($status as $s) { echo '<p>'.$s.'</p>'; }
	} else if ($status != '') echo '<p>'.$status.'</p>';
	echo '</body></html>';
}

function buildcmd($rule, $updown) {
	$rule = preg_replace("/%srcip%/i", $_SERVER['REMOTE_ADDR'], $rule);
	return '/sbin/iptables -'.($updown==0? 'D':'A').' '.$rule;
}

# main function

$status="";
$selectedrule=$defaultservice;
$selectedtimeout=$defaulttimeout;

if (isset($_POST['rulename']) and isset($_POST['timeout'])) {
	$rulename=$_POST['rulename'];
	$timeout=$_POST['timeout'];
	if (isset($rules[$rulename]) and in_array($timeout, $rulestimeouts)) {
		$selectedrule=$rulename;
		$selectedtimeout=$timeout;
		$cmdon='sudo '.buildcmd($rules[$rulename], 1).' >/dev/null 2>&1';
		$cmdoff='(sleep '.$timeout.'; sudo '.
			buildcmd($rules[$rulename], 0).') >/dev/null 2>&1 &';
		exec($cmdon, $placehold, $reton);
		if ($reton == 0) exec($cmdoff, $placehold, $retoff);
		else $retoff='na';
		$date=exec("date +'%Y/%m/%d %T'");
		$status="$date Rule <b>$rulename</b> executed ($reton/$retoff).";
	} else {
		$status="ERROR: invalid use ($rulename/$timeout)";
	}
}

print_page($rules, $rulestimeouts, $status, $selectedrule, $selectedtimeout);

?>
