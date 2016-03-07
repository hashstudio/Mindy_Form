<?php

namespace Mindy\Form\Fields;

use Closure;
use Mindy\Form\Form;
use Mindy\Form\ModelForm;
use Mindy\Orm\Manager;
use Mindy\Orm\Model;

/**
 * Class DropDownField
 * @package Mindy\Form
 */
class DropDownField extends Field
{
    /**
     * @var array
     */
    public $choices = [];
    /**
     * Span tag needed because: http://stackoverflow.com/questions/23920990/firefox-30-is-not-hiding-select-box-arrows-anymore
     * @var string
     */
    public $template = "<span class='select-holder'><select id='{id}' name='{name}' {html}>{input}</select></span>";
    /**
     * @var bool
     */
    public $multiple = false;
    /**
     * @var string
     */
    public $empty = '';
    /**
     * @var array
     */
    public $disabled = [];

    public function render()
    {
        $label = $this->renderLabel();
        $input = $this->renderInput();

        $hint = $this->hint ? $this->renderHint() : '';
        $errors = $this->renderErrors();

        $name = $this->getHtmlName();
        return implode("\n", ["<input type='hidden' value='' name='{$name}' />", $label, $input, $hint, $errors]);
    }

    public function renderInput()
    {
        return strtr($this->template, [
            '{type}' => $this->type,
            '{id}' => $this->getHtmlId(),
            '{input}' => $this->getInputHtml(),
            '{name}' => $this->multiple ? $this->getHtmlName() . '[]' : $this->getHtmlName(),
            '{html}' => $this->getHtmlAttributes()
        ]);
    }

    protected function getInputHtml()
    {
        $out = '';
        $data = [];
        $selected = [];

        if (!empty($this->choices)) {
            $choices = $this->choices;
        } else {
            $choices = $this->form->getModel()->getField($this->name)->choices;
        }

        if (!empty($choices)) {
            if ($choices instanceof Closure) {
                $data = $choices->__invoke();
            } else {
                $data = $choices;
            }

            $value = $this->getValue();
            if ($value) {
                if ($value instanceof Manager) {
                    $selected = $value->valuesList(['pk'], true);
                } else if ($value instanceof Model) {
                    $selected[] = $value->pk;
                } else {
                    $selected[] = $value;
                }
            }

            if ($this->form instanceof ModelForm) {
                $model = $this->form->getInstance();
                $model = $model ? $model : $this->form->getModel();

                $field = $model->getField($this->name);
                if ($field->null && !$this->multiple) {
                    $data = ['' => ''] + $data;
                }

                if (is_a($field, $model::$foreignField)) {
                    $related = $model->{$this->name};
                    if ($related) {
                        $selected[] = $related->pk;
                    }
                } else if (is_a($field, $model::$manyToManyField)) {
                    $this->multiple = true;

                    $selectedTmp = $field->getManager()->all();
                    foreach ($selectedTmp as $model) {
                        $selected[] = $model->pk;
                    }
                } else {
                    $selected[] = $model->{$this->name};
                }
            } elseif ($this->form instanceof Form) {
                if (!is_array($this->value)) {
                    if ($this->value) {
                        $selected = [$this->value];
                    }
                } else {
                    $selected = $this->value;
                };
            }

            if ($this->multiple) {
                $this->html['multiple'] = 'multiple';
            }
            return $this->valueToHtml($data, $selected);
        }

        if ($this->form instanceof ModelForm && $this->form->getModel()->hasField($this->name)) {
            $model = $this->form->getModel();
            $field = $model->getField($this->name);

            if (is_a($field, $model::$manyToManyField)) {
                $this->multiple = true;

                $modelClass = $field->modelClass;
                $models = $modelClass::objects()->all();

                if ($value = $this->getValue()) {
                    if ($value instanceof Manager) {
                        $selectedTmp = $value->all();
                        foreach ($selectedTmp as $item) {
                            $selected[] = $item->pk;
                        }
                    } else {
                        $selected = is_array($value) ? $value : [$value];
                    }
                }

                $this->html['multiple'] = 'multiple';

                foreach ($models as $item) {
                    $data[$item->pk] = (string)$item;
                }
            } elseif (is_a($field, $model::$hasManyField)) {
                $this->multiple = true;

                $modelClass = $field->modelClass;
                $models = $modelClass::objects()->all();

                $this->html['multiple'] = 'multiple';

                foreach ($models as $item) {
                    $data[$item->pk] = (string)$item;
                }
            } elseif (is_a($field, $model::$foreignField)) {
                $modelClass = $field->modelClass;
                $qs = $modelClass::objects();
                if (get_class($model) == $modelClass && $model->getIsNewRecord() === false) {
                    $qs = $qs->exclude(['pk' => $model->pk]);
                }
                /* @var $modelClass \Mindy\Orm\Model */
                if (!$this->required) {
                    $data[''] = $this->empty;
                }
                if ($value = $this->getValue()) {
                    $selected[] = $value instanceof Model ? $value->pk : $value;
                }
                foreach ($qs->all() as $item) {
                    $data[$item->pk] = (string)$item;
                }
            } else {
                $data = parent::getValue();
            }
        } else {
            $data = parent::getValue();
        }

        if (is_array($data)) {
            return $this->valueToHtml($data, $selected);
        } else {
            return $out;
        }
    }

    protected function valueToHtml(array $data, array $selected = [])
    {
        $out = '';
        foreach ($data as $value => $name) {
            $out .= strtr("<option value='{value}'{selected}{disabled}>{name}</option>", [
                '{value}' => $value,
                '{name}' => $name,
                '{disabled}' => in_array($value, $this->disabled) ? " disabled" : "",
                '{selected}' => in_array($value, $selected) ? " selected='selected'" : ""
            ]);
        };
        return $out;
    }
}
