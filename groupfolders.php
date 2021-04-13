<?php

  session_start();
  $active_page = 'groupfolders';
  require_once 'functions.php';
  include_once 'config.php';
  require_once 'l10n/'.$_SESSION['language'].'.php';

  echo "<html lang='{$_SESSION['language']}'>";

?>

  <head>
    <link rel="stylesheet" type="text/css" href="style.php">
    <meta charset="UTF-8">
    <title>Nextcloud Userexport</title>
  </head>

  <body>
    <?php

      include 'navigation.php';
      if(!$_SESSION['authenticated'])
        exit('<br>' . L10N_CONNECTION_NEEDED);

      print_status_overview();

    ?>

    <form method="post" action="groupfolders_detail.php">
    <br><u><?php echo L10N_FORMAT_AS ?></u>
    <input type='radio' name='export_type' value='table' checked="checked">
      <?php echo L10N_TABLE ?>
    <input type='radio' name='export_type' value='csv'> <?php echo L10N_CSV ?>
    <br><br>
    <button id="button-display" type='submit' name='submit'
      value='display'><?php echo L10N_DISPLAY ?></button>
    <br><br><br>
    <u><?php echo L10N_COLUMN_HEADERS ?></u>
    <input type='radio' name='csv_headers' value='default'
      <?php if ($csv_headers == 'true' || $csv_headers === null)
        echo 'checked=\"checked\"'; ?>> <?php echo L10N_YES ?>
    <input type='radio' name='csv_headers' value='no_headers'
      <?php if ($csv_headers == 'false')
        echo 'checked=\"checked\"'; ?>> <?php echo L10N_NO ?>
    <br><br>
    <button id="button-download" type='submit' name='submit'
      value='download'><?php echo L10N_DOWNLOAD_CSV ?></button>
    </form>
  </body>
</html>
