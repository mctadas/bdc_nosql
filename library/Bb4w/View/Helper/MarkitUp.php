<?php

class Bb4w_View_Helper_MarkItUp extends Zend_View_Helper_FormTextarea
{

    public function markitUp($name, $value = null, $attribs = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
		extract($info); // name, value, attribs, options, listsep, disable

		// is it disabled?
		$disabled = '';
		if ($disable) {
			// disabled.
			$disabled = ' disabled="disabled"';
		}
        
		// Make sure that there are 'rows' and 'cols' values
		// as required by the spec.  noted by Orjan Persson.
		if (empty($attribs['rows'])) {
			$attribs['rows'] = (int) $this->rows;
		}
		if (empty($attribs['cols'])) {
			$attribs['cols'] = (int) $this->cols;
		}
        
		$this->view->headLink()->appendStylesheet('/js/markitup/skins/simple/style.css');
		$this->view->headLink()->appendStylesheet('/js/markitup/sets/textile/style.css');
        
		// build link to js
		$this->view->headScript()->appendFile('/js/markitup/jquery.markitup.js');
        $this->view->headScript()->appendFile('/js/markitup/sets/textile/set.js');
        
        
        $xhtml = '
						<textarea name="' . $this->view->escape($name) . '"'
		                . ' id="id_'.$this->view->escape($name).'"'
		                . $disabled
		                . $this->_htmlAttribs($attribs) . '>'
		                . $this->view->escape($value) . '</textarea>';
        
        $xhtml .= "
            
            <script language=\"javascript\">
                $(document).ready(function()	{
                               
                    $('#id_". $this->view->escape($name) ."').markItUp(markdownSettings);
                });
            </script>

        ";
        return $xhtml;
    }
}