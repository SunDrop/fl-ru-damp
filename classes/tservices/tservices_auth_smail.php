<?php

//require_once($_SERVER['DOCUMENT_ROOT'].'/classes/tservices/tservices_helper.php');
//require_once $_SERVER['DOCUMENT_ROOT'].'/classes/freelancer.php';
//require_once $_SERVER['DOCUMENT_ROOT'].'/classes/employer.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/template.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/smail.php';

/**
 * ���������� �������� �����
 */
define('TSERVICES_TPL_MAIL_PATH', $_SERVER['DOCUMENT_ROOT'] . '/templates/mail/tu/');
//define('TSERVICES_TPL_BASE_LAYOUT', 'layout.tpl.php');

/**
 * Class tservices_auth_smail
 * ����� ��� ������ � ��������� ����� ��� ��
 */
class tservices_auth_smail extends smail
{
    protected $is_local = FALSE;

    public function __construct() 
    {
        parent::__construct();

        $server = defined('SERVER')?strtolower(SERVER):'local';
        $this->is_local = ($server == 'local');
    }
    

    /**
     * �������� ����� ��������� ������� ����� ��� �� ������ ��������� 
     * � ����� ��������� ����������� �������� � ���� �� ������ �� ������� ��
     * 
     * @todo: ���� ������ ���������� ����������� �������� �� ����������� ;)
     * 
     * @param string $method
     * @param type $arguments
     * @return boolean
     */
    public function __call($method, $arguments) 
    {
        if($this->is_local) return FALSE;
        
        $method = '_' . $method;
        if(method_exists($this, $method)) 
        {
            call_user_func_array(array($this, $method), $arguments);
        }
        
        return TRUE;
    }

    
    /**
     * ���������� ������ � ����������� ��� ������ ��
     * 
     * @param type $status
     * @return boolean
     */
    public function _orderByNewUser($email, $tService, $code) {
        $this->recipient = $email;
        $this->message = Template::render(
                TSERVICES_TPL_MAIL_PATH . 'auth_order_by_new.tpl.php', 
                array(
                    'smail' => &$this, 
                    'tu_id' => $tService['id'],
                    'tu_title' => $tService['title'],
                    'code' => $code
                )
        );
        return $this->send('text/html');        
    }
    
    /**
     * ���������� ������ � ������������� ��� ������ ��
     * 
     * @param type $status
     * @return boolean
     */
    public function _orderByOldUser($email, $tService, $code) {
        $this->recipient = $email;
        $this->message = Template::render(
                TSERVICES_TPL_MAIL_PATH . 'auth_order_by_old.tpl.php', 
                array(
                    'smail' => &$this, 
                    'tu_id' => $tService['id'],
                    'tu_title' => $tService['title'],
                    'code' => $code
                )
        );
        return $this->send('text/html');        
    }
}