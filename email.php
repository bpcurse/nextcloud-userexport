<?php

  session_start();
  $active_page = 'email';
  require_once 'functions.php';
  include_once 'config.php';
  require_once 'l10n/'.$_SESSION['language'].'.php';

  if(!$_SESSION['authenticated'])
    exit('<br>'.L10N_CONNECTION_NEEDED);

  if($_POST['submit']) {
    $_SESSION['message_mode'] = $_POST['message_mode'] ?? $_SESSION['message_mode'];
    filter_email();
  }

?>

  <head>
    <link rel="stylesheet" type="text/css" href="style.php">
    <title>Nextcloud Userexport</title>
  </head>

  <body>
    <?php

      include 'navigation.php';

      $_SESSION['message_mode'] = $_SESSION['message_mode'] ?? 'bcc';
      
      echo "<html lang='{$_SESSION['language']}'>";

      print_status_overview();

      echo "<form method='post'>
            <br>
            <table>
            <tr><td style='padding-bottom: 1em;'><u>Send as:</u></td>
                <td style='padding: 0.3em; padding-bottom: 1em;'>";

      $value = ['bcc','cc','to'];

      foreach($value as $mode) {
        echo "<input type='radio' name='message_mode' id='$mode' value='$mode'";
        if($mode == $_SESSION['message_mode'])
          echo " checked";
        echo "> <label for='$mode'>$mode</label>";
      }

      echo "</td></tr>
            <tr><td style='padding-bottom: 1em;'><u>Send to:</u></td>
                <td style='padding: 0.3em; padding-bottom: 1em;'><input type='radio' name='recipients' value='all_users' checked>
                  <label for='recipients'>All users</label></td>
            </tr>
            <tr><td>Limit to:</td>
                <td style='padding: 0.3em;'><input type='checkbox' name='select_limit_group' value='true'>
                  <label for='group'>Group</label>
                  <select name='group_selected' id=group_selected>
                    <option value='' selected>-- select group --</option>";

              foreach($_SESSION['grouplist'] as $item)
                echo "<option value='$item'>$item</option>";

            echo "</select></td></tr>";

      echo "<tr><td></td>
                <td style='padding: 0.3em;'><input type='checkbox' name='select_limit_login' value='true'>
                  <label for='login'>Users with last login between </label>
                  <input type=date name='lastlogin_since'>
                  and
                  <input type=date name='lastlogin_before' value='".date('Y-m-d')."'></td>
            </tr>
            <tr><td></td>
                <td style='padding: 0.3em;'><input type='checkbox' name='select_limit_quota' value='true'>
                  <label for='quota_used'>Users with quota usage over </label>
                  <input style='width: 6em;' type=number min=0.5 step=0.5 name='quota_used' value=25> GB
            </tr>
            </table>";

      echo "<br><input id='button-email' type='submit' name='submit'
              value='Create list'>
            </form>";

    ?>
  </body>
</html>
