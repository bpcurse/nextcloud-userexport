<?php

  // Set active navigation item
  $active_page = "index";

  require_once 'functions.php';
  include 'config.php';

  session_secure_start();

  // Perform logout steps if selected through GET parameter (click on 'Logout' in navigation)
  if($_GET['logout'])
    logout();

  /**
    * Get parameters if any, else set defaults
    */
  $target_url = filter_input(INPUT_GET, 'url', FILTER_SANITIZE_URL)
    ?? $target_url;
  $user_name = $_GET['user'] ?? $user_name;
  $user_pass = $_GET['pass'] ?? $user_pass;

  // Set UI language to config value or to english (en), if it is not configured
  $_SESSION['language'] = $language ?? 'en';

  // Include language file
  require_once 'l10n/'.$_SESSION['language'].'.php';

  // Check access_token if set and supplied
  if($access_token) {

    if(!$_SESSION['access_token_provided'])
      $_SESSION['access_token_provided'] = $_GET['access_token'];

    if($_SESSION['access_token_provided'] !== $access_token) {
      sleep(1); // Primitive pseudo brute-force protection
      unset($_SESSION['access_token_provided']);
      exit('ERROR: Authentication failed, wrong access token supplied.
          <br><br>Token needs to be supplied through GET parameter e.g. https://export.cloud.example.com?access_token=tokengoeshere and is set in config.php
          <br>(This has nothing to do with your Nextcloud user credentials)');
    }

  }

  /**
    * Check if data choices have been submitted (GET parameter 'select'),
    * if yes set $_SESSION variable to GET values, else set defaults
    */
  $_SESSION['data_choices'] = isset($_GET["select"])
    ? explode(",", $_GET["select"])
    : ['id', 'displayname', 'email', 'lastLogin'];
  // Check if export type has been set (GET parameter 'type'), else default to 'table'
  $_SESSION['export_type'] = $_GET['type'] ?? 'table';
  // Check if message mode has been set (GET parameter 'msg_mode'), else default to 'bcc'
  $_SESSION['message_mode'] = $_GET['msg_mode'] ?? 'bcc';

  // Populate session array 'data_options' with all data options that can be selected
  set_data_options();

  if($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Transfer $_POST values to $_SESSION variables for further use
    $_SESSION['user_name'] = $_POST['user_name'];
    $_SESSION['user_pass'] = $_POST['user_pass'];

    // Save the script's start timestamp to measure execution time
    $_SESSION['timestamp_script_start'] = microtime(true);

    /**
    * Check if plain HTTP is used without override command and exit if not
    * add 'https://' if no protocol specified and set $_SESSION variable for further use
    */
    $_SESSION['target_url'] = check_https($_POST['target_url']);

    // cURL API call fetching userlist (containing only user IDs) from target server
    fetch_userlist();
    // cURL API call fetching grouplist (containing only group names) from target server
    fetch_grouplist();
    // cURL API call fetching groupfolder data from target server
    fetch_raw_groupfolders_data();

    // Count list items and save them as session variables
    $_SESSION['user_count'] = count($_SESSION['userlist']);
    $_SESSION['group_count'] = count($_SESSION['grouplist']);

    /**
    * Count list items and save them as session variable
    * (if groupfolders app is active and at least one groupfolder exists)
    */
    $_SESSION['groupfolders_count'] =
        $_SESSION['groupfolders_active'] == true
        ? count($_SESSION['raw_groupfolders_data']['ocs']['data'])
        : null;

    // Fetch all user details (this can take a long time)
    $_SESSION['raw_user_data'] = fetch_raw_user_data();

    // Calculate how much disk space is assigned, used and available in total
    calculate_quota();

  }

  set_security_headers();

  // Tell the browser which language is used
  echo "<html lang='{$_SESSION['language']}'>";

?>

  <head>
    <link rel="stylesheet" type="text/css" href="style.php">
    <meta charset="UTF-8">
    <title>Nextcloud Userexport</title>

    <script>
      function showStartInfo() {
        document.getElementById("submitted").innerHTML = "<br><?php echo L10N_WAIT ?>";
      }

      function setFocus() {
        var targetUrl = "<?php if($target_url){echo true;} ?>";
        var userName = "<?php if($user_name){echo true;} ?>";
        var userPass = "<?php if($user_pass){echo true;} ?>";

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('pass') || userPass) {
          document.getElementById('button-connect').focus();
        }
        else if (urlParams.get('user') || userName) {
          document.getElementById('user_pass').focus();
        }
        else if (urlParams.get('url') || targetUrl) {
          document.getElementById('user_name').focus();
        }
        else {
          document.getElementById('url').focus();
        }
      }
    </script>

  </head>

  <body onload='setFocus()'>
    <?php
      include 'navigation.php';
      if($_SERVER['REQUEST_METHOD'] == 'POST') {
        print_status_success();
        exit();
      }
    ?>
    <div style='width: 305px;'>
    <form method='post' id='auth-form' onsubmit='showStartInfo()'>
      <br>
      <u><?php echo L10N_SERVER_AND_LOGIN_DATA ?></u>
      <br><br>
      <table>
        <tr>
        <td colspan='2'>
          <input style='width: 100%;' id='url' type='text' name='target_url'
          required placeholder='https://cloud.example.com'
          value='<?php echo $target_url; ?>'>
        </td></tr>
        <tr>
        <td><input style='width: 100%;' id='user_name' type='text'
          name='user_name' required placeholder='<?php echo L10N_USERNAME ?>'
          value='<?php echo $user_name; ?>'>
        </td>
        <td><input style='width: 100%;' id='user_pass' type='password'
          name='user_pass' required placeholder='<?php echo L10N_PASSWORD ?>'
          value='<?php echo $user_pass; ?>'>
        </td>
        <?php if($access_token && !$_SESSION['access_token_provided'])
        echo "</tr>
          <tr><td colspan='2' style='padding-top: 0.5em;'>
            <input style='width: 100%;' id='access_token' type='password'
            name='access_token' required placeholder='".L10N_ACCESS_TOKEN."'>
          </td>";
        ?>
        </tr>
      </table>
      <br>
      <input id='button-connect' value='<?php echo L10N_CONNECT_AND_FETCH ?>'
          type='submit' name='submit'>
      <div id='submitted' style="text-align: center; color: grey;"></div>
    </form>
    </div>
  </body>
</html>
