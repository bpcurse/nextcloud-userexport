<?php

  session_start();
  $active_page = 'statistics';
  require_once 'functions.php';
  include_once 'config.php';
  require_once 'l10n/'.$_SESSION['language'].'.php';

  echo "<html lang='{$_SESSION['language']}'>"

?>

  <head>
    <link rel="stylesheet" type="text/css" href="style.php">
    <title>Nextcloud Userexport</title>
  </head>

  <body>
    <?php

      include 'navigation.php';
      if(!$_SESSION['authenticated'])
        exit('<br>'.L10N_CONNECTION_NEEDED);

      print_status_overview('full');

    ?>
  </body>
</html>
