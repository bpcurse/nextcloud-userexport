<?php

  $active_page = 'email';
  require_once 'functions.php';
  include_once 'config.php';

  session_secure_start();

  require_once 'l10n/'.$_SESSION['language'].'.php';

  if(!$_SESSION['authenticated']) {
    header('Content-Type: text/html; charset=utf-8');
    exit('<br>'.L10N_CONNECTION_NEEDED);
  }

  if($_POST['submit']) {

    $_SESSION['message_mode'] = $_POST['message_mode'] ?? $_SESSION['message_mode'];
    $_SESSION['filters_set'] = array_keys($_POST, 'set_filter');
    $_SESSION['filter_group'] = $_POST['filter_group'];
    $_SESSION['filter_ll_since'] = $_POST['filter_ll_since'];
    $_SESSION['filter_ll_before'] = $_POST['filter_ll_before'];
    $_SESSION['filter_quota'] = $_POST['filter_quota'];

    $user_ids = $_SESSION['filters_set']
      ? filter_users()
      : $_SESSION['userlist'];

    header("Location: ".build_mailto_list($_SESSION['message_mode'], $user_ids));

  }

?>

  <head>
    <link rel="stylesheet" type="text/css" href="style.php">
    <meta charset="UTF-8">
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
            <tr><td style='padding-bottom: 1em;'><u>".L10N_SEND_AS."</u></td>
                <td style='padding: 0.3em; padding-bottom: 1em;'>";

      $value = ['bcc','cc','to'];

      foreach($value as $mode) {
        echo "<input type='radio' name='message_mode' id='$mode' value='$mode'";
        if($mode == $_SESSION['message_mode'])
          echo " checked";
        echo "> <label for='$mode'>$mode</label>";
      }

      echo "</td></tr>
            <tr><td style='padding-bottom: 1em;'><u>".L10N_SEND_TO."</u></td>
                <td style='padding: 0.3em; padding-bottom: 1em;'>
                  <input type='radio' name='filter_group_choice' value='all_users' checked>
                    <label for='filter_group_choice'>".L10N_ALL_USERS."</label>
                  <input type='radio' name='filter_group_choice' value='set_filter'>
                    <label for='filter_group_choice'>".L10N_GROUP."</label>
                  <select name='filter_group'>
                    <option value='' selected>-- ".L10N_SELECT_GROUP." --</option>";
                    foreach($_SESSION['grouplist'] as $item)
                      echo "<option value='$item'>$item</option>";
      echo "</select></td></tr>
            <tr><td><u>".L10N_LIMIT_TO."<u></td>
                <td style='padding: 0.3em;'>
                  <input type='checkbox' name='filter_lastLogin_choice' value='set_filter'>
                    <label for='filter_lastLogin_choice'>".L10N_LAST_LOGIN_BETWEEN." </label>
                  <input type=date name='filter_ll_since'>
                    ".L10N_AND."
                  <input type=date name='filter_ll_before' value='".date('Y-m-d')."'>
                </td>
            </tr>
            <tr><td></td>
                <td style='padding: 0.3em;'>
                  <input type='checkbox' name='filter_quota_choice' value='set_filter'>
                    <label for='filter_quota_choice'>".L10N_QUOTA_USAGE_OVER." </label>
                  <input style='width: 6em;' type=number min=0.5 step=0.5 name='filter_quota' value=25> GB
            </tr>
            </table>";

      echo "<br><input id='button-email' type='submit' name='submit'
              value='".L10N_CREATE_LIST."'>
            </form>";

    ?>
  </body>
</html>
