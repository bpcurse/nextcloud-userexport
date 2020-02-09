<?php

  session_start();
  $active_page = "users";
  require("functions.php");
  include("config.php");

  $export_type = $_SESSION['export_type'];

?>

<html lang="en">
  <head>
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>Nextcloud user export</title>
  </head>

  <body>
    <?php

      include ("navigation.php");
      if (!$_SESSION['authenticated']) {
        exit('<br>Please first connect to a server on the server page!');
      }
      echo '<hr>' . $_SESSION['target_url']
        . '<br>Number of user accounts: ' . count($_SESSION['userlist']);

      echo '<hr><br><u>Include the following user data:</u><br><br>
        <form method="post" action="users_detail.php"><table><tr>';

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
              echo "<td><input type='checkbox' name='" . $option
                . "' value='true' " . $checked . ">" . $title
                . "</td></tr>";
              break;
            case 'lastLogin':
            case 'quota':
            case 'groups':
            case 'language':
              echo "<tr><td><input type='checkbox' name='" . $option
                . "' value='true' " . $checked . ">" . $title . "</td>";
              break;
            default:
              echo "<td><input type='checkbox' name='" . $option
                . "' value='true' " . $checked . ">" . $title . "</td>";
          }
        }
        echo '</table>';

    ?>
    <br><br>
    <u>Format as:</u>
    <input type='radio' name='export_type' value='table'
      <?php if ($export_type == 'table' || $export_type == null)
        {echo 'checked=\"checked\"';} ?>> Table
    <input type='radio' name='export_type' value='csv'
      <?php if ($export_type == 'csv') {echo 'checked=\"checked\"';} ?>> CSV
    <br><br>
    <input style="background-color: #4c6489; color: white; height: 45px; width: 300px;"
      type='submit' name='submit' value='Display'>
    <br><br>
    <input style='background-color: green; color: white; height: 45px; width: 300px;'
      type='submit' name='submit' value='Download (CSV)'>
    </form>
  </body>
</html>
