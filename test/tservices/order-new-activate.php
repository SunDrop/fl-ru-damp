<?php


ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/profiler.php");

//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/mem_storage.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');

//------------------------------------------------------------------------------


$results = array();

//$profiler = new profiler();


//------------------------------------------------------------------------------


//$profiler->start('fill_frl_mem');


//------------------------------------------------------------------------------



$code = TServiceOrderModel::model()->newOrderActivation(array(
    //'user_id' => 33,
    //'uname' => '����',
    //'usurname' => '������',
    //'email' => 'vasya-'.uniqid().'@test.lo',
    'email' => 'ddezinger@yandex.ru',
    'tu_id' => rand(100,1000)
    //'options' => NULL
));


$results['code'] = sprintf('/tu/new-order/%s/',$code);






//$results['test'] = 'test';


/*
$uid = 6;

$account = new account();
$ok = $account->GetInfo($uid, true);
$results['GetInfo'] = (int)$ok;
if($ok)
{
    $sum = -777;
    $scomment = '��� �������� �������� ��� �������';
    $ucomment = '��� �������� �������� ��� "�������" � �������� �����';
    $trs_sum = $sum;
    $op_date = date('c');//, strtotime($_POST['date']));
            
    $results['depositEx'] = $account->depositEx($account->id, $sum, $scomment, $ucomment, 134, $trs_sum, NULL, $op_date);
}

*/



//------------------------------------------------------------------------------

//$profiler->stop('fill_frl_mem');

//------------------------------------------------------------------------------





//------------------------------------------------------------------------------



//------------------------------------------------------------------------------

array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;