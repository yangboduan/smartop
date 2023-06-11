<?php
/**
 * FunAdmin
 * ============================================================================
 * 版权所有 2017-2028 FunAdmin，并保留所有权利。
 * 网站地址: http://www.FunAdmin.com
 * ----------------------------------------------------------------------------
 * 采用最新Thinkphp6实现
 * ============================================================================
 * Author: yuege
 * Date: 2021/5/11
 * Time: 15:48
 */

namespace app\curd\controlLer;

use app\curd\model\Curd as CurdModel;
use app\common\controller\Backend;
use fun\helper\FileHelper;
use think\App;
use app\common\annotation\ControllerAnnotation;
use app\common\annotation\NodeAnnotation;
use think\facade\Config;
use think\facade\Console;
use think\facade\Db;
use think\helper\Str;

/**
 * @ControllerAnnotation('Index')
 * @package addons\curd\backend\controlLer
 */
class Index extends Backend
{
    protected $layout = '../../backend/view/layout/main';
    protected $systemTable = [
        "fun_addon",
        "fun_addons_curd",
        "fun_admin",
        "fun_admin_log",
        "fun_attach",
        "fun_auth_group",
        "fun_auth_rule",
        "fun_blacklist",
        "fun_config",
        "fun_config_group",
        "fun_field_type",
        "fun_field_verify",
        "fun_languages",
        "fun_member",
        "fun_member_account",
        "fun_member_address",
        "fun_member_group",
        "fun_member_level",
        "fun_member_third",
        "fun_oauth2_client",
        "fun_provinces",
    ];

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->modelClass = new CurdModel();
    }

    public function index()
    {
        if ($this->request->isAjax()) {
            list($this->page, $this->pageSize, $sort, $where) = $this->buildParames();
            $count = $this->modelClass
                ->with(['admin'])
                ->where($where)
                ->count();
            $list = $this->modelClass
                ->with('admin')
                ->where($where)
                ->order($sort)
                ->page($this->page, $this->pageSize)
                ->select();
            $result = ['code' => 0, 'msg' => lang('operation success'), 'data' => $list, 'count' => $count];
            return json($result);
        }
        return view();
    }

    /**
     * @NodeAnnotation('List')
     * @return \think\response\View
     */
    public function add()
    {
        $driver = array_values(array_keys(Config::get('database.connections')));
        $driver = array_combine($driver,$driver);
        $sql = "show tables";
        $list = Db::query($sql);
        $list = array_column($list, 'Tables_in_' . $this->modelClass->get_databasename());
        $table = [];
        foreach ($list as $k => $v) {
            if (!in_array($v, $this->systemTable)) {
                $table[$k] = $v;
            }
        }
        sort($table);
        $controllerList = $this->getController();
        $table = array_combine(array_values($table), $table);
        $list = array_combine(array_values($list), $list);
        if ($this->request->isPost()) {
            $config = get_addons_config(app()->http->getName());
            if(!$config['status']['value']){
                $this->error('功能被禁用了~~');
            }
            $post = $this->request->post();
            if (input('type') == 1) {
                $action = 'curd';
                unset($post['type']);
                $arr = [];
                $curd = '';
                foreach ($post as $k => $v) {
                    if ($k == '__token__') continue;
                    if ($v === '') continue;
                    if ($k === 'joinTable') {
                        $v = str_replace($this->modelClass->get_table_prefix(), '', $v);
                    }
                    if (is_array($v)) {
                        foreach ($v as $kk => $vv) {
                            $arr[] = ['--' . $k, $vv];
                            $curd .= '--' . $k . '=' . $vv . ' ';
                        }
                    } else {
                        $arr[] = ['--' . $k, $v];
                        $curd .= '--' . $k . '=' . $v . ' ';
                    }
                }
                $result = [];
                array_walk_recursive($arr, function ($value) use (&$result) {
                    array_push($result, $value);
                });
                $output = Console::call('curd', $result);
            } else {
                $action = 'menu';
                $controller = input('controllers');
                $controllersArr = explode('@', $controller);
                $app = reset($controllersArr);
                $info = get_addons_info($app);
                if ($info){
                    $arr = explode('/', $controllersArr[1]);
                    $result = [
                        '--controller' , str_replace('.','/',array_pop($arr)),
                        '--addon' , $app,
                        '--app' , '',
                        '--force' , input('force'),
                        '--delete' , input('delete'),
                    ];
                }else{
                    $result = [
                        '--controller' ,  str_replace('.','/',$controller),
                        '--addon' , '',
                        '--app' , $app,
                        '--force', input('force',0),
                        '--delete' , input('delete',0),
                    ];
                }
                $curd = implode(' ',$result);
                $output = Console::call('menu', $result);
            }
            $content = $output->fetch();
            if (strpos($content, 'success')) {
                try {
                    $data['curd'] = "php think $action ". $curd;
                    $data['post_json'] = json_encode($post, JSON_UNESCAPED_UNICODE);
                    $data['admin_id'] = session('admin.id');
                    $save = $this->modelClass->save($data);
                }catch (\Exception $e){
                    $this->error(lang($e->getMessage()));
                }
                $this->success(lang('make success'));
            }
            $this->error($content);
        }
        if ($this->request->isGet() && $this->request->isAjax()) {
            $driver = $this->request->param('driver');
            $type = $this->request->param('type');
            $table = $this->request->param('tablename');
            $database = Config::get('database.connections.'.$driver.'.database');
            $sql = "show tables";
            if($type == 1){//驱动
                $list = Db::connect($driver)->query($sql);
                $tableList = array_column($list, 'Tables_in_' . $database);
                $data = ['table'=>$tableList];
                $this->success('', '', $data);
            }
            if($type == 2 || $type==3){//选择主表
                $jointable = $this->request->param('jointablename');
                $sql = "select COLUMN_NAME from information_schema . columns  where table_name = '" . $table . "' and table_schema = '" . $database . "'";
                $tableField = Db::connect($driver)->query($sql);
                $dataFileds = [];
                foreach ($tableField as $k => $v) {
                    $dataFileds[] = $v['COLUMN_NAME'];
                }
                $sql = "select COLUMN_NAME from information_schema . columns  where table_name = '" . $jointable . "' and table_schema = '" . $database . "'";
                $jointableField = Db::connect($driver)->query($sql);
                $joindataFileds = [];
                foreach ($jointableField as $k => $v) {
                    $joindataFileds[] = $v['COLUMN_NAME'];
                }
                $data = ['fields_table'=>$dataFileds,'fields_join'=>$joindataFileds];
                $this->success('', '', $data);
            }
            if($type == 4){//增加table
                $list = Db::connect($driver)->query($sql);
                $tableList = array_column($list, 'Tables_in_' . $database);
                $sql = "select COLUMN_NAME from information_schema . columns  where table_name = '" . $table . "' and table_schema = '" . $database . "'";
                $tableField = Db::connect($driver)->query($sql);
                $dataFileds = [];
                foreach ($tableField as $k => $v) {
                    $dataFileds[] = $v['COLUMN_NAME'];
                }
                $data = ['table'=>$tableList,'fields'=>$dataFileds];
                $this->success('', '', $data);
            }

        }
        $view = ['table' => $table,'driver'=>$driver, 'list' => $list, 'formData' => [], 'controllerList' => $controllerList];
        return view('add', $view);
    }

    /**
     * 获取所有的控制器
     */
    protected function getController()
    {
        //查询**模块所有控制器
        $backendPath = root_path() . 'app' . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR;
        $addonsPath = root_path() . 'addons' . DIRECTORY_SEPARATOR;
        //配置
        $scanBackendPath = scandir($backendPath);
        $scanAddonsPath = scandir($addonsPath);
        $addonsControllers = [];
        foreach ($scanAddonsPath as $name) {
            if (in_array($name, ['.', '..'])) continue;
            $controllerPath = $addonsPath . $name . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR .$name .DIRECTORY_SEPARATOR. 'controller' . DIRECTORY_SEPARATOR;
            if (is_dir($controllerPath)) {
                foreach (scandir($controllerPath) as $mdir) {
                    if (in_array($mdir, ['.', '..']))  continue;
                    //路由配置文件
                    if (is_file($controllerPath . $mdir)) {
                        $mdir = ucfirst(Str::studly($mdir));
                        $keys =  $name . '@' . str_replace('.php', '', $mdir);
                        $addonsControllers[$keys] = $keys;
                    }else{
                        foreach (scandir($controllerPath) as $mdir) {
                            if (in_array($mdir, ['.', '..']))  continue;
                            //路由配置文件
                            if (is_file($controllerPath . $mdir)) {
                                $mdir = ucfirst(Str::studly($mdir));
                                $keys = $name . '@' .$mdir. str_replace('.php', '', $mdir);
                                $addonsControllers[$keys] =  $keys;
                            }
                        }
                    }
                }
            }
        }
        $controllers = [];
        foreach ($scanBackendPath as $key => $name) {
            if (in_array($name, ['.', '..'])) continue;
            if (is_file($backendPath . $name)) {
                $name = Str::snake($name);
                $keys= 'backend@' . str_replace('.php', '', $name);
                $addonsControllers[$keys] = $keys;
            } else {
                $module_dir = $backendPath . $name . DS;
                foreach (scandir($module_dir) as $mdir) {
                    if (in_array($mdir, ['.', '..'])) continue;
                    if (is_file($backendPath . $name . DS . $mdir)) {
                        $mdir = ucfirst(Str::studly($mdir));
                        $keys = 'backend@' . str_replace('.php', '', $name . '/' . $mdir);
                        $addonsControllers[$keys] = $keys;
                    }
                }
            }
        }
        return array_merge($addonsControllers, $controllers);
    }
}