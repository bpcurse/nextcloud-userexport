<?php include 'config.php' ?>

<div id="navigation"><ul>
  <li<?php if ($active_page == "index")
  { echo ' id="currentpage"'; } ?>><a href="index.php">Server</a></li>
  <li<?php if ($active_page == "users")
  { echo ' id="currentpage"'; } ?>><a href="users.php">Users</a></li>
  <li<?php if ($active_page == "groups")
  { echo ' id="currentpage"'; } ?>><a href="groups.php">Groups</a></li>
  <li style="float:right;"><b><a style="color: red;" href="https://github.com/bpcurse/nextcloud-userexport">nextcloud-userexport v1.0.0 Alpha 4</a></b>
</ul></div>
