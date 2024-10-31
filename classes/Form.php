<?php
namespace muhandle;

class Form {

    /**
     * Form attributes
     * @var array $aAttributes
     */
    protected $aAttributes = array();

    /**
     * Form inner html data
     * @var array $aData
     */
    protected $aData = array();

    /**
     * Unique input names
     * @var array $aNames
     */
    protected $aNames = array();

    /**
     * Build form
     * @param string $sId
     * @param array $aAttributes
     */
    public function __construct($sId, $aAttributes = array()) {
		if (empty($sId)) {
			throw new \Exception('ID couldn\'t be empty');
		}
        $aAttributes['id'] = $sId;
        if (empty($aAttributes['method'])) {
            $aAttributes['method'] = 'post';
        }
        $this->aAttributes = $aAttributes;
    }

	/**
	 * Has input
	 * @return boolean
	 */
	public function hasInput() {
		$aData = $this->getAllInput();
		return isset($aData[$this->aAttributes['id']]);
	}

	/**
	 * Clear input
	 * @return void
	 */
	public function clearInput() {

		if (strtolower($this->aAttributes['method']) == 'get') {
			unset($_GET[$this->aAttributes['id']]);
		}
		else {
			unset($_POST[$this->aAttributes['id']]);
		}
	}

	public function getAllInput() {
		$aData = $_POST;
		if (strtolower($this->aAttributes['method']) == 'get') {
			$aData = $_GET;
		}
		return $aData;
	}

	/**
	 * Add other tag
	 * @param string $sType
	 * @param array $aAttributes
	 * @param string $sInnerHtml
	 */
	public function addOther($sType, $aAttributes = array(), $sInnerHtml = '', $sBefore = '', $sAfter = '') {
		$this->aData[] = array($sType, $aAttributes, $sInnerHtml, 'before' => $sBefore, 'after' => $sAfter);
	}

	/**
     * Add input to form
     * @param string $sLabel
     * @param array $aAttributes
     * @param array $aValidation
     * @param string $sBefore
     * @param string $sAfter
     * @throws \Exception
     */
    public function addInput($sLabel, $aAttributes, $aValidation = array(), $sBefore = '', $sAfter = '') {
        if (empty($aAttributes['type'])) {
            throw new \Exception('Please add type attribute for "' . $sLabel . '"');
        }
        if (empty($aAttributes['id'])) {
            $aAttributes['id'] = md5($this->aAttributes['id'] . $sLabel . $aAttributes['type']);
        }
        if (empty($aAttributes['name'])) {
            $aAttributes['name'] = md5($this->aAttributes['id'] . $sLabel . $aAttributes['type']);
            if (!$this->isUniqueName($aAttributes['name'])) {
                throw new \Exception('Please add unique name attribute for "' . $sLabel . '"');
            }
        }
        if (!in_array($aAttributes['type'],array('radio','checkbox','hidden'))) {
            if (!empty($sLabel)) {
                $this->aData[] = array('label', array('for' => $aAttributes['id']), $sLabel, 'before' => $sBefore);
            }
        }
        if (!in_array($aAttributes['type'],array('radio','checkbox'))) {
            if (!empty($sLabel)) {
                $this->aData[] = array('input', $aAttributes, null, $aValidation, 'after' => $sAfter);
            } else {
                $this->aData[] = array('input', $aAttributes, null, $aValidation, 'before' => $sBefore, 'after' => $sAfter);
            }
        }
        else {
            if (!empty($sLabel)) {
                $this->aData[] = array('input', $aAttributes, null, $aValidation, 'before' => $sBefore);
                $this->aData[] = array('label', array('for' => $aAttributes['id']), $sLabel, 'after' => $sAfter);
            } else {
                $this->aData[] = array('input', $aAttributes, null, $aValidation, 'before' => $sBefore, 'after' => $sAfter);
            }
        }
    }

    /**
     * Add textarea to form
     * @param string $sLabel
     * @param array $aAttributes
     * @param array $aValidation
     * @param string $sBefore
     * @param string $sAfter
     * @throws \Exception
     */
    public function addTextarea($sLabel, $aAttributes = array(), $aValidation = array(), $sBefore = '', $sAfter = '') {
        if (empty($aAttributes['id'])) {
            $aAttributes['id'] = md5($this->aAttributes['id'] . $sLabel . 'textarea');
        }
        if (empty($aAttributes['name'])) {
            $aAttributes['name'] = md5($this->aAttributes['id'] . $sLabel . 'textarea');
            if (!$this->isUniqueName($aAttributes['name'])) {
                throw new \Exception('Please add unique name attribute for "' . $sLabel . '"');
            }
        }
        $this->aData[] = array('label', array('for' => $aAttributes['id']), $sLabel, 'before' => $sBefore);
        $this->aData[] = array('textarea', $aAttributes, htmlentities($aAttributes['value']), $aValidation, 'after' => $sAfter);
    }

