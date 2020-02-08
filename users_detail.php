<?php

  session_start();
  $active_page = "users";
  require("functions.php");
  // Filter POST array and save keys with value 'true' as constant
  define('EXPORT_CHOICES', array_keys($_POST,'true'));
  define('EXPORT_TYPE', $_POST['export_type']);

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
      . '<hr>';

    /**
      * Display results page either as HTML table or comma separated values (CSV)
      */
    if (EXPORT_TYPE == 'table') { echo build_table_user_data(select_user_data()); }
    elseif (EXPORT_TYPE == 'csv') { echo build_csv_user_data(select_user_data()); }

    ?>
  </body>
</html>
