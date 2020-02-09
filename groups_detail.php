<?php

  session_start();
  $active_page = 'groups';
  require 'functions.php';
  include 'config.php';

  $export_type = $_POST['export_type'];
  $display_or_download = $_POST['submit'];
  $filename = $_POST['file']; // TODO unused
  $mime_type = $_POST['mime']; // TODO unused
  $filename_download = $_POST['name']; // TODO unused

  if ($display_or_download == "Download (CSV)") {
    // Set filename or create one depending on GET parameters
    if($filename_download == null) {
      $filename_download = "nextcloud-grouplist_" . date("Y-m-d_Hi") . ".csv";
    }

    // Create and populate CSV file with selected user data and set filename variable
    $filename = build_csv_file(select_user_data('utf8'));

    download_file($filename, $mime_type, $filename_download, TEMP_FOLDER);
    exit();
  }

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
      . '<br>Number of groups: ' . count($_SESSION['grouplist']) . '<hr>';

    echo build_table_group_data();

    ?>
  </body>
</html>
