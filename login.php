<?php
session_start();

include 'config.inc.php';
include 'header.php';
include 'topmain.php';
echo "<title>$title - Admin Login</title>\n";

$self = $_SERVER['PHP_SELF'];

if (isset($_POST['login_userid']) && (isset($_POST['login_password']))) {
    $login_userid = $_POST['login_userid'];

    $query = "select empfullname, employee_passwd, admin, time_admin from ".$db_prefix."employees
              where empfullname = '".$login_userid."'";
    $result = mysql_query($query);

    while ($row=mysql_fetch_array($result)) {

        $admin_username = $row['empfullname'];
        $admin_password = $row['employee_passwd'];
        $admin_auth = $row['admin'];
        $time_admin_auth = $row['time_admin'];
    }  
	if (preg_match('/^xy/',$admin_password)) {
		$db_salt = 'xy';
	} else $db_salt = $admin_password;
	$login_password = crypt($_POST['login_password'], $db_salt);
	
	if ((strtolower($login_userid) == strtolower(@$admin_username)) && ($login_password == @$admin_password) && ($admin_auth == "1")) {
        $_SESSION['valid_user'] = $login_userid;
		#check for old password key and upgrade the hash if needed
		if($db_salt == 'xy') {
			if (defined("CRYPT_BLOWFISH") && CRYPT_BLOWFISH) {
			$salt_chars = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','0','1','2','3','4','5','6','7','8','9','.','/');
			$salt_chars_length = count($salt_chars) - 1;
			$cost = sprintf("%02s", rand(4,17)); #between 04 and 31...too spendy above 17
			$salt = '$2y$'.$cost.'$';
			#loop through and generate a random 22 char salt using all the characters bcrypt supports for the salt
			for ($counter=1;$counter<=22;$counter++){
					$key = rand(0,$salt_chars_length);
					$salt .= $salt_chars[$key];
			}
			$password = crypt($_POST['login_password'], $salt);
			$query = "update ".$db_prefix."employees set employee_passwd = ('".$password."') where empfullname = ('".$admin_username."')";
			$result = mysql_query($query);
			} else die ('Blowfish algorithm not present');
		}
    }

    elseif (($login_userid == @$admin_username) && ($login_password == @$admin_password) && ($time_admin_auth == "1")) {
        $_SESSION['time_admin_valid_user'] = $login_userid;
    }

}

if (isset($_SESSION['valid_user'])) {
    echo "<script type='text/javascript' language='javascript'> window.location.href = 'admin/index.php';</script>";
    exit;
}

elseif (isset($_SESSION['time_admin_valid_user'])) {
    echo "<script type='text/javascript' language='javascript'> window.location.href = 'admin/timeadmin.php';</script>";
    exit;

} else {

    // build form

    echo "<form name='auth' method='post' action='$self'>\n";
    echo "<table align=center width=210 border=0 cellpadding=7 cellspacing=1>\n";
    echo "  <tr class=right_main_text><td colspan=2 height=35 align=center valign=top class=title_underline>PHP Timeclock Admin Login</td></tr>\n";
    echo "  <tr class=right_main_text><td align=left>Username:</td><td align=right><input type='text' name='login_userid'></td></tr>\n";
    echo "  <tr class=right_main_text><td align=left>Password:</td><td align=right><input type='password' name='login_password'></td></tr>\n";
    echo "  <tr class=right_main_text><td align=center colspan=2><input type='submit' onClick='admin.php' value='Log In'></td></tr>\n";

    if (isset($login_userid)) {
        echo "  <tr class=right_main_text><td align=center colspan=2>Could not log you in. Either your username or password is incorrect.</td></tr>\n";
    }

    echo "</table>\n";
    echo "</form>\n";
    echo "<script language=\"javascript\">document.forms['auth'].login_userid.focus();</script>\n";
}

echo "</body>\n";
echo "</html>\n";
?>
