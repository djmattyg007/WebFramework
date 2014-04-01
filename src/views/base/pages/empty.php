<!DOCTYPE html>
<html>
<head>
<?php echo $this->renderChild("head"); ?>
</head>
<body>
<?php echo $this->renderChild("header"); ?>
<div class="container">
    <?php echo $this->renderChild("page-content"); ?>
</div>
<?php echo $this->renderChild("footer"); ?>
</body>
</html>
