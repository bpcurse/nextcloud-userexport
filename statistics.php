<?php

  $active_page = 'statistics';
  require_once 'functions.php';
  include_once 'config.php';

  session_secure_start();

  require_once 'l10n/'.$_SESSION['language'].'.php';

  echo "<html lang='{$_SESSION['language']}'>"

?>

  <head>
    <link rel="stylesheet" type="text/css" href="style.php">
    <meta charset="UTF-8">
    <title>Nextcloud Userexport</title>
  </head>

  <body>
    <?php

      include 'navigation.php';

      if(!$_SESSION['authenticated']) {
        header('Content-Type: text/html; charset=utf-8');
        exit('<br>'.L10N_CONNECTION_NEEDED);
      }

      print_status_overview('full');

    ?>
  </body>
</html>
