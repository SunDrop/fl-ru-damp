<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Class Form_Validate_CostOrAgreementRequired
 *
 * ��������� ��������� ���������� ������ �� �����: "������" � "�� ��������������"
 */
class Form_Validate_CostOrAgreementRequired extends Zend_Validate_Abstract {
    
    const INVALID = 'invalid';

    protected $_messageTemplates = array(
        self::INVALID => '������� ������ ��� �������� ����� "�� ��������������"'
    ); 

    public function isValid($value, $context = array())
    {
        $checkFields = array('budget', 'agreement');
        
        $data = $context['cost'];

        foreach ($checkFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                return true;
            }
        }

        $this->_error(self::INVALID);
        return false;   
    }

}
