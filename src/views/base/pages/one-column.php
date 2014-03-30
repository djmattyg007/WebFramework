<!DOCTYPE html>
<html>
<head>
<?php echo $this->renderChild("head"); ?>
</head>
<body>
<?php echo $this->renderChild("header"); ?>
<div class="container">
    <?php echo $this->renderChild("page-header"); ?>
    <?php echo $this->renderChild("page-content-main"); ?>
    <?php echo $this->renderChild("page-footer"); ?>
</div>
<?php echo $this->renderChild("footer"); ?>
</body>
</html>
