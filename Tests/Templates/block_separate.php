<?php
/* @var $form RenderForm */
foreach($form as $name => $field) {
    echo $field->renderLabel();
    echo $field->renderInput();
    foreach($field->errors as $error) {
        echo $error;
    }
}
