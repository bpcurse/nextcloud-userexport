<?php

  session_start();
  $active_page = 'users';
  require_once 'functions.php';
  include_once 'config.php';
  require_once 'l10n/'.$_SESSION['language'].'.php';

  $export_type = $_SESSION['export_type'];

  echo "<html lang='{$_SESSION['language']}'>";

?>

  <head>
    <link rel="stylesheet" type="text/css" href="style.php">
    <meta charset="UTF-8">
    <title>Nextcloud Userexport</title>
    <script>
      function toggle(source) {
        checkboxes = document.getElementsByClassName('checkbox');
        for(var i=0, n=checkboxes.length;i<n;i++) {
          checkboxes[i].checked = source.checked;
        }
      }
    </script>
  </head>

  <body>
    <?php

      include 'navigation.php';

      if(!$_SESSION['authenticated']) {
        header('Content-Type: text/html; charset=utf-8');
        exit('<br>'.L10N_CONNECTION_NEEDED);
      }

      print_status_overview();

      echo '<br><u>'.L10N_SELECT_USER_DATA.'</u><br><br>
        <form method="post" action="users_detail.php">
        <table id="options">
        <tr>';

      foreach($_SESSION['data_options'] as $option => $title) {
        $checked = in_array($option, $_SESSION['data_choices'])
          ? "checked='checked'"
          : null;
        switch ($option) {
          case 'email':
          case 'enabled':
          case 'percentage_used':
          case 'subadmin':
          case 'locale':
            echo "<td><input type='checkbox' class='checkbox' name='$option' value='true' $checked>$title</td></tr>";
            break;
          case 'lastLogin':
          case 'quota':
          case 'free':
          case 'language':
            echo "<tr><td><input type='checkbox' class='checkbox' name='$option' value='true' $checked>$title</td>";
            break;
          default:
            echo "<td><input type='checkbox' class='checkbox' name='$option' value='true' $checked>$title</td>";
        }
      }

      echo "<tr><td colspan=3 style='height: 10px;'></td></tr>
            <tr><td style='border: 1px solid #ddd;'>
              <input type='checkbox' onClick='toggle(this)' /> "
                .L10N_TOGGLE_ALL. "
            </td></tr>
          </table><br><br>
          <u>".L10N_LIMIT_TO."</u><br><br>
          <table>
          <tr><td style='padding-bottom: 0.3em;'><input type='checkbox' name='filter_group_choice' value='set_filter'>
                <label for='filter_group_choice'>".L10N_GROUP."</label>
                <select name='filter_group'>
                  <option value='' selected>-- ".L10N_SELECT_GROUP." --</option>";
                  foreach($_SESSION['grouplist'] as $item)
                    echo "<option value='$item'>$item</option>";
    echo "</select></td></tr>
          <tr><td><input type='checkbox' name='filter_lastLogin_choice' value='set_filter'>
                <label for='filter_lastLogin_choice'>".L10N_LAST_LOGIN_BETWEEN." </label>
                <input type=date name='filter_ll_since'>
                ".L10N_AND."
                <input type=date name='filter_ll_before' value='".date('Y-m-d')."'></td>
          </tr>
          <tr><td><input type='checkbox' name='filter_quota_choice' value='set_filter'>
                <label for='filter_quota_choice'>".L10N_QUOTA_USAGE_OVER." </label>
                <input style='width: 6em;' type=number min=0.5 step=0.5 name='filter_quota' value=25> GB
          </tr>
          </table>";

    ?>
    <br><br>
    <u><?php echo L10N_FORMAT_AS ?></u>
    <input type='radio' name='export_type' value='table'
      <?php if ($export_type == 'table' || $export_type == null)
        echo 'checked=\"checked\"'; ?>> <?php echo L10N_TABLE ?>
    <input type='radio' name='export_type' value='csv'
      <?php if ($export_type == 'csv')
        echo 'checked=\"checked\"'; ?>> CSV
    <br><br>
    <button id='button-display' type='submit' name='submit'
      value='display'><?php echo L10N_DISPLAY ?></button>
    <br><br><br>
    <u><?php echo L10N_COLUMN_HEADERS ?></u>
    <input type='radio' name='csv_headers' value='default'
      <?php if ($csv_headers == 'true' || $csv_headers === null)
        echo 'checked=\"checked\"'; ?>> <?php echo L10N_YES ?>
    <input type='radio' name='csv_headers' value='no_headers'
      <?php if ($csv_headers == 'false')
        echo 'checked=\"checked\"'; ?>> <?php echo L10N_NO ?>
    <br><br>
    <button id='button-download' type='submit' name='submit'
      value='download'><?php echo L10N_DOWNLOAD_CSV ?></button>
    </form>
  </body>
</html>
