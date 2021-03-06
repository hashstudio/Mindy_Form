<?php

namespace Mindy\Form;

use Mindy\Utils\RenderTrait;

/**
 * Class Form
 * @package Mindy\Form
 */
class Form extends BaseForm
{
    use RenderTrait;

    public function renderInternal($template, array $params)
    {
        return self::renderTemplate($template, $params);
    }
}
