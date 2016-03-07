<?php

namespace Mindy\Form\Fields;

class UEditorField extends TextField
{
    public function render()
    {
        $js = "<script type='text/javascript'>var ue = UE.getEditor('{$this->getHtmlId()}');</script>";
        return parent::render() . $js;
    }
}