<?php

  session_start();
  $active_page = 'groups';
  require 'functions.php';
  include 'config.php';
  require 'l10n/' . $_SESSION['language'] . '.php';

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
        exit('<br>' . L10N_ERROR_CONNECTION_NEEDED);

      print_status_overview();

    ?>

    <form method="post" action="groups_detail.php">
    <br><u><?php echo L10N_FORMAT_AS ?></u>
    <input type='radio' name='export_type' value='table' checked="checked"> Table
    <input type='radio' name='export_type' value='csv'> CSV
    <br><br>
    <input id="button-blue" type='submit' name='submit'
      value='<?php echo L10N_DISPLAY ?>'>
    <br><br>
    <input id="button-green" type='submit' name='submit'
      value='<?php echo L10N_DOWNLOAD_CSV ?>'>
    </form>
  </body>
</html>
