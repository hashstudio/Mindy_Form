<?php

namespace Mindy\Form\Fields;

/**
 * Class AceField
 * @package Mindy\Form
 */
class AceField extends CharField
{
    public $template = "<textarea id='{id}' class='hide' name='{name}'{html}>{value}</textarea>";

    public $aceMode = "ace/mode/twig";

    public $aceTheme = "ace/theme/crimson_editor";

    public function render()
    {
        $out = strtr('<div id="{id}-ace-editor" class="ace-editor">{value}</div>
        <script type="text/javascript">
            var editor = ace.edit("{id}-ace-editor");

            // Hide deprecate warning
            editor.$blockScrolling = Infinity;

            editor.setFontSize(".9rem");
            editor.getSession().setTabSize(4);
            editor.setShowPrintMargin(false);
            editor.setAutoScrollEditorIntoView(true);
            editor.setOption("enableEmmet", true);
            ' . ($this->aceMode ? 'editor.getSession().setMode("' . $this->aceMode . '");' : '') . '
            ' . ($this->aceTheme ? 'editor.setTheme("' . $this->aceTheme . '");' : '') . '
            editor.getSession().on("change", function(e) {
                $("#{id}").val(editor.getSession().getValue());
            });
        </script>', [
            '{id}' => $this->getHtmlId(),
            '{value}' => htmlentities($this->getValue())
        ]);
        return parent::render() . $out;
    }
}
