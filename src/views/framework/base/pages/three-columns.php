<!DOCTYPE html>
<html>
<head>
<?php echo $this->renderChild("head"); ?>
</head>
<body>
<?php echo $this->renderChild("header"); ?>
<div class="container">
    <?php echo $this->renderChild("page-header"); ?>
    <div class="row">
        <div class="columns small-12 medium-3 side-nav-container">
            <?php echo $this->renderChild("page-content-left"); ?>
        </div>
        <div class="columns small-12 medium-6">
            <?php echo $this->renderChild("page-content-main"); ?>
        </div>
        <div class="columns small-12 medium-3 side-nav-container">
            <?php echo $this->renderChild("page-content-right"); ?>
        </div>
    </div>
    <?php echo $this->renderChild("page-footer"); ?>
</div>
<?php echo $this->renderChild("footer"); ?>
</body>
</html>

