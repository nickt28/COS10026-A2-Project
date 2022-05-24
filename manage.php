<!DOCTYPE html>
<html lang="en" dir="ltr">
  <?php $title = 'Supervisor Management Quiz View'; include_once './header.inc' ?>
  <body>
	<?php session_start();?>
    <?php $select = 6; include_once './menu.inc' ?>
    <div class="manage-flex-content">
      <div class="manage-menu">
        <form method="post" action="manage.php">
          <button class="manage-hide" type="submit" name="action" value="5"></button>
          <button type="submit" name="action" value="1">List All attempts</button>
          <button type="submit" name="action" value="2">List half attempts</button>
          <button type="submit" name="action" value="3">Delete attempts</button>
          <button type="submit" name="action" value="4">Manage Score</button>
          <hr class="manage-menu-hr" />
          <?php
            if (!isset($_SESSION["username"])) {
              print "
              <div class='manage-id-field'>
                <label for='student_id'>
                  <input name='username' type='text' placeholder='Username' />
                </label>
                <label for='student_name'>
                  <input name='password' type='password' placeholder='Password' />
                </label>
              </div>";
              print "<button type='submit' name='action' value='5'>Login/Sign Up</button>";
            } else {
              print "
              <div class='manage-id-field'>
                <p>".$_SESSION["username"]."</p>
              </div>";
              print "<button type='submit' name='action' value='6'>Logout</button>";
            }
          ?>
        </form>
        <div style="height: 500px;"></div>
      </div>
      <div class="manage-content">
        <h1>Management Page</h1>
          <section>
            <table>
              <?php
                $main_page = true;
                include_once './manage_processing_revamp.php';
              ?>
            </table>
          </section>
      </div>
    </div>
    <?php include './footer.inc'; ?>
  </body>
</html>
