<?php

namespace simplerest\core\api\v1;

use simplerest\core\interfaces\IAuth;
use simplerest\libs\Factory;
use simplerest\libs\Arrays;
use simplerest\libs\DB;
use simplerest\libs\Debug;
use simplerest\libs\Url;
use simplerest\libs\Validator;
use simplerest\models\FolderPermissionsModel;
use simplerest\models\FolderOtherPermissionsModel;
use simplerest\models\FoldersModel;
use simplerest\core\api\v1\ResourceController;
use simplerest\core\exceptions\InvalidValidationException;


abstract class ApiController extends ResourceController
{
    static protected $folder_field;
    static protected $soft_delete = true;

    protected $is_listable;
    protected $is_retrievable;
    protected $callable = [];
    protected $config;
    protected $impersonated_by;
    protected $conn;
    protected $modelName;
    protected $model_table;

    protected $id;
    protected $folder;


    function __construct(array $headers = []) 
    {        
        parent::__construct();

        if ($this->config['debug_mode'] == false)
            set_exception_handler([$this, 'exception_handler']);
        
        if (preg_match('/([A-Z][a-z0-9_]+[A-Z]*[a-z0-9_]*[A-Z]*[a-z0-9_]*[A-Z]*[a-z0-9_]*)/', get_called_class(), $matchs)){
            $this->modelName = $matchs[1] . 'Model';
            $this->model_table = strtolower($matchs[1]);
        }   

        $perms = $this->getPermissions($this->model_table);
        
        if ($perms !== NULL)
        {
            // individual permissions *replaces* role permissions
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET': 
                    $this->is_listable     = ($perms & 16) AND 1;
                    $this->is_retrievable  = ($perms & 8 ) AND 1;

                    if ($this->is_listable || $this->is_retrievable){
                        $this->callable = ['get'];
                    }
                break;
                
                case 'POST': 
                    if (($perms & 4 ) AND 1){
                        $this->callable = ['get'];
                    }
                break;    

                case 'PUT':
                    if (($perms & 2 ) AND 1){
                        $this->callable = ['put'];
                    }
				break;
                      
                case 'PATCH':
                    if (($perms & 2 ) AND 1){
                        $this->callable = ['patch'];
                    }                      
                break;    

                case 'DELETE': 
                    if (($perms & 1 ) AND 1){
                        $this->callable = ['delete'];
                    }                    
                break;
            } 
 
        }else{

            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    if ($this->acl->hasSpecialPermission('read_all', $this->roles)){
                        $this->callable  = ['get'];
                        $this->is_listable    = true;
                        $this->is_retrievable = true;
                    } else {
                        if ($this->acl->hasResourcePermission('show', $this->roles, $this->model_table)){
                            $this->callable  = ['get'];
                            $this->is_retrievable = true;
                        }

                        if ($this->acl->hasResourcePermission('list', $this->roles, $this->model_table)){
                            $this->callable  = ['get'];
                            $this->is_listable    = true;
                        }
                    }  
                break;
                
                case 'POST':
                    if ($this->acl->hasSpecialPermission('write_all', $this->roles)){
                        $this->callable  = ['post', 'put', 'patch', 'delete'];;
                    } else {
                        if ($this->acl->hasResourcePermission('create', $this->roles, $this->model_table)){
                            $this->callable  = ['post'];;
                        }
                    }  
                break;    

                case 'PUT':
                    if ($this->acl->hasSpecialPermission('write_all', $this->roles)){
                        $this->callable  = ['post', 'put', 'patch', 'delete'];;
                    } else {
                        if ($this->acl->hasResourcePermission('update', $this->roles, $this->model_table)){
                            $this->callable  = ['put'];;
                        }
                    }  
                break;

                case 'PATCH':
                    if ($this->acl->hasSpecialPermission('write_all', $this->roles)){
                        $this->callable  = ['post', 'put', 'patch', 'delete'];;
                    } else {
                        if ($this->acl->hasResourcePermission('update', $this->roles, $this->model_table)){
                            $this->callable  = ['patch'];;
                        }
                    }  
                break;    

                case 'DELETE':
                    if ($this->acl->hasSpecialPermission('write_all', $this->roles)){
                        $this->callable  = ['post', 'put', 'patch', 'delete'];;
                    } else {
                        if ($this->acl->hasResourcePermission('delete', $this->roles, $this->model_table)){
                            $this->callable  = ['delete'];;
                        }
                    }  
                break;
            } 
              
        }
    

        $this->impersonated_by = $this->auth->impersonated_by ?? null;

    
        //Debug::dump($perms, 'permissions');
        //Debug::dump($this->roles, 'roles');    
        //Debug::dump($this->is_listable, 'is_listable?');
        //Debug::dump($this->is_retrievable, 'is_retrievable?');
        //Debug::dump($this->callable, 'callables');
        //Debug::dump($this->impersonated_by, 'impersonated_by);
        //exit;
        
        if (empty($this->callable))
            Factory::response()->sendError("Forbidden", 403, "Operation is not permited");

        $this->callable = array_merge($this->callable,['head','options']);

        // headers
        $headers = array_merge($headers, ['Access-Control-Allow-Methods' => implode(',',array_map( function ($e){ return strtoupper($e); },$this->callable )) ]);
        $this->setheaders($headers);            	        
 
    }
    
    /**
     * setheaders
     * mover a Response *
     *
     * @param  mixed $headers
     *
     * @return void
     */
    private function setheaders(array $headers = []) {
        header('Access-Control-Allow-Credentials: True');
        header('Access-Control-Allow-Headers: Origin,Content-Type,X-Auth-Token,AccountKey,X-requested-with,Authorization,Accept, Client-Security-Token,Host,Date,Cookie,Cookie2'); 
        header('Access-Control-Allow-Methods: GET,POST,PUT,PATCH,POST,DELETE,OPTIONS'); 
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=UTF-8');

        /*
        $headers = array_merge($this->default_headers, $headers);     

        foreach ($headers as $k => $val){
            if (empty($val))
                continue;
            
            header("${k}:$val");
        }
        */
    }

    /**
     * exception_handler
     *
     * @param  mixed $e
     *
     * @return void
     */
    function exception_handler($e) {
        Factory::response()->sendError($e->getMessage());
    }

    
    /**
     * head
     * discard conentent (body)
     * 
     * @param  mixed $id
     *
     * @return void
     */
    function head(int $id = null) {
        if (method_exists($this,'get')){
            ob_start();
            $this->get($id);
            ob_end_clean();
        }
    }

    /**
     * options
     *
     * @return void
     */
    function options() {
    }
 
  
    /**
     * get
     *
     * @param  mixed $id
     *
     * @return void
     */
    function get($id = null) {
        global $api_version;

        $_get    = Factory::request()->getQuery();   
        
        $this->id = $id;
        $this->folder = $folder  = Arrays::shift($_get,'folder');

        // Si el rol no le permite a un usuario ver un recurso aunque se le comparta un folder tampoco podrá listarlo

        if (!$this->acl->hasSpecialPermission('read_all', $this->roles)) {

            if ($id == null && !$this->is_listable)
                Factory::response()->sendError('Unauthorized', 403, "You are not allowed to list");    

            if ($id != null && !$this->is_retrievable)
                Factory::response()->sendError('Unauthorized', 401, "You are not allowed to retrieve");  

        }

        // event hook
        $this->onGetting($id);

        try {            

            $this->conn = $conn = DB::getConnection();

            $model    = 'simplerest\\models\\'.$this->modelName;
            $instance = (new $model($conn))->setFetchMode('ASSOC'); 
            
            $data    = [];


            if ($id == null) {            
                foreach (['created_by', 'updated_by', 'deleted_by', 'belongs_to', 'user_id'] as $f){
                    if (isset($_get[$f])){
                        if ($_get[$f] == 'me')
                            $_get[$f] = $this->uid;
                        elseif (is_array($_get[$f])){
                            foreach ($_get[$f] as $op => $idx){                            
                                if ($idx == 'me'){
                                    $_get[$f][$op] = $this->uid;
                                }else{      
                                    $p = explode(',',$idx);
                                    if (count($p)>1){
                                    foreach ($p as $ix => $idy){
                                        if ($idy == 'me')
                                            $p[$ix] = $this->uid;
                                        }
                                    }
                                    $_get[$f][$op] = implode(',',$p);
                                }
                            }
                        }else{
                            $p = explode(',',$_get[$f]);
                            if (count($p)>1){
                            foreach ($p as $ix => $idx){
                                if ($idx == 'me')
                                    $p[$ix] = $this->uid;
                                }
                            }
                            $_get[$f] = implode(',',$p);
                        }
                    }
                }
        
                //var_export($_get);
                //exit; ////

                if (isset($_get['created_by']) && $_get['created_by'] == 'me')
                    $_get['created_by'] = $this->uid;

                foreach ($_get as $f => $v){
                    if (!is_array($v) && strpos($v, ',')=== false)
                        $data[$f] = $v;
                } 
            }

            //var_export($_get);
            //exit;
                
            $owned = $instance->inSchema(['belongs_to']);

            $_q      = Arrays::shift($_get,'q'); /* search */
            
            
            $fields  = Arrays::shift($_get,'fields');
            $fields  = $fields != NULL ? explode(',',$fields) : NULL;

            $properties = $instance->getProperties();
            
            foreach ((array) $fields as $field){
                if (!in_array($field,$properties))
                    Factory::response()->sendError("Unknown field '$field'", 400);
            }

            $exclude = Arrays::shift($_get,'exclude');
            $exclude = $exclude != NULL ? explode(',',$exclude) : NULL;

            foreach ((array) $exclude as $field){
                if (!in_array($field,$properties))
                    Factory::response()->sendError("Unknown field '$field' in exclude", 400);
            }

            $ignored = [];

            if ($exclude != null)
                $instance->hide($exclude);
                       
            $pretty  = Arrays::shift($_get,'pretty');

            foreach ($_get as $key => $val){
                if ($val == 'NULL' || $val == 'null'){
                    $_get[$key] = NULL;
                }               
            }

            //var_dump($_get);
            //exit;

            if ($folder !== null)
            {
                // event hook
                $this->onGettingFolderBeforeCheck($id, $folder);  

                $f = DB::table('folders')->setFetchMode('ASSOC');
                $f_rows = $f->where(['id' => $folder])->get();
        
                if (count($f_rows) == 0 || $f_rows[0]['tb'] != $this->model_table)
                    Factory::response()->sendError('Folder not found', 404);  
        
                $folder_access = $this->acl->hasSpecialPermission('read_all_folders', $this->roles) || $f_rows[0]['belongs_to'] == $this->uid  || $this->hasFolderPermission($folder, 'r');   

                if (!$folder_access)
                    Factory::response()->sendError("Forbidden", 403, "You don't have permission for the folder $folder");
            }

            if ($id != null)
            {
                $_get = [
                    ['id', $id]
                ];  

                if (empty($folder)){               
                    // root, by id          
                         
                    if ($this->isGuest()){                        
                        if ($instance->inSchema(['guest_access'])){
                            $_get[] = ['guest_access', 1];
                        } elseif (!empty(static::$folder_field)) {
                            $_get[] = [static::$folder_field, NULL, 'IS'];
                        } 
                                                
                    } else {
                        if ($owned && !$this->acl->hasSpecialPermission('read_all', $this->roles))
                            $_get[] = ['belongs_to', $this->uid];
                    }
                       
                    
                }else{
                    // folder, by id
                    if (empty(static::$folder_field))
                        Factory::response()->sendError("Forbidden", 403, "folder_field is undefined");    
                                           
                    $_get[] = [static::$folder_field, $f_rows[0]['name']];
                    $_get[] = ['belongs_to', $f_rows[0]['belongs_to']];
                }

                $rows = $instance->where($_get)->get($fields); 
                if (empty($rows))
                    Factory::response()->sendError('Not found', 404, $id != null ? "Registry with id=$id in table '{$this->model_table}' was not found" : '');
                else
                    Factory::response()->send($rows[0]);
            }else{    
                // "list
                
                $props    = Arrays::shift($_get,'props');
                $group_by = Arrays::shift($_get,'groupBy');
                $having   = Arrays::shift($_get,'having');
             
                $get_limit = function(&$limit){
                    if ($limit == NULL)
                        $limit = min($this->config['paginator']['max_limit'], $this->config['paginator']['default_limit']);
                    else{
                        if ($limit !=0)
                            $limit = min($limit, $this->config['paginator']['max_limit']);
                        else    
                            $limit = $this->config['paginator']['max_limit'];
                    } 
                };
   
                $page  = Arrays::shift($_get,'page');
                $page_size = Arrays::shift($_get,'pageSize');

                if ($page != null)
                    $page = (int) $page;

                if ($page_size  != null)
                    $page_size  = (int) $page_size;

                if ($page != NULL || $page_size != NULL){
                    $get_limit($page_size);

                    if ($page == NULL)
                        $page = 1;

                    $limit  = $page_size;
                    $offset = $page_size * ($page -1);

                    //var_export(['limit' =>$limit, 'offset' => $offset]);
                }else{
                    $limit  = Arrays::shift($_get,'limit');
                    $offset = Arrays::shift($_get,'offset',0);                    

                    $get_limit($limit);
                    $page_size = $limit;
                }

                $order  = Arrays::shift($_get,'orderBy');

                // Importante:
                $_get = Arrays::nonassoc($_get);

                $allops = ['eq', 'gt', 'gteq', 'lteq', 'lt', 'neq'];
                $eqops  = ['=',  '>' , '>=',   '<=',   '<',  '!=' ];

                //var_export($_get);

                foreach ($_get as $key => $val){
                    if (is_array($val)){

                        $campo = $val[0];                       

                        if (is_array($val[1])){                             

                            #var_export($val[1]); ///

                            foreach ($val[1] as $op => $v){

                                #var_export([$op, $v]); ///
                                 
                                switch ($op) {
                                    case 'contains':
                                        $_get[$key] = [$campo, '%'.$v.'%', 'like'];
                                        $ignored[] = $campo;
                                        $data[$campo][] = $v;
                                    break;
                                    case 'notContains':
                                        $_get[$key] = [$campo, '%'.$v.'%', 'not like'];
                                        $ignored[] = $campo;
                                        $data[$campo][] = $v;
                                    break;
                                    case 'startsWith':
                                        $_get[$key] = [$campo, $v.'%', 'like'];
                                        $ignored[] = $campo;
                                        $data[$campo][] = $v;
                                    break;
                                    case 'notStartsWith':
                                        $_get[$key] = [$campo, $v.'%', 'not like'];
                                        $ignored[] = $campo;
                                        $data[$campo][] = $v;
                                    break;
                                    case 'endsWith':
                                        $_get[$key] = [$campo, '%'.$v, 'like'];
                                        $ignored[] = $campo;
                                        $data[$campo][] = $v;
                                    break;
                                    case 'notEndsWith':
                                        $_get[$key] = [$campo, '%'.$v, 'not like'];
                                        $ignored[] = $campo;
                                        $data[$campo][] = $v;
                                    break;
                                    case 'in':                                    
                                        if (strpos($v, ',')!== false){    
                                            $vals = explode(',', $v);
                                            $_get[$key] = [$campo, $vals, 'IN']; 

                                            foreach ($vals as $_v){
                                                $data[$campo][] = $_v;
                                            }
                                        }                                         
                                    break;
                                    case 'notIn':
                                        if (strpos($v, ',')!== false){    
                                            $vals = explode(',', $v);
                                            $_get[$key] = [$campo, $vals, 'NOT IN'];

                                            foreach ($vals as $_v){
                                                $data[$campo][] = $_v;
                                            }
                                        }                                         
                                    break;
                                    case 'between':
                                        if (substr_count($v, ',') == 1){    
                                            $vals = explode(',', $v);
                                            unset($_get[$key]);

                                            $min = min($vals[0],$vals[1]);
                                            $max = max($vals[0],$vals[1]);

                                            $_get[] = [$campo, $min, '>='];
                                            $_get[] = [$campo, $max, '<='];

                                            $data[$campo][] = $min;
                                            $data[$campo][] = $max;
                                        }                                         
                                    break;
                                    default:
                                        // 'eq', 'gt', ...

                                        $found = false;
                                        foreach ($allops as $ko => $oo){
                                            if ($op == $oo){
                                                $op = $eqops[$ko];
                                                unset($_get[$key]);
                                                $_get[] = [$campo, $v, $op];
                                                $data[$campo][] = $v; // 
                                                $found = true;                            
                                                break;                                    
                                            }                                    
                                        }

                                        if (!$found)
                                            Factory::response()->sendError("Invalid operator '$op'", 400);
                                    break;
                                }
                            }
                            
                        }else{                           

                            // IN
                            $v = $val[1];
                            if (strpos($v, ',')!== false){    
                                $vals = explode(',', $v);
                                $_get[$key] = [$campo, $vals];    
                                
                                foreach ($vals as $_v){
                                   $data[$campo][] = $_v;
                                }
                            } 
                        }   
                        
                    }                         
                }
                                
                // Si se pide algo que involucra un campo no está en el schema lanzar error
                foreach ($_get as $arr){
                    if (!in_array($arr[0],$properties))
                        Factory::response()->sendError("Unknown field '$arr[0]'", 400);
                }
                

                if (empty($folder)){
                    // root, sin especificar folder ni id (lista)   // *             
                    if (!$this->isGuest() && $owned && !$this->acl->hasSpecialPermission('read_all', $this->roles) )
                        $_get[] = ['belongs_to', $this->uid];        
                }else{
                    // folder, sin id

                    if (empty(static::$folder_field))
                        Factory::response()->sendError("Forbidden", 403, "'folder_field' is undefined");   
                        
                    $_get[] = [static::$folder_field, $f_rows[0]['name']];
                    $_get[] = ['belongs_to', $f_rows[0]['belongs_to']];
                }
                
                if ($id == null){
                    $validation = (new Validator())->setRequired(false)->ignoreFields($ignored)->validate($instance->getRules(),$data);
                    //var_export(['data' => $data, 'rules'=> $instance->getRules(), 'validation' => $validation]);

                    if ($validation !== true)
                        throw new InvalidValidationException(json_encode($validation));
                }      

                if (!empty($folder)) {
                    // event hook
                    $this->onGettingFolderAfterCheck($id, $folder);
                }
           
                if (strtolower($pretty) == 'false' || $pretty === 0)
                    $pretty = false;
                else
                    $pretty = true;   

                //var_export($_get); ////
                //var_export($_SERVER["QUERY_STRING"]);

                $query = Factory::request()->getQuery();
                
                if (isset($query['offset'])) 
                    unset($query['offset']);

                if (isset($query['limit'])) 
                    unset($query['limit']);

                if (isset($query['page'])) 
                    unset($query['page']);

                if (!isset($query['pageSize'])) 
                    $query['pageSize'] = $page_size;

                
                //////////
    
                // MIN, MAX, SUM, COUNT, AVG
                if (preg_match('/(min|max|sum|avg|count)\(([a-z\*]+)\)( as [a-z]+)?/i', $props, $matches)){
                    $ag_fn = strtolower($matches[1]);
                    $ag_ff = $matches[2];
                
                    if (preg_match('/[a-z]+\([a-z\*]+\) as ([a-z]+)/i', $props, $matches)){
                        $ag_alias = $matches[1];
                    }else
                        $ag_alias = NULL;
                }

                // WHERE
                $instance->where($_get);
                //var_export($_get);

                // GROUP BY
                if ($group_by != NULL){
                    $group_by = explode(',', $group_by);
                    $instance->groupBy($group_by);                   
                }                    

                // HAVING
                if (preg_match('/([a-z]+)\(([a-z\*]+)\)([><=]+)([0-9\.]+)/i', $having, $matches)){
                    $hv_fn = strtoupper($matches[1]);
                    $hv_ff = $matches[2];
                    $hv_op = $matches[3];
                    $hv_vv = $matches[4];                   

                    //var_export($matches);                    
                    $instance->having(["$hv_fn($hv_ff)", $hv_vv, $hv_op]);
                }elseif (preg_match('/([a-z]+)([><=]+)([0-9\.]+)/i', $having, $matches)){
                    $hv_fn_alias = $matches[1];
                    $hv_op = $matches[2];
                    $hv_vv = $matches[3]; 

                    $instance->having([$hv_fn_alias, $hv_vv, $hv_op]);
                }

                // ORDER BY
                if ($order !=  NULL)
                    $instance->orderBy($order);
                
                // LIMIT
                if ($limit != NULL)
                    $instance->limit($limit);

                // OFFSET
                if ($offset != NULL)
                    $instance->offset($offset);

                /*
                    Debe incluir los alias 
                */
                if (!empty($fields))
                    $instance->select($fields);


                if (isset($ag_fn)){
                    $rows = $instance->$ag_fn($ag_ff, $ag_alias);
                }else                               
                    $rows = $instance->get();
                

                ///Debug::dd($instance->getLastPrecompiledQuery());
            
                $res = Factory::response()->setPretty($pretty);

                /*
                    Falta paginar cuando hay groupBy & having
                */

                //  pagino solo sino hay funciones agregativas
                if (!isset($ag_fn)){
                    $total = (int) (new $model($conn))->where($_get)->setFetchMode('COLUMN')->count();
                    
                    $page_count = ceil($total / $limit);

                    if ($page == NULL)
                        $page = ceil($offset / $limit) +1;
                    
                    if ($page +1 <= $page_count){
                        $query['page'] = ($page +1);

                        $api_slug = $this->config['REMOVE_API_SLUG'] ? '' : '/api' ;
                        $next =  Url::protocol() . '//' . $_SERVER['HTTP_HOST'] . $api_slug . '/' . $api_version . '/'. $this->model_table . '?' . $query = str_replace(['%5B', '%5D', '%2C'], ['[', ']', ','], http_build_query($query));
                    }else{
                        $next = 'null';
                    }        

                    $pg = [ 
                        'total' => $total,
                        'count' => count($rows),
                        'currentPage' => $page,
                        'totalPages' => $page_count, 
                        'pageSize' => $page_size,
                        'nextUrl' => $next                                              
                    ];  

                    $res->setPaginator($pg);
                }
                               
                // event hooks
                if ($folder){
                    $this->onGotFolder($id, $total, $folder);
                }

                // event hook
                $this->onGot($id, $total);
                
                $res->send($rows);       
            }

        
        } catch (InvalidValidationException $e) { 
            Factory::response()->sendError('Validation Error', 400, json_decode($e->getMessage()));
        } catch (SqlException $e) { 
            Factory::response()->sendError('SQL Exception', 500, json_decode($e->getMessage())); 
        } catch (\PDOException $e) {    
            Factory::response()->sendError('PDO Exception', 500, $e->getMessage()); 
        } catch (\Exception $e) {   
            Factory::response()->sendError($e->getMessage());
        }	    
    } // 


    /**
     * post
     *
     * @return void
     */
    function post() {
        $data = Factory::request()->getBody();

        if (empty($data))
            Factory::response()->sendError('Invalid JSON',400);
        
        $model    = '\\simplerest\\models\\'.$this->modelName;
        $instance = (new $model())->setFetchMode('ASSOC');
        
        $id = $data[$id_name] ?? null;
        $this->folder = $folder = $data['folder'] ?? null;

        try {
            $this->conn = $conn = DB::getConnection();
            $instance->setConn($conn);

            // event hook             
            $this->onPosting($id, $data);

            if ($instance->inSchema(['belongs_to'])){
                if ($this->acl->hasSpecialPermission('transfer', $this->roles)){
                    // sino no digo de quien es, ... es mio 
                    if (!isset($data['belongs_to']))
                        $data['belongs_to'] = $this->uid;
                }elseif (!$this->isGuest())
                    $data['belongs_to'] = $this->uid; 
            }   

            if ($instance->inSchema(['created_by'])){
                $data['created_by'] = $this->uid;
            }

            if ($this->acl->hasSpecialPermission('fill_all', $this->roles)){               
                $instance->fillAll();
            }
            
            if ($folder !== null)
            {
                if (empty(static::$folder_field))
                    Factory::response()->sendError("Forbidden", 403, "'folder_field' is undefined");

                // event hook    
                $this->onPostingFolderBeforeCheck($id, $data, $folder);

                $f = DB::table('folders');
                $f_rows = $f->where(['id' => $folder])->get();
                      
                if (count($f_rows) == 0 || $f_rows[0]['tb'] != $this->model_table)
                    Factory::response()->sendError('Folder not found', 404); 
        
                if ($f_rows[0]['belongs_to'] != $this->uid  && !$this->hasFolderPermission($folder, 'w'))
                    Factory::response()->sendError("Forbidden", 403, "You have not permission for the folder $folder");

                unset($data['folder']);    
                $data[static::$folder_field] = $f_rows[0]['name'];
                $data['belongs_to'] = $f_rows[0]['belongs_to'];    
            }    

            $validado = (new Validator)->validate($instance->getRules(), $data);
            if ($validado !== true){
                Factory::response()->sendError('Data validation error', 400, $validado);
            }  

            if (!empty($folder)) {
                // event hook    
                $this->onPostingFolderAfterCheck($id, $data, $folder);
            }

            if ($instance->create($data)!==false){
                // event hooks
                $this->onPostFolder($instance->id, $data, $folder);
                $this->onPost($instance->id, $data);

                Factory::response()->send(['id' => $instance->id], 201);
            }	
            else
                Factory::response()->sendError("Error: creation of resource fails!");

        } catch (InvalidValidationException $e) { 
            Factory::response()->sendError('Validation Error', 400, json_decode($e->getMessage()));
        } catch (\Exception $e) {
            Factory::response()->sendError($e->getMessage());
        }	

    } // 
    
    protected function modify($id = NULL, bool $put_mode = false)
    { 
        if ($id == null)
            Factory::response()->sendError("Lacks id in request",400);

        $data = Factory::request()->getBody();

        if (empty($data))
            Factory::response()->sendError('Invalid JSON',400);
        
        $this->id = $id;    
        $this->folder = $folder = $data['folder'] ?? null; 

        // event hook
        $this->onPutting($id, $data);
        
        try {
            $model    = 'simplerest\\models\\'.$this->modelName;            
            $this->conn = $conn = DB::getConnection();       
			
			if (!$this->acl->hasSpecialPermission('lock', $this->roles)){
                $instance0 = (new $model($conn))->setFetchMode('ASSOC');
                $row = $instance0->where(['id', $id])->first();

                if (isset($row['locked']) && $row['locked'] == 1)
                    Factory::response()->sendError("Forbidden", 403, "Locked by Admin");
            }

            // Creo una instancia
            $instance = new $model();
            $instance->setConn($conn)->setFetchMode('ASSOC');

            $owned = $instance->inSchema(['belongs_to']);

            // evito que cualquiera pueda cambiar la propiedad de un registro
            if (!$this->acl->hasSpecialPermission('fill_all', $this->roles)){
                if (isset($data['deleted_at']))
                    unset($data['deleted_at']);

                if (!$this->acl->hasSpecialPermission('transfer', $this->roles)){    
                    if (isset($data['belongs_to']))
                    unset($data['belongs_to']);
                }   
            }else{
                //$instance->fill(['deleted_at']);
                $instance->fillAll();
            }

            if ($folder !== null)
            {
                if (empty(static::$folder_field))
                    Factory::response()->sendError("'folder_field' is undefined", 403);

                // event hook    
                $this->onPuttingFolderBeforeCheck($id, $data, $folder);

                $f = DB::table('folders')->setFetchMode('ASSOC');
                $f_rows = $f->where(['id' => $folder])->get();
                      
                if (count($f_rows) == 0 || $f_rows[0]['tb'] != $this->model_table)
                    Factory::response()->sendError('Folder not found', 404); 
        
                if ($f_rows[0]['belongs_to'] != $this->uid  && !$this->hasFolderPermission($folder, 'w') && !$this->acl->hasSpecialPermission('write_all_folders', $this->roles))
                    Factory::response()->sendError("You have not permission for the folder $folder", 403);

                $folder_name = $f_rows[0]['name'];

                // Creo otra nueva instancia
                $instance2 = new $model();
                $instance2->setConn($conn)->setFetchMode('ASSOC');

                if (count($instance2->where(['id' => $id, static::$folder_field => $folder_name])->get()) == 0)
                    Factory::response()->code(404)->sendError("Register for id=$id does not exists");

                unset($data['folder']);    
                $data[static::$folder_field] = $f_rows[0]['name'];
                $data['belongs_to'] = $f_rows[0]['belongs_to'];    
                
            } else {

                $instance2 = new $model();
                $instance2->setConn($conn)->setFetchMode('ASSOC'); 

                $rows = $instance2->where(['id' => $id])->get();

                if (count($rows) == 0){
                    Factory::response()->code(404)->sendError("Register for id=$id does not exists");
                }

                if  ($owned && !$this->acl->hasSpecialPermission('write_all', $this->roles) && $rows[0]['belongs_to'] != $this->uid)
                    Factory::response()->sendError('Forbidden', 403, 'You are not the owner');
                
            }        

            foreach ($data as $k => $v){
                if (strtoupper($v) == 'NULL' && $instance->isNullable($k)) 
                    $data[$k] = NULL;
            }

            $validado = (new Validator())->setRequired($put_mode)->validate($instance->getRules(), $data);
            if ($validado !== true){
                Factory::response()->sendError('Data validation error', 400, $validado);
            }
    
            if ($instance->inSchema(['updated_by'])){
                $data['updated_by'] = $this->impersonated_by != null ? $this->impersonated_by : $this->uid;
            }

            if (!empty($folder)) {
                // event hook 
                onPuttingFolderAfterCheck($id, $data, $folder);
            }

            $affected = $instance->where(['id', $id])->update($data);
            if ($affected !== false) {

                // even hooks        	    
                $this->onPutFolder($id, $data, $folder, $affected);
                $this->onPut($id, $data, $affected);
                
                Factory::response()->sendJson("OK");
            } else
                Factory::response()->sendError("Error in PATCH",404);	

        } catch (InvalidValidationException $e) { 
            Factory::response()->sendError('Validation Error', 400, json_decode($e->getMessage()));
        } catch (\Exception $e) {
            Factory::response()->sendError("Error during PATCH for id=$id with message: {$e->getMessage()}");
        }
    } //

        
    /**
     * put
     *
     * @param  int $id
     *
     * @return void
     */
    function put($id = null) {
        $this->modify($id, true);
    } // 
    

    /**
     * patch
     *
     * @param  mixed $id
     *
     * @return void
     */
    function patch($id = NULL) { 
        $this->modify($id);
    } //

        
    /**
     * delete
     *
     * @param  mixed $id
     *
     * @return void
     */
    function delete($id = NULL) {
        if($id == NULL)
            Factory::response()->sendError("Lacks id in request", 400);

        $data = Factory::request()->getBody();        

        $this->id = $id;
        $this->folder = $folder = $data['folder'] ?? null;

        $this->onDeleting($id);

        try {    
            $this->conn = $conn = DB::getConnection();

            $model    = 'simplerest\\models\\'.$this->modelName;
            
            $instance = (new $model($conn))->setFetchMode('ASSOC');
            $instance->fill(['deleted_at']); //

            $owned = $instance->inSchema(['belongs_to']);

            $rows = $instance->where(['id', $id])->get();
            
            if (count($rows) == 0){
                Factory::response()->code(404)->sendError("Register for id=$id does not exists");
            }

            if ($folder !== null)
            {
                if (empty(static::$folder_field))
                    Factory::response()->sendError("'folder_field' is undefined", 403);

                // event hook    
                $this->onDeletingFolderBeforeCheck($id, $folder);

                $f = DB::table('folders')->setFetchMode('ASSOC');
                $f_rows = $f->where(['id' => $folder])->get();
                      
                if (count($f_rows) == 0 || $f_rows[0]['tb'] != $this->model_table)
                    Factory::response()->sendError('Folder not found', 404); 
        
                if ($f_rows[0]['belongs_to'] != $this->uid  && !$this->hasFolderPermission($folder, 'w'))
                    Factory::response()->sendError("You have not permission for the folder $folder", 403);

                $folder_name = $f_rows[0]['name'];

                // Creo otra nueva instancia
                $instance2 = new $model();
                $instance2->setConn($conn)->setFetchMode('ASSOC');

                if (count($instance2->where(['id' => $id, static::$folder_field => $folder_name])->get()) == 0)
                    Factory::response()->code(404)->sendError("Register for id=$id does not exists");

                unset($data['folder']);    
                $data[static::$folder_field] = $f_rows[0]['name'];
                $data['belongs_to'] = $f_rows[0]['belongs_to'];    
            } else {
                if ($owned && !$this->acl->hasSpecialPermission('write_all', $this->roles) && $rows[0]['belongs_to'] != $this->uid){
                    Factory::response()->sendError('Forbidden', 403, 'You are not the owner');
                }
            }  

            $extra = [];

            if ($this->acl->hasSpecialPermission('lock', $this->roles)){
                if ($instance->inSchema(['locked'])){
                    $extra = array_merge($extra, ['locked' => 1]);
                }   
            }else {
                if (isset($rows[0]['locked']) && $rows[0]['locked'] == 1){
                    Factory::response()->sendError("Locked by Admin", 403);
                }
            }

            if ($instance->inSchema(['deleted_by'])){
                $extra = array_merge($extra, ['deleted_by' => $this->impersonated_by != null ? $this->impersonated_by : $this->uid]);
            }               
       
            if (!empty($folder)) {
                // event hook    
                $this->onDeletingFolderAfterCheck($id, $folder);
            }

            $affected = $instance->delete(static::$soft_delete && $instance->inSchema(['deleted_at']), $extra);
            if($affected){
                
                // event hooks
                if ($folder !==  null){
                    $this->onDeletedFolder($id, $affected, $folder);
                }
                $this->onDeleted($id, $affected);
                
                Factory::response()->sendJson("OK");
            }	
            else
                Factory::response()->sendError("Record not found",404);

        } catch (\Exception $e) {
            Factory::response()->sendError("Error during DELETE for id=$id with message: {$e->getMessage()}");
        }

    } // 


    /*
        API event hooks
    */    

    public function onGetting($id) { }
    public function onGot($id, ?int $count){ }

    public function onDeleting($id){ }
    public function onDeleted($id, ?int $affected){ }

    public function onPosting($id, Array $data){ }
    public function onPost($id, Array $data){ }

    public function onPutting($id, Array $data){ }
    public function onPut($id, Array $data, ?int $affected){ }

     /*
        API event hooks for folder access
    */  

    public function onGettingFolderBeforeCheck($id, $folder){ } 
    public function onGettingFolderAfterCheck($id, $folder){ }
    public function onGotFolder($id, ?int $count, $folder){ }

    public function onDeletingFolderBeforeCheck($id, $folder){ }
    public function onDeletingFolderAfterCheck($id, $folder){ }
    public function onDeletedFolder($id, ?int $affected, $folder){ }

    public function onPostingFolderBeforeCheck($id, Array $data, $folder){ }
    public function onPostingFolderAfterCheck($id, Array $data, $folder){ }
    public function onPostFolder($id, Array $data, $folder){ }

    public function onPuttingFolderBeforeCheck($id, Array $data, $folder){ }
    public function onPuttingFolderAfterCheck($id, Array $data, $folder){ }
    public function onPutFolder($id, Array $data, $folder, ?int $affected){ }

    
}  