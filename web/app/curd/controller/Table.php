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
declare (strict_types=1);

namespace app\curd\controller;

use app\common\annotation\NodeAnnotation;
use fun\helper\FileHelper;
use think\db\exception\DbException;
use think\facade\Db;
use think\Request;
use think\App;
use think\facade\View;
use think\facade\Cookie;

/**
 * @ControllerAnnotation (title="计划任务")
 */
class Table extends \app\common\controller\Backend
{
    protected $pageSize = 15;
    protected $layout = '../../backend/view/layout/main';
    protected $prefix = '../../backend/view/layout/main';

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->setSearchFields = [];
        $this->prefix = config('database.connections.mysql.prefix');
        View::assign('engineList', ['InnoDB' => 'InnoDB', 'MyISAM' => 'MyISAM']);
        View::assign('indexList', ['PRI'=>'主键索引','UNI'=>'唯一索引','MUL'=>'普通索引']);
        View::assign('engineList', ['InnoDB' => 'InnoDB', 'MyISAM' => 'MyISAM']);
        View::assign('id', input('id'));
        View::assign('datatypeList', ['tinyint'=>'小整数(tinyint)',
                                            'int'=>'整数(int)',
                                            'bigint'=>'极大整数(bigint)',
                                            'char'=>'字符串(char)',
                                            'varchar'=>'变长字符串(varchar)',
                                            'text'=>'文本(text)',
                                            'mediumtext'=>'中文本(mediumtext)',
                                            'longtext'=>'大文本(longtext)',
                                            'float'=>'单精度浮点数(float)',
                                            'double'=>'双精度浮点数(double)',
                                            'decimal'=>'定点型(decimal)',
                                            'date'=>'日期(date)',
                                            'time'=>'时间(time)',
                                            'datetime'=>'日期时间(datetime)',
                                            'year'=>'年(year)',
                                            'timestamp'=>'时间戳(timestamp)',
                                            'set'=>'集合(set)',
                                            'enum'=>'枚举(enum)',
                                            'json'=>'json']);

    }


    /**
     *更新数据库表缓存，刷新列
     */
    public function dataclear()
    {
//        $sql = 'SET GLOBAL information_schema_stats_expiry=0;';
//        Db::execute($sql);
//        $sql = 'SET @@GLOBAL.information_schema_stats_expiry=0;';
//        Db::execute($sql);
//        $sql = 'SET SESSION information_schema_stats_expiry=0;';
//        Db::execute($sql);
        $sql = 'SET @@SESSION.information_schema_stats_expiry=0;';
        Db::execute($sql);
        $this->success('成功');
    }


    /**
     *数据库列表
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            if ($this->request->param('selectFields')) {
                $this->selectList();
            }
            list($this->page, $this->pageSize, $sort, $where) = $this->buildParames();
            $where[] = ['`TABLE_SCHEMA`', '=', config('database.connections.mysql.database')];
            $wheres = '';
            foreach ($where as $v) {
                $wheres .= (empty($wheres) ? '' : ' and ') . $v[0] . ' ' . $v[1] . ' \'' . $v[2] . '\'';
            }
            $sql = 'SELECT  `TABLE_NAME` as `id`,`TABLE_NAME`,`ENGINE`,`TABLE_ROWS`,`TABLE_COMMENT`,`CREATE_TIME`,`UPDATE_TIME`,`TABLE_COLLATION` FROM information_schema.`TABLES` WHERE ' . $wheres .' ORDER BY `CREATE_TIME` DESC';
            $list = Db::query($sql);
            $result = ['code' => 0, 'msg' => lang('Get Data Success'), 'data' => $list, 'count' => count($list)];
            return json($result);
        }
        return view();
    }


    /**
     * @NodeAnnotation (title="add")
     * @return \think\response\View
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            try {
                $tablename = $post['TABLE_NAME'];
                $engine = $post['ENGINE'];
                $comment = $post['TABLE_COMMENT'];
                $collation = $post['TABLE_COLLATION']?:'utf8mb4_unicode_ci';
                if (empty($engine) || empty($comment)) $this->error(lang('关键字段没有值'));
                $tableSql = "SHOW TABLES LIKE '{$this->prefix}{$tablename}';";
                $tableResult = Db::query($tableSql);
                if(!empty($tableResult)){
                    $this->error(lang('表已经存在'));
                }
                $fields = $post['field'];
                $sql = '';
                $index = '';
                foreach ($fields as $key=>$field) {
                    if(!$field) continue;
                    $comment = $post['comment'][$key]?:$field;
                    if($post['index'][$key]=='PRI'){
                        $sql.= "`{$field}` INT unsigned NOT NULL AUTO_INCREMENT COMMENT '{$comment}' ,";
                    }else{
                        $length = $post['length'][$key];
                        switch ($post['type'][$key]){
                            case 'varchar':
                                if(!$length)$length = 255;
                                break;
                            case 'char':
                                if($length)$length = 50;
                                break;
                            case 'decimal':
                            case 'double':
                            case 'float':
                                if(!$length)$length = '10,2';
                                break;
                            case 'tinyint':
                                if(!$length)$length = 1;
                                break;
                            case 'int':
                                if(!$length)$length = 10;
                                break;
                            case 'set':
                            case 'enum':
                                if(!$length) $this->error('set或enum 类型需要设置值');
                                $lengthArr = explode(',',$length);
                                $newlength = '';
                                foreach ($lengthArr as $item) {
                                    $item = trim($item);
                                    $newlength.= "'{$item}',";
                                }
                                $length = trim($newlength,',');
                                break;
                            case 'bigint':
                            case 'text':
                            case 'mediumtext':
                            case 'longtext':
                            case 'double':
                            case 'float':
                            case 'date':
                            case 'time':
                            case 'datetime':
                            case 'year':
                            case 'timestamp':
                            case 'json':
                                $length = '';
                                break;
                        }
                        $default = $post['default'][$key];
                        $require = $post['require'][$key];
                        if($require==1 && !$default){
                            $default = "DEFAULT ''";
                        }else{
                            $default = $default?"DEFAULT {$default}":"DEFAULT null";
                        }
                        $require = $require?"NOT NULL":" ";
                        if($length) $length = "({$length})";
                        $sql .= "`{$field}` {$post['type'][$key]}{$length} {$require} {$default} COMMENT '{$comment}',";
                    }

                    if($post['index'][$key]=='PRI'){
                        $index.="PRIMARY KEY (`{$field}`),UNIQUE KEY `{$field}` (`{$field}`),";
                    }
                    if($post['index'][$key]=='UNI'){
                        $index.="UNIQUE KEY `{$field}` (`{$field}`),";
                    }
                    if($post['index'][$key]=='MUL'){
                        $index.="KEY '{$field}' (`{$field}`),";
                    }
                }
                $index = trim($index,',');
                $dbsql = <<<EOF
CREATE TABLE IF NOT EXISTS `{$this->prefix}{$tablename}`(
{$sql}
`create_time` int unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
`update_time` int DEFAULT '0' COMMENT '跟新时间',
`delete_time` int DEFAULT '0' COMMENT '删除时间',
{$index}
)ENGINE={$engine} DEFAULT CHARSET=utf8mb4 COLLATE={$collation} COMMENT='{$comment}';
EOF;
                $save = Db::query($dbsql);
            } catch (DbException $e) {
                $this->error(lang($e->getMessage()));
            }
            $this->success('创建成功');
        }
        $view = [
            'formData' => '',
            'title' => lang('Add'),
        ];
        return view('add', $view);
    }

    /**
     * @NodeAnnotation("delete")
     * @return void
     */
    public function delete(){
        $id = $this->request->get('id', '');
        $sql = "DROP TABLE IF EXISTS {$id}";
        try {
            $res = Db::query($sql);
        }catch (DbException $e) {
            $this->error(lang($e->getMessage()));
        }
        $this->success('删除成功');
    }
    /**
     * 表格数据查看
     * @NodeAnnotation(title="edit")
     * @return \think\response\View
     */
    public function list()
    {

        $id = $this->request->get('id', '');
        if (!empty($id)) {
            cookie('tablename', $id);
        }
        if($this->request->isAjax()){
            $id = Cookie::get('tablename');
            if (empty($id)) $this->error('没有数据表');
            if ($this->request->param('selectFields')) {
                $this->selectList();
            }
            list($this->page, $this->pageSize, $sort, $where) = $this->buildParames();//添加返回条件，陈俊威
            $count = Db::table($id)
                ->where($where)
                ->count();
            $list = Db::table($id)
                ->where($where)
                ->order($sort)
                ->page($this->page, $this->pageSize)
                ->select();
            $result = ['code' => 0, 'msg' => lang('Get Data Success'), 'data' => $list, 'count' => $count];
            return json($result);
        }
        $result = Db::query("SHOW FULL COLUMNS FROM $id");
        $width = ['int' => '90', 'varchar' => '200', 'date' => '120', 'datetime' => '180'];
        foreach ($result as $k => $v) {
            $type = explode('(', $v['Type'])[0];
            if (str_replace('_time', '', $v['Field']) != $v['Field']) $type = 'datetime';
            $result[$k]['width'] = $width[$type] ?? '100';
        }
        View::assign('result', $result);
        return view('', ['id' => Cookie::get('tablename')]);
    }



    /**
     * @NodeAnnotation('backup')
     * @return array
     */
    public function backup()
    {
        $tableName = $this->request->get('id');
        $base = $this->getDatabase();
        if (empty($tableName)) {
            $res = Db::table('information_schema.`TABLES`')->where('TABLE_SCHEMA', $base)->field('TABLE_NAME')->select();
            if (empty($res)) {
                return [false, '没有表备份'];
            }
            $alltabls = array_column($res->toArray(), 'TABLE_NAME');
        } else {
            $alltabls = is_array($tableName) ? $tableName : explode(',', $tableName);
            foreach ($alltabls as $k => $v) {
                $alltabls[$k] = (str_replace($this->prefix, '', $v) == $v) ? $this->prefix . $v : $v;
            }
        }
        $nobeifne = [$this->prefix . 'asn_file']; //不备份的表;
        $strstr = "/*\n*备份时间：" . date('Y-m-d H:i:s') . "\n*/\n\n";
        $file = '' . $base . (count($alltabls) == 1 ? '_(' . $alltabls[0] . ')' : '') . '_' . date('Y.m.d_H.i.s') . '.sql';//文件名称
        $filepath = './data';//文件路径
        if (!is_dir($filepath)) {
            FileHelper::mkdirs($filepath);
        }
        $filepath .= $file . '';
        $bo = false;
        foreach ($alltabls as $tabs) {
            if (in_array($tabs, $nobeifne)) continue;
            $strstr .= "\nDROP TABLE IF EXISTS `$tabs`;\n";
            $sqla = Db::query('show create table `' . $tabs . '`');
            $strstr .= "" . $sqla[0]['Create Table'] . ";\n";
            $count = Db::table($tabs)->count();
            for ($p = 0, $limit = 10000; $p * $limit < $count; $p++) {//分段行数大小，防止内存溢出，如果php.ini里面的memory_limit设置的足够大，$limit可以直接改成无限大
                $rows = Db::table($tabs)->limit($p * $limit, $limit)->select();
                foreach ($rows as $k => $rs) {
                    $vstr = '';
                    foreach ($rs as $k1 => $v1) {
                        $v1 = ($v1 == null) ? 'null' : "'$v1'";
                        $vstr .= ",$v1";
                    }
                    $strstr .= "INSERT INTO `$tabs` VALUES(" . substr($vstr, 1) . ");\n";
                }
                $strstr .= "\n";
                @$file = fopen($filepath, 'a');
                if ($file) {
                    $bo = true;
                    if ($strstr) $bo = fwrite($file, $strstr);
                    fclose($file);
                }
                $strstr = "\n";
            }
            if ($count == 0) {
                @$file = fopen($filepath, 'a');
                if ($file) {
                    $bo = true;
                    if ($strstr) $bo = fwrite($file, $strstr);
                    fclose($file);
                }
                $strstr = "\n";
            }
        }
        $bo ? $this->success(lang('备份成功')) : $this->error(lang('备份失败'));
    }


    /**
     * 删除历史的备份文件，避免文件过多导致磁盘不足
     * @param int $num
     */
    public function delfile()
    {
        $filePath = './data/';//目录
        $ha = scandir($filePath);//扫描文件
        $daynum = '-20 day';//设置删除超过时长
        $num = 30;//设置保留文件个数
        $now = date('Y.m.d', strtotime($daynum));//设置删除时间段前的数据
        $base = $this->getDatabase();
        $filename = $base . '_' . $now;
        foreach ($ha as $k => $v) {
            if (strpos($v, '.sql') !== false) {//压缩文件
                $zip = new \ZipArchive();
                $zip->open(($filePath . @str_replace('sql', 'zip', $v)), \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
                $zip->addFile($v);
                $zip->close();
            }
            if ($v < $filename && strpos($v, $base) !== false && strpos($v, '.zip') === true) {
                unlink($filePath . $v);
            } else if ($k > $num && strpos($v, '.zip') === true) {//超过30个文件，开始删除超出的
                unlink($filePath . $v);
            }
        }
        $this->success(lang('删除成功')) ;
    }

    /**
     * @NodeAnnotation (title="字段列表")
     * @return \think\response\View
     */
    public function fieldlist()
    {
        $id = $this->request->param('id');
        $base = $this->getDatabase();
        $list = $this->getFieldList($id);
        if (empty($list)) $this->error(lang('Data is not exist'));
        if ($this->request->isAjax()) {
            $sql = 'SELECT  CONCAT("' . $id . '","|",`COLUMN_NAME`) id,`COLUMN_NAME`,`COLUMN_DEFAULT`,`COLUMN_KEY`,`IS_NULLABLE`,`DATA_TYPE`,`CHARACTER_MAXIMUM_LENGTH`,`COLUMN_COMMENT`,`COLUMN_TYPE` FROM information_schema.`COLUMNS` WHERE `TABLE_SCHEMA` = \'' . $base . "' and `TABLE_NAME` = '$id'";
            $list = Db::query($sql);
            foreach ($list as $key => $value){
                if(in_array($value['DATA_TYPE'],['enum','set'])){
                    $length = str_replace($value['DATA_TYPE'],'',$value['COLUMN_TYPE']);
                    $length = str_replace("'",'',$length);
                    $length = str_replace(")",'',$length);
                    $length = str_replace('(','',$length);
                    $list[$key]['CHARACTER_MAXIMUM_LENGTH'] = $length;
                }
                if(in_array($value['DATA_TYPE'],['json','text','date','time','datetime','year','mediumtext','longtext','bigint'])){
                    $list[$key]['CHARACTER_MAXIMUM_LENGTH'] = '';
                }
            }
            $result = ['code' => 0, 'msg' => lang('operation success'), 'data' => $list, 'count' => count($list)];
            return json($result);
        }
        $fieldList = [];
        foreach ($list as $item) {
            $fieldList[$item['COLUMN_NAME']] = '排在'.$item['COLUMN_NAME'].'之后';
        }
        $view = ['formData' => $list[0], 'title' => lang('Add'),'id'=>$id,'fieldList'=>$fieldList];
        return view('', $view);
    }


    /**
     * @NodeAnnotation (title="添加字段")
     * @return \think\response\View
     */
    public function addfield()
    {
        $id = $this->request->param('id');
        if ($this->request->isPost()) {
            $post = $this->request->post();
            try {
                if(!$id) $this->error('表格错误');
                $define = '';
                switch ($post['type']){
                    case 'char':
                    case 'varchar':
                        $length = $post['length']?:255;
                        $define.= "{$post['type']}"."({$length}) ";
                        break;
                    case 'tinyint':
                    case 'int':
                        $length = $post['length'];
                        if($length){
                            $define.= "{$post['type']}"."({$length})";
                        }else{
                            $define.= "{$post['type']} ";
                        }
                        break;
                    case 'decimal':
                    case 'double':
                    case 'float':
                        $length = $post['length']?str_replace('.',',',$post['length']):"10,2";
                        $define.= "{$post['type']}"."({$length}) ";
                        break;
                    default:
                        $define.= "{$post['type']} ";
                        break;
                }
                if(!$post['required']){
                    $define.= ' NULL ';
                }else{
                    $define.= ' NOT NULL ';
                }
                if($post['default']){
                    $define.= " DEFAULT '{$post['default']}' ";
                }

                $post['comment'] = $post['comment']?:$post['field'];
                if($post['extra']){
                    $arr = array_filter(explode("\n",str_replace("\r",'',$post['extra'])));
                    $remark = '';
                    foreach ($arr as $key=>$item) {
                        $remark.= $key.':'.$item.',';
                    }
                    $post['comment'] = $post['comment'].'('.trim($remark,',').')';
                }
                $after='';
                if($post['after']){
                    $after.= " AFTER `{$post['after']}`";
                }
                $sql = <<<EOF
ALTER TABLE `{$id}` ADD `{$post['field']}` {$define} COMMENT '{$post['comment']}' $after;
EOF;
                Db::query($sql);
            } catch (\Exception $e) {
                $this->error(lang($e->getMessage()));
            }
            $this->success(lang('operation success'));
        }
        $view = ['formData' =>'','fieldList' => $this->getFieldList($id), 'title' => lang('Add'),'id'=>$id];
        return view('addfield', $view);
    }

    /**
     * @NodeAnnotation('修改字段')
     * @return \think\response\View
     */
    public function savefield()
    {
        $id = input('id');
        $comment = input('COLUMN_COMMENT');
        $length = input('CHARACTER_MAXIMUM_LENGTH');
        $type = input('DATA_TYPE');
        $require = input('IS_NULLABLE');
        $index = input('COLUMN_KEY');
        $default = input('COLUMN_DEFAULT');
        $field = input('COLUMN_NAME');
        $arr = explode('|',$id);
        $define = '';
        switch ($type){
            case 'char':
            case 'varchar':
                $length = $length?:255;
                $define.= "{$type}"."({$length}) ";
                break;
            case 'tinyint':
            case 'int':
                if($length){
                    $define.= "{$type}"."({$length})";
                }else{
                    $define.= "{$type} ";
                }
                break;
            case 'decimal':
            case 'double':
            case 'float':
                $length = $length?str_replace('.',',',$length):"10,2";
                $define.= "{$type}"."({$length}) ";
                break;
            case 'set':
            case 'enum':
                if(!$length) $this->error('set或enum 类型需要设置值');
                $lengthArr = explode(',',$length);
                $newlength = '';
                foreach ($lengthArr as $item) {
                    $item = trim($item);
                    $newlength.= "'{$item}',";
                }
                $newlength = trim($newlength,',');
                $define .= "{$type}"."({$newlength}) ";;
                break;
            default:
                $define.= "{$type} ";
                break;
        }
        if(!$require){
            $define.= ' NULL ';
        }else{
            $define.= ' NOT NULL ';
        }
        if($default){
            $define.= " DEFAULT '{$default}' ";
        }
        $comment = $comment ?:$field;
        $indexsql = '';
        $base = $this->getDatabase();
        $searchSql = "show index from `{$base}`.`{$arr[0]}`  where column_name like '{$field}'";
        $res = Db::query($searchSql);
        if(!empty($res)){
            if($res[0]['Key_name']=='PRIMARY'){
                $dropSql = "ALTER TABLE `{$arr[0]}` DROP PRIMARY KEY;";
            }else{
                $dropSql = "ALTER TABLE `{$arr[0]}` DROP INDEX `{$res[0]['Key_name']}`;";
            }
            Db::query($dropSql);
        }
        if($index){
            switch ($index){
                case 'PRI':
                    $index = 'PRIMARY';
                    break;
                case 'UNI':
                    $index = 'UNIQUE';
                    break;
                case 'MUL':
                    $index = 'INDEX ';
                    break;

            }
            $indexsql = "ALTER TABLE `{$base}`.`{$arr[0]}` ADD {$index} `{$field}` (`{$field}`);";
        }
        $after='';
        $AFTER = input('AFTER');
        if($AFTER){
            $after.= " AFTER `{$AFTER}`";
        }
        if ($id) {
            try {
                $sql = <<<EOF
ALTER TABLE `{$arr[0]}` CHANGE `{$field}` `{$field}` {$define} COMMENT'{$comment}' $after;
EOF;
                if($indexsql){
                    Db::query($indexsql);
                }
                Db::query($sql);
            } catch (DbException $e) {
                $this->error(lang($e->getMessage()));
            }
            $this->success(lang('Modify success'));
        } else {
            $this->error(lang('Invalid data'));
        }
    }

    /**
     * @NodeAnnotation('删除字段')
     * @return \think\response\View
     */
    public function delfield()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            try {
                $id = $this->request->param('id');
                if(!$id)$this->error('表格错误');
                $arr = explode('|',$id);
                $sql = <<<EOF
ALTER TABLE `{$arr[0]}` DROP `{$arr[1]}`;
EOF;
                Db::query($sql);
            } catch (DbException $e) {
                $this->error(lang($e->getMessage()));
            }
            $this->success(lang('delete success'));
        }
        $view = [
            'formData' => '',
            'title' => lang('Add'),
        ];
        return view('', $view);
    }


    /**
     * @NodeAnnotation(title="编辑表格")
     * @return \think\response\View
     */
    public function edit()
    {
        $id = $this->request->param('id');
        $base = $this->getDatabase();
        $sql = 'SELECT  `TABLE_NAME` as `id`,`TABLE_NAME`,`ENGINE`,`TABLE_ROWS`,`TABLE_COMMENT`,`CREATE_TIME`,`UPDATE_TIME`,`TABLE_COLLATION` FROM information_schema.`TABLES` WHERE `TABLE_SCHEMA` = \'' . $base . "' and `TABLE_NAME` = '$id'";
        $list = Db::query($sql);
        if (empty($list)) $this->error(lang('Data is not exist'));
        if ($this->request->isPost()) {
            $post = $this->request->post();
            foreach ($post as $k => $v) {
                if (is_array($v)) {
                    $post[$k] = implode(',', $v);
                }
            }
            try {
                $tablename = $post['TABLE_NAME'];
                $engine = $post['ENGINE'];
                $comment = $post['TABLE_COMMENT'];
                $collation = $post['TABLE_COLLATION'];
                if (empty($engine) || empty($comment)) $this->error(lang('关键字段没有值'));
                $sql = "ALTER TABLE `$base`.`$tablename` ENGINE = '$engine',COMMENT = '$comment', COLLATE='$collation' ;";
                $save = Db::query($sql);
            } catch (DbException $e) {
                $this->error(lang($e->getMessage()));
            }
            $this->success(lang('operation success'));
        }
        $view = ['formData' => $list[0], 'title' => lang('Add'),];
        return view('edit', $view);
    }

    protected function getDatabase(){
        return  config('database.connections.mysql.database');
    }
    protected function getFieldList($id){
        $base = $this->getDatabase();
        $sql = 'SELECT  `COLUMN_NAME`,`IS_NULLABLE`,`DATA_TYPE`,`CHARACTER_MAXIMUM_LENGTH`,`COLUMN_TYPE`,`COLUMN_COMMENT` FROM information_schema.`COLUMNS` WHERE `TABLE_SCHEMA` = \'' . $base . "' and `TABLE_NAME` = '$id'";
        $list = Db::query($sql);
        return $list;
    }
}

