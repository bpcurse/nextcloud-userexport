<?php

  session_start();
  $active_page = "users";
  require("functions.php");

  // Set SESSION variables to POST values
  if (isset($_POST['target_url'])) {
    $_SESSION['user_name'] = $_POST['user_name'];
    $_SESSION['user_pass'] = $_POST['user_pass'];

    // Check if plain HTTP is used without override command and exit if not
    $_SESSION['target_url'] = check_https($_POST['target_url']);

    // Fast cURL API call fetching the userlist (containing only user IDs) from target server
    $_SESSION['userlist'] = fetch_userlist();
  }

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
      echo '<hr>' . $_SESSION['target_url']
        . '<br>Number of user accounts: ' . count($_SESSION['userlist'])
        . '<hr><br><u>Include the following user data:</u><br><br>
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
            case 'total':
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
    <u>Display results as:</u><br><br>
    <input type='radio' name='export_type' value='table'
      <?php if ($export_type == 'table' || $export_type == null)
        {echo 'checked=\"checked\"';} ?>> Table
    <input type='radio' name='export_type' value='csv'
      <?php if ($export_type == 'csv') {echo 'checked=\"checked\"';} ?>> CSV
    <br><br>
    <span style="font-size: small">
    A CSV formatted file can be downloaded on the next page regardless of the above selection</span>
    <br><br>
    <input style="background-color: #4c6489; color: white; height: 45px"
      type="submit" onclick="submit" value="Fetch user data from server">
    </form>
  </body>
</html>
