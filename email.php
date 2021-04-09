<?php

  session_start();
  $active_page = 'email';
  require_once 'functions.php';
  include_once 'config.php';
  require_once 'l10n/'.$_SESSION['language'].'.php';

  echo "<html lang='{$_SESSION['language']}'>";

?>

  <head>
    <link rel="stylesheet" type="text/css" href="style.php">
    <title>Nextcloud Userexport</title>
  </head>

  <body>
    <?php

      include 'navigation.php';
      if(!$_SESSION['authenticated'])
        exit('<br>'.L10N_CONNECTION_NEEDED);

      print_status_overview();

      if($_POST['submit']) {
        switch($_POST['recipients']) {
          case 'all_users':
            show_button_mailto($_SESSION['message_mode']);
            break;
          case 'group':
            $user_ids = select_data_all_users_filter(
              'groups', $_POST['group_selected']);
            show_button_mailto($_SESSION['message_mode'], $user_ids,
              'Open mailto link (Group '.$_POST['group_selected']);
            //echo '<pre>';
            //print_r(select_data_all_users_filter());
            //echo '</pre>';
            break;
          case 'login':
            $user_ids = select_data_all_users_filter(
              'lastLogin',$_POST['lastlogin_since'],$_POST['lastlogin_before']);
            show_button_mailto($_SESSION['message_mode'], $user_ids,
              'Open mailto link filtered by last login');
            break;
        }
        exit();
      }

      $_SESSION['message_mode'] = $_SESSION['message_mode'] ?? 'bcc';

      echo "<form method='post'>
            <br>
            <table>
            <tr><td style='padding-bottom: 1em'><u>Send as:</u></td>
                <td style='padding: 0.3em; padding-bottom: 1em'>";

      $value = ['bcc','cc','to'];

      foreach($value as $mode) {
        echo "<input type='radio' name='message_mode' id='$mode' value='$mode'";
        if($mode == $_SESSION['message_mode'])
          echo " checked";
        echo "> <label for='$mode'>$mode</label>";
      }

      echo "</td></tr>
            <tr><td><u>Send to:</u></td>
                <td style='padding: 0.3em;'><input type='radio' name='recipients' value='all_users' checked>
                  <label for='recipients'>All users</label></td>
            </tr>
            <tr><td></td><td style='padding: 0.3em;'><input type='radio' name='recipients' value='group'>
                  <label for='recipients'>Group</label>
                  <select name='group_selected' id=group_selected>
                    <option value='' selected>-- select group --</option>";

              foreach($_SESSION['grouplist'] as $item)
                echo "<option value='$item'>$item</option>";

              echo "</select></td></tr>";

      echo "<tr><td></td>
                <td style='padding: 0.3em;'><input type='radio' name='recipients' value='login'>
                  <label for='login'>Users with last login between </label>
                  <input type=date name='lastlogin_since'>
                  and
                  <input type=date name='lastlogin_before' value='".date('Y-m-d')."'></td>
            </tr>
            </table>";

      echo "<br><input type='submit' name='submit' value='Open mailto link'>
            </form>";

    ?>
  </body>
</html>
