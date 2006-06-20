<html>
  <body>
    <h1>Counter: <?php echo $__continuation->getAttribute('counter') ?></h1>
    <form action="" method="post">
      <input type="hidden" name="<?php echo $__flowExecutionTicketKey ?>" value="<?php echo $__flowExecutionTicket ?>" />
      <input type="submit" name="<?php echo "{$__eventNameKey}_next" ?>" value="next" />
    </form>
  </body>
</html>
