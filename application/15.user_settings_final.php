<?php
namespace MyApplication;

require "../vendor/autoload.php";

use SmartFactory\Interfaces\ILanguageManager;

use function SmartFactory\singleton;
use function SmartFactory\session;
use function SmartFactory\user_settings;
use function SmartFactory\checkempty;
use function SmartFactory\echo_html;
use function SmartFactory\text;
use function SmartFactory\input_text;
use function SmartFactory\checkbox;
use function SmartFactory\select;

session()->startSession();
?><!DOCTYPE html>
<html lang="en">
<head>
<title>User Settings</title>

<link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>User Settings: Summary</h2>

<?php
report_messages();
?>

<p>Dirty (global): <?php echo(user_settings()->isDirty(true)); ?></p>

<h3>General Settings</h3>

<table>
<tr>
  <td>Language*:</td>
  <td><?php echo_html(singleton(ILanguageManager::class)->getLanguageName(user_settings()->getParameter("LANGUAGE"))); ?></td>
</tr>
<tr>
  <td>Time zone*:</td>
  <td><?php echo_html(user_settings()->getParameter("TIME_ZONE")); ?></td>
</tr>
</table>

<h3>Forum Settings</h3>

<table>
<tr>
  <td>Status*:</td>
  <td><?php echo_html(user_settings()->getParameter("STATUS")); ?></td>
</tr>
<tr>
  <td>Signature:</td>
  <td><?php echo_html(user_settings()->getParameter("SIGNATURE")); ?></td>
</tr>
<tr>
  <td>Hide pictures:</td>
  <td><?php echo(user_settings()->getParameter("HIDE_PICTURES") ? "1" : "0"); ?></td>
</tr>
<tr>
  <td>Hide signatures:</td>
  <td><?php echo(user_settings()->getParameter("HIDE_SIGNATURES") ? "1" : "0"); ?></td>
</tr>
</table>

<br>
<br>

<button onclick="document.location.href = '15.user_settings_next.php'">Back</button>

</body>
</html>



