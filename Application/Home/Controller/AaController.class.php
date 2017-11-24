<?php
namespace Home\Controller;
use Think\Controller;
class AaController extends Controller {

    function _initialize(){
        //采用Vendor直接引入log4php目录下的Logger文件
        Vendor('log4php.Logger');

        //引入配置文件
        \Logger::configure(HOME_PATH.'/log4php.xml');

        //获取log的对象， 参数是一个名字， 没有时会自动创建， 我传入的是类名
        $log = \Logger::getLogger('IndexController');

        $log->info('来， 以钱赚钱了');
        //$log->warn('来， 以钱赚钱了');
        //$log->debug('来， 以钱赚钱了');
        //$log->error('来， 以钱赚钱了');
        //$log->fatal('来， 以钱赚钱了');
    }

    public function index(){
        //$this->display();
    }
}