    /**
     * Add select to form
     * @param string $sLabel
     * @param array $aOptions
     * @param array $aAttributes
     * @param array $aValidation
     * @param string $sBefore
     * @param string $sAfter
     * @throws \Exception
     */
    public function addSelect($sLabel, $aOptions, $aAttributes = array(), $aValidation = array(), $sBefore = '', $sAfter = '') {
        if (!is_array($aOptions) || empty($aOptions)) {
            throw new \Exception('Please add otpions for "' . $sLabel . '"');
        }
        if (empty($aAttributes['id'])) {
            $aAttributes['id'] = md5($this->aAttributes['id'] . $sLabel . 'select');
        }
        if (empty($aAttributes['name'])) {
            $aAttributes['name'] = md5($this->aAttributes['id'] . $sLabel . 'select');
            if (!$this->isUniqueName($aAttributes['name'])) {
                throw new \Exception('Please add unique name attribute for "' . $sLabel . '"');
            }
        }
		$sSelected = $aAttributes['value'];
		unset($aAttributes['value']);
        $this->aData[] = array('label', array('for' => $aAttributes['id']), $sLabel, 'before' => $sBefore);
        $this->aData[] = array('select', $aAttributes, $this->buildOptions($aOptions, $sSelected), $aValidation, 'after' => $sAfter);
    }

    /**
     * Build option array for buildTag()
     * @param array $aOptions
     * @return array
     */
    protected function buildOptions($aOptions, $sSelected = null) {
        $aCleanOtpions = array();
        foreach ($aOptions as $sValue => $mLabel) {
            if (is_array($mLabel)) {
                $aCleanOtpions[] = array('optgroup', array('label' => $sValue), $this->buildOptions($mLabel));
            } else {
				$aAttributes = array('value' => $sValue);
				if ($sSelected !== null && $sValue == $sSelected){
					$aAttributes['selected'] = 'selected';
				}
                $aCleanOtpions[] = array('option', $aAttributes, $mLabel);
            }
        }
        return $aCleanOtpions;
    }

    /**
     * Set an option as selected
     * @param array $aOptions
     * @param string|array $mValues
     */
    protected function setSelectedOptions(&$aOptions, $mValues) {
        if (!is_array($mValues)) {
            $mValues = array($mValues);
        }
        foreach ($aOptions as &$aOption) {
            if ($aOption[0] == 'optgroup') {
                $this->setSelectedOptions($aOption[2], $mValues);
            } elseif (in_array($aOption[1]['value'], $mValues)) {
                $aOption[1]['selected'] = 'selected';
            }
        }
    }

    /**
     * Add submit input to form
     * @param string $sLabel
     * @param array $aAttributes
     * @param string $sBefore
     * @param string $sAfter
     */
    public function addSubmit($sLabel, $aAttributes = array(), $sBefore = '', $sAfter = '') {
        $aAttributes['type'] = 'submit';
        $aAttributes['value'] = $sLabel;
        $this->aData[] = array('input', $aAttributes, 'before' => $sBefore, 'after' => $sAfter);
    }

    /**
     * Check if the given name is unique in this form
     * @param string $sName
     * @return boolean
     */
    public function isUniqueName($sName) {
        if (in_array($sName, $this->aNames)) {
            return false;
        }
        $this->aNames[] = $sName;
        return true;
    }

	/**
     * Get form html
     * @param type $bWithJS
	 * @param \stdClass $oJS
     */
	public function getHTML($bWithJS = true, \stdClass $oJS = null) {
		$this->validate();
        $sOutput = $this->buildTag(array('form', $this->aAttributes, $this->aData));
        if ($bWithJS) {
            $sOutput .= $this->getJavaScriptValidation($oJS);
        }
        return $sOutput;
	}

	/**
     * Dispaly form
     * @param type $bWithJS
	 * @param \stdClass $oJS
     */
    public function display($bWithJS = true, \stdClass $oJS = null) {
        echo $this->getHTML($bWithJS, $oJS);
    }

