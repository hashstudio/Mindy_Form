<?php
/* @var $form \Mindy\Form\BaseForm */
/* @var $inlines \Mindy\Form\BaseForm[] */
foreach($form as $name => $field) {
    echo $field . "\n";
}

foreach($inlines as $inlineName => $instances) { ?>
<h2><?php echo $inlineName ?></h2><?php echo "\n" ?>
<?php
foreach($instances as $instance) {
foreach($instance as $n => $f) {
echo $f . "\n";
}
}
?>
<?php } ?>
