<?php

  session_start();
  $active_page = 'users';
  require 'functions.php';
  include 'config.php';

  // Filter POST array and save keys with value 'true' as constant
  define('EXPORT_CHOICES', array_keys($_POST,'true'));
  $export_type = $_POST['export_type'];
  $display_or_download = $_POST['submit'];
  $filename = $_POST['file']; // TODO unused
  $mime_type = $_POST['mime']; // TODO unused
  $filename_download = $_POST['name']; // TODO unused

  if ($display_or_download == "Download (CSV)") {
    // Set filename or create one depending on GET parameters
    if($filename_download == null) {
      $filename_download = "nextcloud-userlist_" . date("Y-m-d_Hi") . ".csv";
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
      . '<br>Number of user accounts: ' . count($_SESSION['userlist'])
      . '<hr>';

    if ($display_or_download == "Display") {
      /**
        * Display results page either as HTML table or comma separated values (CSV)
        */
      if ($export_type == 'table') {
        echo build_table_user_data(select_user_data());
      }
      elseif ($export_type == 'csv') {
        echo build_csv_user_data(select_user_data());
      }
    }

    ?>
  </body>
</html>
