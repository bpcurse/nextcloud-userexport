<?php

  session_start();
  $active_page = 'groups';
  require 'functions.php';
  include 'config.php';

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
        exit('<br>Please first connect to a server at the <a href="index.php">server</a> page!');
      }
      echo '<hr>' . $_SESSION['target_url']
        . '<br>Number of groups: ' . count($_SESSION['grouplist']);
    ?>

    <hr><form method="post" action="groups_detail.php">
    <br><br>
    <u>Format as:</u>
    <input type='radio' name='export_type' value='table' checked="checked"> Table
    <input type='radio' name='export_type' value='csv'> CSV
    <br><br>
    <input style="background-color: #4c6489; color: white; height: 45px; width: 300px;"
      type='submit' name='submit' value='Display'>
    <br><br>
    <input style='background-color: green; color: white; height: 45px; width: 300px;'
      type='submit' name='submit' value='Download (CSV)'>
    </form>
  </body>
</html>
