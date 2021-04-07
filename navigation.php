<div id="navigation">
  <ul>
    <li<?php if($active_page == "index")
      echo ' id="currentpage"'; ?>><a href="index.php"><?php echo L10N_SERVER ?></a>
    </li>
  <?php
    if($_SESSION['authenticated']) {
      echo '<li';
        if($active_page == "users") echo ' id="currentpage"';
      echo '><a href="users.php">'.L10N_USERS.'</a>
      </li>';
      echo '<li';
        if($active_page == "groups") echo ' id="currentpage"';
      echo '><a href="groups.php">'.L10N_GROUPS.'</a>
      </li>';

      if($_SESSION['groupfolders_active']) {
        echo '<li';
          if($active_page == "groupfolders") echo ' id="currentpage"';
        echo '><a href="groupfolders.php">'.L10N_GROUPFOLDERS.'</a>
        </li>';
      }

      echo '<li';
        if ($active_page == "email") echo ' id="currentpage"';
      echo '><a href="email.php">'.L10N_EMAIL.'</a>
      </li>';
      echo '<li';
        if ($active_page == "statistics") echo ' id="currentpage"';
      echo '><a href="statistics.php">'.L10N_STATISTICS.'</a>
      </li>
      <li><a style="color: red;"
          href="index.php?logout=true">'.L10N_LOGOUT.'</a>
      </li>';
    }
  ?>

  <li style="float:right;"><a style="font-size: 13px;"
    href="https://github.com/bpcurse/nextcloud-userexport">
    Nextcloud Userexport v1.1.2</a>
  </li>
  </ul>
</div>
