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
 * @date 20/10/14.10.2014 17:51
 */

namespace Mindy\Form\Tests;


class ManagedTestForm extends TestForm
{
    public function getInlines()
    {
        return [
            ['post' => InlineTestForm::className()],
        ];
    }
}
