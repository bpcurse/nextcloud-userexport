<?php

  session_start();
  $active_page = "index";
  require 'functions.php';
  include 'config.php';

  // Get parameters if any
  if (isset($_GET['url'])) {
    $target_url = $_GET['url'];
  }
  if (isset($_GET['user'])) {
    $user_name = $_GET['user'];
  }
  if (isset($_GET['pass'])) {
    $user_pass = $_GET['pass'];
  }
  if (isset($_GET['type'])) {
    $_SESSION['export_type'] = $_GET['type'];
  }
  if (isset($_GET['msg_mode'])) {
    $_SESSION['message_mode'] = $_GET['msg_mode'];
  }
  if (isset($_GET['select'])) {
    $_SESSION['data_choices'] = explode(",", $_GET["select"]);
  }
  if ($_SESSION['data_choices'] === null) {
    $_SESSION['data_choices'] = ['id', 'displayname', 'email', 'lastLogin'];
  }
  $_SESSION['data_options'] = [
    'id' => 'User ID', 'displayname' => 'Displayname', 'email' => 'Email',
    'lastLogin' => 'Last Login', 'backend' => 'Backend', 'enabled' => 'Enabled',
    'quota' => 'Quota limit', 'used' => 'Quota used', 'free' => 'Quota free',
    'groups' => 'Groups', 'subadmin' => 'Subadmin', 'language' => 'Language',
    'locale' => 'Locale'];

?>

<html lang="en">
  <head>
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>Nextcloud user export</title>
  </head>

  <body>
    <?php include ("navigation.php"); ?>
    <form method='post' id='auth_form'>
      <font face='Helvetica'>
      <br>
      <u>URL and login data:</u>
      <br><br>
      <table>
        <tr><td><label for='url'>Target URL</label></td>
        <td colspan="3"><input id='url' type='text' name='target_url' size='36'
          placeholder='https://cloud.example.com'
          value='<?php echo $target_url; ?>'>
        </td></tr>
        <tr><td><label for='user'>Username</label></td>
        <td><input id='user' type='text' name='user_name' size='10'
          placeholder='username' value='<?php echo $user_name; ?>'></td>
        <td><label for='pass'>Password</label></td>
        <td><input id='pass' type='password' name='user_pass' size='10'
          placeholder='password' value='<?php echo $user_pass; ?>'></td>
        </tr>
      </table>
      <br>
      <input style="background-color: #4c6489; color: white; height: 45px"
        value='Connect and fetch data from server (this may take minutes!)'
        type='submit' name='submit'>
      </font>
    </form>
    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      // Set SESSION variables to POST values
      if (isset($_POST['target_url'])) {
        $_SESSION['user_name'] = $_POST['user_name'];
        $_SESSION['user_pass'] = $_POST['user_pass'];

        // Check if plain HTTP is used without override command and exit if not
        $_SESSION['target_url'] = check_https($_POST['target_url']);

        // Fast cURL API call fetching the userlist (containing only user IDs) from target server
        $_SESSION['userlist'] = fetch_userlist();
        // Fetch grouplist from target server
        $_SESSION['grouplist'] = fetch_grouplist();
      }

      // Fetch all user details (this can take a long time)
      $_SESSION['raw_user_data'] = fetch_raw_user_data();

      print_status_message();
    }
    ?>
  </body>
</html>
