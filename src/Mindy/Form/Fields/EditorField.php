<?php

namespace Mindy\Form\Fields;

use Mindy\Helper\JavaScript;

/**
 * Class EditorField
 * @package Mindy\Form
 */
class EditorField extends TextAreaField
{
    public function render()
    {
        $model = $this->form->getInstance();
        $options = Javascript::encode([
            'language' => 'ru',
            'plugins' => ['space', 'text', 'image', 'video'],
            'image' => [
                'uploadUrl' => '/core/files/upload/?path=' . $model->getModuleName() . '/' . $model->classNameShort()
            ]
        ]);
        $js = "<script type='text/javascript'>
        var editor = meditor.init('#{$this->getHtmlId()}', $options);
        </script>";
        return parent::render() . $js;
    }
}