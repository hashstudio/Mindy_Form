<?php

namespace Mindy\Form\Fields;

use Mindy\Utils\RenderTrait;

/**
 * Class LicenseField
 * @package Mindy\Form
 */
class LicenseField extends CheckboxField
{
    use RenderTrait;

    /**
     * @var array
     */
    public $htmlLabel = [];
    /**
     * @var string
     */
    public $errorMessage = 'You must agree terms';

    public $licenseTemplate = '';

    public function init()
    {
        $this->validators[] = function ($value) {
            if (!$value) {
                return $this->errorMessage;
            }
            return true;
        };
        parent::init();
    }

    public function render()
    {
        if(!empty($this->licenseTemplate)) {
            $tpl = self::renderTemplate($this->licenseTemplate);
            return $tpl . parent::render();
        } else {
            return parent::render();
        }
    }
}
