<?php
/** 
 * Validates the contents of a field are numeric.
 * 
 * @author Ironpilot
 * @copyright Copyright (c) 2011, STAPLE CODE
 * 
 * This file is part of the STAPLE Framework.
 * 
 * The STAPLE Framework is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by the 
 * Free Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 * 
 * The STAPLE Framework is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public License for 
 * more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with the STAPLE Framework.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace Staple\Form\Validate;

use Staple\Form\FieldElement;
use Staple\Form\FieldValidator;

class NumericValidator extends FieldValidator
{
	const DEFAULT_ERROR = 'Field value must be numeric.';

	/**
	 * @param  mixed $data
	 * @return  bool
	 * @see Staple_Form_Validator::check()
	 */
	public function check($data)
	{
		if(ctype_digit((string)$data) === true)
		{
			return true;
		}
		else
		{
			$this->addError();
		}
		return false;
	}

	/**
	 * @param string $fieldType
	 * @param FieldElement $field
	 * @return string
	 */
	public function clientJQuery($fieldType, FieldElement $field)
	{
		switch ($fieldType)
		{
			case 'Staple_Form_SelectElement':
				$fieldid = "#{$field->getId()}";
				$valstring = "#{$field->getId()} option:selected";
				break;
			case 'Staple_Form_RadioGroup':
				$fieldid = "input:radio[name={$field->getName()}]";
				$valstring = "input:radio[name={$field->getName()}]:checked";
				break;
			case 'Staple_Form_CheckboxElement':
				return '';
				break;
			default:
				$fieldid = "#{$field->getId()}";
				$valstring = $fieldid;
		}
		
		$script = "\t//Numeric Validator for ".addslashes($field->getLabel())."\n";
		$script .= "\tif(!(/^[0-9]+$/.test($('$valstring').val())))\n";
		$script .= "\t{\n";
		$script .= "\t\terrors.push('".addslashes($field->getLabel()).": \\n{$this->clientJSError()}\\n');\n";
		$script .= "\t\t$('$fieldid').addClass('form_error');\n";
		$script .= "\t}\n";
		$script .= "\telse {\n";
		$script .= "\t\t$('$fieldid').removeClass('form_error');\n";
		$script .= "\t}\n";
		return $script;
	}
}