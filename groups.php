<?php

  session_start();
  $active_page = "groups";
  require("functions.php");
  include("config.php");

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
      . '<br>Number of groups: ' . count($_SESSION['grouplist']) . '<hr>';

    echo build_table_group_data();

    ?>
  </body>
</html>
