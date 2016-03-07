<?php
/**
 * 
 *
 * All rights reserved.
 * 
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 20/10/14.10.2014 17:54
 */

namespace Mindy\Form\Tests;

use Mindy\Form\Fields\CharField;
use Mindy\Form\Fields\FileField;

class FooTestForm extends TestForm
{
    public function getFields()
    {
        return [
            'foo' => [
                'class' => CharField::className(),
                'required' => true
            ],
            'bar' => [
                'class' => FileField::className()
            ]
        ];
    }
}
