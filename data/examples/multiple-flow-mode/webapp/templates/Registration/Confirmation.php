<html>
  <body>
    <div>First Name: <?php echo $__continuation->getAttribute('firstName') ?></div>
    <div>Last Name: <?php echo $__continuation->getAttribute('lastName') ?></div>
    <form action="" method="post">
      <input type="hidden" name="<?php echo $__flowExecutionTicketKey ?>" value="<?php echo $__flowExecutionTicket ?>" />
      <input type="submit" name="<?php echo "{$__eventNameKey}_register" ?>" value="register" />
    </form>
  </body>
</html>
