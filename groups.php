<?php

  session_start();
  $active_page = "groups";
  require("functions.php");

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
      exit('<br>Please first connect to a server on the authentication page!');
    }
    // Fetch grouplist from target server
    $_SESSION['grouplist'] = fetch_grouplist();
    echo '<hr>' . $_SESSION['target_url']
      . '<br>Number of groups: ' . count($_SESSION['grouplist']) . '<hr>';

    echo build_table_group_data();

    ?>
  </body>
</html>
