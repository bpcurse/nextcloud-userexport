<?php

  session_start();
  $active_page = "groups";
  require("functions.php");

  // Fetch grouplist from target server
  $_SESSION['grouplist'] = fetch_grouplist();

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
      . '<br>Number of groups: ' . count($_SESSION['grouplist']) . '<hr>';

    echo build_table_group_data();

    ?>
  </body>
</html>
