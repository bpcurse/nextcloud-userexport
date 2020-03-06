<?php

  session_start();
  $active_page = 'groups';
  require_once 'functions.php';
  include_once 'config.php';
  require_once 'l10n/' . $_SESSION['language'] . '.php';

  echo '<html lang="' . $_SESSION['language'] . '">'

?>

  <head>
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>Nextcloud user export</title>
  </head>

  <body>
    <?php

      include ("navigation.php");
      if (!$_SESSION['authenticated'])
        exit('<br>' . L10N_CONNECTION_NEEDED);

      print_status_overview();

    ?>

    <form method="post" action="groups_detail.php">
    <br><u><?php echo L10N_FORMAT_AS ?></u>
    <input type='radio' name='export_type' value='table' checked="checked">
      <?php echo L10N_TABLE ?>
    <input type='radio' name='export_type' value='csv'> <?php echo L10N_CSV ?>
    <br><br>
    <button id="button-blue" type='submit' name='submit'
      value='display'><?php echo L10N_DISPLAY ?></button>
    <br><br>
    <button id="button-green" type='submit' name='submit'
      value='download'><?php echo L10N_DOWNLOAD_CSV ?></button>
    </form>
  </body>
</html>