    /**
     * Build JavaScript validation part for jquery.validate
     * @param \stdClass $oJS
     * @return string
     */
    public function getJavaScriptValidation(\stdClass $oJS = null) {
        if (!is_object($oJS)) {
            $oJS = new \stdClass();
        }
        $oJS->rules = new \stdClass();
        $oJS->messages = new \stdClass();
        foreach ($this->aData as $aElement) {
            if (empty($aElement[3])) {
                continue;
            }
            if (is_array($aElement[3])) {
                foreach ($aElement[3] as $sKey => $sMessage) {
                    $sName = $this->aAttributes['id'] . '[' . $aElement[1]['name'] . ']';
                    if (!is_object($oJS->rules->$sName)) {
                        $oJS->rules->$sName = new \stdClass();
                    }
                    $oJS->rules->$sName->$sKey = true;
                    if (!is_object($oJS->messages->$sName)) {
                        $oJS->messages->$sName = new \stdClass();
                    }
                    $oJS->messages->$sName->$sKey = $sMessage;
                }
            }
        }
        ob_start();
        ?>
        <script type="text/javascript">
            if (jQuery != undefined)
            {
                jQuery(document).ready(function($) {
                    if ($('#<?php echo $this->aAttributes['id']; ?>').validate != undefined)
                    {
                        $('#<?php echo $this->aAttributes['id']; ?>').validate(<?php echo preg_replace('/"function\(\)\{(.*?)\}"/i', 'function(){$1}', json_encode($oJS)); ?>);
                        $('#<?php echo $this->aAttributes['id']; ?> input').on('keyup', function()
                        {
                            $(this).valid();
                        });
                        $('#<?php echo $this->aAttributes['id']; ?> textarea').on('keyup', function()
                        {
                            $(this).valid();
                        });
                        $('#<?php echo $this->aAttributes['id']; ?> select').on('change', function()
                        {
                            $(this).valid();
                        });
                    }
                });
            }
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Get form input
     * @return mixed
     */
    public function getInput($sKey = '') {
        $sType = '_' . strtoupper($this->aAttributes['method']);
        if (!empty($GLOBALS[$sType]) && isset($GLOBALS[$sType][$this->aAttributes['id']])) {
            if (!empty($sKey)) {
				return $GLOBALS[$sType][$this->aAttributes['id']][$sKey];
			}
			return $GLOBALS[$sType][$this->aAttributes['id']];
        }
        return;
    }

    /**
     * Get input by label
     * @param string $sLabel
     * @return mixed
     */
    public function getInputByLabel($sLabel) {
        $sId = '';
        $aInput = $this->getInput();
        if (empty($aInput)) {
            return null;
        }
        foreach ($this->aData as $aData) {
            if ($aData[0] == 'label' && $aData[2] == $sLabel) {
                $sId = $aData[1]['for'];
            }
        }
        if (!empty($sId)) {
            foreach ($this->aData as $aData) {
                if ($aData[1]['id'] == $sId) {
                    return $aInput[$aData[1]['name']];
                }
            }
        }
        return null;
    }

    /**
     * Validate input
     */
    public function validate() {
        $aInput = $this->getInput();
        if (!empty($aInput)) {
            foreach ($this->aData as &$aElement) {
                $bValid = true;
                if (!in_array($aElement[0], array('input', 'select', 'textarea')) || ($aElement[0] == 'input' && in_array($aElement[1]['type'] ,array('radio','submit')))) {
                    continue;
                }
                if (!empty($aElement[1]) && (
                    (isset($aElement[1]['required']) && empty($aInput[$aElement[1]['name']])) //required
                    || (!empty($aElement[1]['pattern']) && !preg_match('/' . $aElement[1]['pattern'] . '/', $aInput[$aElement[1]['name']])) //pattern
                    )) {
                    $bValid = false;
                }
                if (in_array($aElement[0], array('input', 'textarea')) && isset($aInput[$aElement[1]['name']]) && $aElement[1]['type'] != 'password') {
                    $aElement[1]['value'] = $aInput[$aElement[1]['name']];
                    if ($aElement[0] == 'textarea') {
                        $aElement[2] = $aInput[$aElement[1]['name']];
                    }
                } elseif ($aElement[0] == 'select' && isset($aInput[$aElement[1]['name']])) {
                    $this->setSelectedOptions($aElement[2], $aInput[$aElement[1]['name']]);
                }
                $aElement[1]['class'] = (isset($aElement[1]['class']) ? ' ' : '') . ($bValid == true ? 'valid' : 'error');
            }
        }
    }

    /**
     * Build a html tag
     * @param array $aData
     * @return string
     */
    public function buildTag($aData) {
        $sTag = $aData['before'] . '<' . $aData[0];
        if (!empty($aData[1])) {
            foreach ($aData[1] as $sKey => $sValue) {
                if ($sKey == 'name') {
                    $sValue = $this->aAttributes['id'] . '[' . $sValue . ']';
                }
				$sTag .= ' ' . $sKey . '="' . addslashes($sValue) . '"';
            }
        }
        if (is_array($aData[2])) {
            $sTag .= '>';
            foreach ($aData[2] as $aSubData) {
                $sTag .= $this->buildTag($aSubData);
            }
            $sTag .= '</' . $aData[0] . '>';
        } elseif (isset($aData[2]) && !empty($aData[2])) {
            $sTag .= '>' . $aData[2] . '</' . $aData[0] . '>';
        } else {
            $sTag .= ' />';
        }
        $sTag .= $aData['after'];
        return $sTag;
    }
}
