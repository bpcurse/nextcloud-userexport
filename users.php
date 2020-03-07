<?php

  session_start();
  $active_page = 'users';
  require_once 'functions.php';
  include_once 'config.php';
  require_once 'l10n/' . $_SESSION['language'] . '.php';

  $export_type = $_SESSION['export_type'];

  echo '<html lang="' . $_SESSION['language'] . '">'

?>

  <head>
    <link rel="stylesheet" type="text/css" href="style.php">
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

      include ("navigation.php");
      if (!$_SESSION['authenticated'])
        exit('<br>' . L10N_CONNECTION_NEEDED);

      print_status_overview();

      echo '<br><u>' . L10N_SELECT_USER_DATA . '</u><br><br>
        <form method="post" action="users_detail.php">
        <table id="options">
        <tr>';

      foreach ($_SESSION['data_options'] as $option => $title) {
        $checked = in_array($option, $_SESSION['data_choices'])
          ? "checked='checked'"
          : null;
        switch ($option) {
          case 'email':
          case 'enabled':
          case 'free':
          case 'subadmin':
          case 'locale':
            echo "<td><input type='checkbox' class='checkbox' name='" . $option
              . "' value='true' " . $checked . ">" . $title
              . "</td></tr>";
            break;
          case 'lastLogin':
          case 'quota':
          case 'groups':
          case 'language':
            echo "<tr><td><input type='checkbox' class='checkbox' name='" . $option
              . "' value='true' " . $checked . ">" . $title . "</td>";
            break;
          default:
            echo "<td><input type='checkbox' class='checkbox' name='" . $option
              . "' value='true' " . $checked . ">" . $title . "</td>";
        }
      }

      echo '<tr><td colspan=3 style="height: 10px;"></td></tr>
            <tr><td style="background-color: whitesmoke;">
              <input type="checkbox" onClick="toggle(this)" /> '
                . L10N_TOGGLE_ALL . '
            </td></tr>
          </table>';

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
    <br><br>
    <button id='button-download' type='submit' name='submit'
      value='download'><?php echo L10N_DOWNLOAD_CSV ?></button>
    </form>
  </body>
</html>
