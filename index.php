<?php

  session_start();
  $active_page = "index";
  require 'functions.php';
  include 'config.php';

  // Set UI language to config value or to english, if it is not configured
  $_SESSION['language'] = $language ?? 'en';
  require 'l10n/' . $_SESSION['language'] . '.php';

  /**
  * Get parameters if any, set defaults
  */
  $target_url = $_GET['url']
    ?? filter_input(INPUT_GET, 'url', FILTER_SANITIZE_URL);
  $user_name = $_GET['user'];
  $user_pass = $_GET['pass'];
  $_SESSION['data_choices'] = isset($_GET["select"])
    ? explode(",", $_GET["select"])
    : ['id', 'displayname', 'email', 'lastLogin'];
  $_SESSION['export_type'] = $_GET['type'] ?? 'table';
  $_SESSION['message_mode'] = $_GET['msg_mode'] ?? 'bcc';
  set_data_options();

  echo '<html lang="' . $_SESSION['language'] . '">'

?>

  <head>
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>Nextcloud user export</title>
  </head>

  <body>
    <?php include ("navigation.php"); ?>
    <form method='post' id='auth_form'>
      <font face='Helvetica'>
      <br>
      <u><?php echo L10N_SERVER_AND_LOGIN_DATA ?></u>
      <br><br>
      <table>
        <tr><td style="text-align: right;">
          <label for='url'><?php echo L10N_SERVER ?></label>
        </td>
        <td colspan="3">
          <input id='url' type='text' name='target_url' size='32' required
          placeholder='https://cloud.example.com'
          value='<?php echo $target_url; ?>'>
        </td></tr>
        <tr><td><label for='user'><?php echo L10N_USERNAME ?></label></td>
        <td><input id='user' type='text' name='user_name' size='10' required
          placeholder='<?php echo L10N_USERNAME ?>'
          value='<?php echo $user_name; ?>'>
        </td>
        <td><label for='pass'><?php echo L10N_PASSWORD ?></label></td>
        <td><input id='pass' type='password' name='user_pass' size='10' required
          placeholder='<?php echo L10N_PASSWORD ?>'
          value='<?php echo $user_pass; ?>'>
        </td>
        </tr>
      </table>
      <br>
      <table>
      <tr><td>
        <input id='button-blue' value='<?php echo L10N_CONNECT_AND_FETCH ?>'
          type='submit' name='submit'>
      </td></tr>
      <tr><td style="text-align: center; font-size: small; color: grey;">
        <?php echo L10N_WAIT ?>
      </td></tr>
      </table>
      </font>
    </form>
    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      // Set SESSION variables to POST values
      if (isset($_POST['target_url'])) {
        $_SESSION['user_name'] = $_POST['user_name'];
        $_SESSION['user_pass'] = $_POST['user_pass'];

        // Save the script's start timestamp to measure execution time
        define('TIMESTAMP_SCRIPT_START', microtime(true));

        // Check if plain HTTP is used without override command and exit if not
        $_SESSION['target_url'] = check_https($_POST['target_url']);

        // Fast cURL API call fetching userlist (containing only user IDs) from target server
        $_SESSION['userlist'] = fetch_userlist();
        // Fast cURL API call fetching grouplist (containing only group names) from target server
        $_SESSION['grouplist'] = fetch_grouplist();

        // Count the list items and save them as session variable
        $_SESSION['usercount'] = count($_SESSION['userlist']);
        $_SESSION['groupcount'] = count($_SESSION['grouplist']);
      }

      // Fetch all user details (this can take a long time)
      $_SESSION['raw_user_data'] = fetch_raw_user_data();

      print_status_success();
    }
    ?>
  </body>
</html>
