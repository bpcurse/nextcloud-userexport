<?php

  session_start();
  $active_page = 'email';
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
      if (!$_SESSION['authenticated'])
        exit('<br>' . ERROR_CONNECTION_NEEDED);

      print_status_overview();

      show_button_mailto($_SESSION['message_mode']);

      /* TODO Future release

        echo '<br><u>Send emails to the following users:</u><br><br>
        <form method="post">
        <input type="radio" name="filter" checked="checked" value="all">All
        <input type="radio" name="filter" value="since">No login since
        <input type="text" name="since" size="10" placeholder="2020-01-20">';

      <hr><u><br>Filter by the following groups:</u><table><tr>';

      foreach ($_SESSION['grouplist'] as $key => $group) {
        if ($key % 4 === 0 && $key !== 0) { echo '<tr>'; }
        echo "<td><input type='checkbox' name='" . $group . "' value='true'>"
          . utf8_decode($group) . '</td>';
        if ($key+1 % 4 === 0 || $key+1 == $_SESSION['groupcount'])
          { echo '</tr>'; }
      }

      echo '</table>';

    ?>

    <br><br>
    <u>Send as:</u>
    <input type='radio' name='message_type' value='bcc'
      <?php if ($message_type == 'bcc' || $message_type == null)
        {echo 'checked=\"checked\"';} ?>> bcc
    <input type='radio' name='message_type' value='to'
      <?php if ($message_type == 'to') {echo 'checked=\"checked\"';} ?>> to
    <input type='radio' name='message_type' value='cc'
      <?php if ($message_type == 'to') {echo 'checked=\"checked\"';} ?>> cc
    <br><br>
    <input style="background-color: #4c6489; color: white; height: 45px; width: 300px;"
      type='submit' name='submit' value='Open Mail Application'>
    </form>*/

    ?>
  </body>
</html>
