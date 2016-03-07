<?php
/* @var $form \Mindy\Form\ModelForm */
/* @var $inlines array */
echo $form->asBlock();

foreach($inlines as $name => $inlineList) {
    echo "<h1>" . $name . "</h1>";

    foreach($inlineList as $inline) {
        foreach ($inline as $field) {
            echo $field;
        }
    }
}
