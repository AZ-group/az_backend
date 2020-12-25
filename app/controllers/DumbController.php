<?php

namespace simplerest\controllers;

use simplerest\core\Controller;
use simplerest\core\Request;
use simplerest\libs\Factory;
use simplerest\libs\Debug;
use simplerest\libs\DB;
use simplerest\core\Model;
use simplerest\models\BarModel;
use simplerest\models\UsersModel;
use simplerest\models\ProductsModel;
use simplerest\models\UserRolesModel;
use PHPMailer\PHPMailer\PHPMailer;
use simplerest\libs\Utils;
use simplerest\libs\Strings;
use simplerest\libs\Arrays;
use simplerest\libs\Validator;
//use GuzzleHttp\Client;
//use Guzzle\Http\Message\Request;
//use Symfony\Component\Uid\Uuid;
use simplerest\libs\Files;
use simplerest\libs\Time;
use simplerest\core\Schema;
//use simplerest\models\CablesModel;
use simplerest\core\Route;


class DumbController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    // ok
    function test504(){
        DB::table('products')
        ->setFetchMode('COLUMN')
        ->selectRaw('cost * 1.05 as cost_after_inc')->get();
    }

    // fails en PSQL
    function test505(){
        DB::table('products')
        ->setFetchMode('COLUMN')
        ->selectRaw('cost * ? as cost_after_inc', [1.05])->get();
    }

    function test506()
    {
        $con = DB::getConnection();
        $sth = $con->prepare('SELECT cost * ? as cost_after_inc FROM products');
        
        $sth->bindValue(1, 1.05, \PDO::PARAM_INT);

        /*
            caught PDOException: SQLSTATE[22P02]: 
            Invalid text representation: 7 ERROR:  
            invalid input syntax for type bigint: "1.05
        */
        $sth->execute(); // fallo
       
        $res = $sth->fetch();
        dd($res);
    }


    function test507()
    {
        //dd(is_float('1.25'));
        //exit;

        $con = DB::getConnection();
        $sth = $con->prepare('SELECT cost * CAST(? AS DOUBLE PRECISION) as cost_after_inc FROM products');
        
        $sth->bindValue(1, 1.05, \PDO::PARAM_INT);

        $sth->execute(); // ok
       
        $res = $sth->fetch();
        dd($res);
    }

    /*
        Falla en POSTGRES:

        SELECT COUNT(*) as c, name FROM products GROUP BY name HAVING COUNT(*) > 3

        Es como si en pgsql se evaluara primero el HAVING y luego el SELECT.
    */
    function alias(){
        $rows = DB::table('products')->showDeleted()
        ->groupBy(['name'])
        ->having(['c', 3, '>'])
        ->select(['name'])
        ->selectRaw('COUNT(*) as c')
        ->get();

        dd(DB::getLog());
    }

    function index(){
        return 'INDEX';
    }

    function add($a, $b){
        $res = (int) $a + (int) $b;
        return  "$a + $b = " . $res;
    }

    function mul(){
        $req = Request::getInstance();
        $res = (int) $req[0] * (int) $req[1];
        return "$req[0] * $req[1] = " . $res;
    }

    function div(){
        $res = (int) @Request::getParam(0) / (int) @Request::getParam(1);
        //
        // hacer un return en vez de un "echo" me habilita a manipular
        // la "respuesta", conviertiendola a JSON por ejemplo 
        //
        return ['result' => $res];
    }

    function login(){
		view('login.php');
    }
    
    function casa_cambio(){
        view('casa_cambio/home.htm', [], 'casa_cambio/layout.php', 'casa_cambio/app.htm' );
    }

    /*
    function xyz(){
        DB::getConnection('db3');
        
        $curs = DB::table('countries')
        ->distinct(['currency'])->get();

        $groups = [];

        $rows = DB::table('countries')
        ->orderBy(['currency' => 'ASC'])
        ->get();

        foreach ($rows as $row){
            $groups[$row['currency']][] = $row;
        }

        $m2 = DB::table('currencies');
        $at = $m2->getAttr();

        //dd($groups);
        //exit;

        foreach ($groups as $curr_code => $g){
            $alpha2 = [];
            $alpha3 = [];
            $langCS = [];
            $langDE = [];
            $langEN = [];
            $langES = [];
            $langFR = [];
            $langIT = [];
            $langNL = [];
            foreach ($g as $c){
                $alpha2[] = $c['alpha2'];
                $alpha3[] = $c['alpha3'];
                $langCS[] = $c['langCS'];
                $langDE[] = $c['langDE'];
                $langEN[] = $c['langEN'];
                $langES[] = $c['langES'];
                $langFR[] = $c['langFR'];
                $langIT[] = $c['langIT'];
                $langNL[] = $c['langNL'];
                //dd($country);
            }

            $alpha2_main = count($alpha2) == 1 ? $alpha2[0] : NULL;
            $alpha3_main = count($alpha3) == 1 ? $alpha3[0] : NULL;

            $alpha2 = json_encode($alpha2);
            $alpha3 = json_encode($alpha3);
            $langCS = json_encode($langCS);
            $langDE = json_encode($langDE);
            $langEN = json_encode($langEN);
            $langES = json_encode($langES);
            $langFR = json_encode($langFR);
            $langIT = json_encode($langIT);
            $langNL = json_encode($langNL);

         
            $data = array_combine($at, [$alpha2_main, $alpha3_main, $alpha2, $alpha3, $langCS, $langDE, $langEN, $langES, $langFR, $langIT, $langNL, $curr_code]);
            
            //exit;
            $m2->create($data);        
        }

        //dd($not_grouped);
        //dd($groups);
        //dd($alpha2);
        //dd(count($groups));
    }
    */


    /*
    function csv(){
        DB::getConnection('db3');
        $m = (new Model())->connect()->table('currencies');

        $file = file_get_contents(UPLOADS_PATH . 'iso_4217.csv');
        $lines = explode("\n", $file);
        
        $regs = [];

        foreach($lines as $line){
            $r = explode(';', $line);
            $r[4] = explode(',', $r[4]);
            array_walk($r[4], function(&$str){ $str =  trim($str);});
            $r[4] = json_encode($r[4]);
            //dd($r);
            
            $reg = array_combine(['code', 'num', 'digits', 'cur_name', 'locations'], $r);
            dd($reg);

            dd($m->create($reg));
        }
    }
    */


    /*
    function mul(Request $req){
        $res = (int) $req[0] * (int) $req[1];
        echo "$req[0] + $req[1] = " . $res;
    }
    */    

    function schema(){
        $m = (new ProductsModel());
        dd($m->getSchema());
    }

    function use_model(){
        $m = (new Model(true))
            ->table('products')  // <---------------- 
            ->select(['id', 'name', 'size'])
            ->where(['cost', 150, '>='])
            ->where(['id', 100, '<']);

        dd($m->get());

        // No hay Schema
        dd($m->getSchema());

        dd($m->dd());
    }

    function get_bar0(){
        $m = (new Model(true))
            ->table('bar')  
            ->where(['uuid', '0fefc2b1-f0d3-47aa-a875-5dbca85855f9']);
            
        dd($m->get());
    }

    function get_bar1(){
        $m = DB::table('bar')
         // ->assoc()
        ->where(['uuid', '0fefc2b1-f0d3-47aa-a875-5dbca85855f9']);
       
        dd($m->get());
    }

    function get_bar2(){
        $m = (new BarModel())
        ->connect()
        // ->assoc()
        ->where(['uuid', '0fefc2b1-f0d3-47aa-a875-5dbca85855f9']);
        
        dd($m->get());
    }
    

    /*
        Se utiliza un modelo *sin* schema y sobre el cual no es posible hacer validaciones
    */
    function create_s(){
        $m = (new Model(true))
        ->table('super_cool_table');

        // No hay schema ?
        dd($m->getSchema());
        
        dd($m->create([
            'name' => 'SUPER',
			'age' => 22,
        ]));
    }

    /*
        Se utiliza un modelo *sin* schema y sobre el cual no es posible hacer validaciones
    */
    function create_baz0(){
        $m = (new Model(true))
        ->table('baz');

        // No hay Schema
        dd($m->getSchema());
        
        dd($m->create([
            'id_baz' => 1800,
            'name' => 'BAZ',
			'cost' => '100',
        ]));
    }


    /*
        Se utiliza un modelo *sin* schema y sobre el cual no es posible hacer validaciones
    */
    function create_bar(){
        $m = (new Model(true))
        ->table('bar');

        // No hay Schema
        dd($m->getSchema());
        
        dd($m->create([
            'name' => 'ggg',
			'price' => '88.90',
        ]));
    }

    function create_bar1(){
        $m = DB::table('bar');
        $m->setValidator(new Validator());

        // SI hay schema
        dd($m->getSchema());
        
        dd($m->create([
            'name' => 'gggggggggg',
			'price' => '100',
        ]));
    }

    function get_products(){
        dd(DB::table('products')->get());
    }

    function get_products2(){
        dd(DB::table('products')->where(['size', '2L'])->get());
    }

    function create_p(){

        $name = '';
        for ($i=0;$i<20;$i++)
            $name .= chr(rand(97,122));

        $id = DB::table('products')->create([ 
            'name' => $name, 
            'description' => 'Esto es una prueba 77', 
            'size' => '100L',
            'cost' => 66,
            'belongs_to' => 90
        ]);   
        
        return $id;
    }

    function create_baz($id = null){

        $name = '';
        for ($i=0;$i<20;$i++)
            $name .= chr(rand(97,122));

        $data = [ 
            'name' => $name,
            'cost' => 100
        ];

        if ($id != null){
            $data['id'] = $id;
        }

        $id = DB::table('baz')->create($data);    

        dd($id, 'las_inserted_id');
    }

    
    // implementada y funcionando en register() 
    function transaction(){
        DB::beginTransaction();

        try {
            $name = '';
            for ($i=0;$i<20;$i++)
                $name .= chr(rand(97,122));

            $id = DB::table('products')->create([ 
                'name' => $name, 
                'description' => 'bla bla bla', 
                'size' => rand(1,5).'L',
                'cost' => rand(0,500),
                'belongs_to' => 90
            ]);   

            //throw new \Exception("AAA"); 

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollback();            
        }            
    }

    // https://fideloper.com/laravel-database-transactions
    function transaction2(){
        DB::transaction(function(){
            $name = '';
            for ($i=0;$i<20;$i++)
                $name .= chr(rand(97,122));

            $id = DB::table('products')->create([ 
                'name' => $name, 
                'description' => 'Esto es una prueba', 
                'size' => rand(1,5).'L',
                'cost' => rand(0,500),
                'belongs_to' => 90
            ]);   

            throw new \Exception("AAA"); 
        });      
    }
    
    function output_mutator(){
        $rows = DB::table('users')
        ->registerOutputMutator('username', function($str){ return strtoupper($str); })
        ->get();

        dd($rows);
    }

    function output_mutator2(){
        $rows = DB::table('products')
        ->registerOutputMutator('size', function($str){ return strtolower($str); })
        ->groupBy(['size'])
        ->having(['AVG(cost)', 150, '>='])
        ->select(['size'])
        ->selectRaw('AVG(cost)')
        ->get();

        dd($rows);
    }

    /*
        El problema de los campos ocultos es que rompen los transformers
        usar when() en su lugar

        https://laravel.com/docs/5.5/eloquent-resources
    */
    function transform(){
        //$this->is_admin = true;


        $t = new \simplerest\transformers\UsersTransformer();

        $rows = DB::table('users')
        ->registerTransformer($t, $this)
        ->get();

        dd($rows);
    }

    function transform_and_output_mutator(){
        $t = new \simplerest\transformers\UsersTransformer();

        $rows = DB::table('users')
        ->registerOutputMutator('username', function($str){ return strtoupper($str); })
        ->registerTransformer($t)
        ->get();

        dd($rows);
    }

    function transform2(){
        $t = new \simplerest\transformers\ProductsTransformer();

        $rows = DB::table('products')
        ->where(['size'=>'2L'])
        ->registerTransformer($t)
        ->get();

        dd($rows);
    }


    // 'SELECT id, name, cost FROM products WHERE (cost = 200) AND deleted_at IS NULL LIMIT 20, 10;'
    function g(){
        dd(DB::table('products')
        ->where(['cost', 200])
        ->limit(10)
        ->offset(20)
        ->get(['id', 'name', 'cost']));
        
        dd(DB::getLog());
    }

    function limit(){
        dd(DB::table('products')
        ->select(['id', 'name', 'cost'])
        ->offset(10)
        ->limit(5)
        ->get());

        dd(DB::getLog());
    }

    function limit0(){
        dd(DB::table('products')
        ->offset(20)
        ->select(['id', 'name', 'cost'])
        ->limit(10)
        ->setPaginator(false)
        ->get());
        
        dd(DB::getLog());

        dd(DB::table('products')->limit(10)->get());
        dd(DB::getLog());
    }
    
    ///
    function limite(){
        DB::table('products')->offset(20)->limit(10)->get();
        dd(DB::getLog());

        DB::table('products')->limit(10)->get();
        dd(DB::getLog());
    }

    function distinct(){
        dd(DB::table('products')->distinct()->get(['size']));

        // Or
        dd(DB::table('products')->distinct(['size'])->get());

        // Or
        dd(DB::table('products')->select(['size'])->distinct()->get());
    }

    function distinct1(){
        dd(DB::table('products')->select(['size', 'cost'])->distinct()->get());
    }

    function distinct2(){
        dd(DB::table('users')->distinct()->get());
    }

    function distinct3(){
        dd(DB::table('products')->distinct()->get());
    }

    function pluck(){
        $names = DB::table('products')->pluck('size');

        foreach ($names as $name) {
            dd($name);
        }
    }

    function pluck2($uid) {
        $perms = DB::table('user_sp_permissions')
        ->assoc()
        ->where(['user_id' => $uid])
        ->join('sp_permissions', 'user_sp_permissions.sp_permission_id', '=', 'sp_permissions.id')
        ->pluck('name');

        dd($perms);
    }

    function get_product($id){       
        // Include deleted items
        dd(DB::table('products')->find($id)->showDeleted()->dd());
    }
    
    function exists(){
       
        dd(DB::table('products')->where(['belongs_to' => 103])->exists());
        //dd(DB::getLog());

        dd(DB::table('products')->where([ 
            ['cost', 200, '<'],
            ['name', 'CocaCola'] 
        ])->exists());
        //dd(DB::  getLog());
		
        dd(DB::table('users')->where(['username' => 'boctulus'])->exists());
        //dd(DB::  getLog());
    }
           
    function first(){
        dd(DB::table('products')->where([ 
            ['cost', 50, '>='],
            ['cost', 500, '<='],
            ['belongs_to',  90]
        ])->first(['name', 'size', 'cost'])); 
    }

    function value(){
		dd(DB::table('products')->where([ 
            ['cost', 5000, '>=']
        ])->value('name')); 
		
        dd(DB::table('products')->where([ 
            ['cost', 200, '>='],
            ['cost', 500, '<='],
            ['belongs_to',  90]
        ])->value('name')); 
    }

    function oldest(){
        // oldest first
        dd(DB::table('products')->oldest()->get());
    }

    function newest(){
        // newest, first result
        dd(DB::table('products')->newest()->first());
    }
    
    // random or rand
    function random(){
        //dd(DB::table('products')->random()->get(['id', 'name']), 'ALL');
        dd(DB::table('products')->random()->select(['id', 'name'])->get(), 'ALL');

        dd(DB::table('products')->random()->limit(5)->get(['id', 'name']), 'N RESULTS');

        dd(DB::table('products')->random()->select(['id', 'name'])->first(), 'FIRST');
    }

    function count(){
        DB::setConnection('db1');

        $c = DB::table('products')
        ->where([ 'belongs_to'=> 90] )
        ->count();

        dd($c);
    }

    function count1(){
        $c = DB::table('products')
        //->assoc()
        ->where([ 'belongs_to'=> 90] )
        ->count('*', 'count');

        dd($c);
        dd(DB::getLog());
    }

    function count1b(){
        // SELECT COUNT(*) FROM products WHERE cost >= 100 AND size = '1L' AND belongs_to = 90 AND deleted_at IS NULL 

        $res =  DB::table('products')
        ->where([ ['cost', 100, '>='], ['size', '1L'], ['belongs_to', 90] ])
        ->count();

        dd($res);
        dd(DB::getLog());
    } 

    function count2(){
        // SELECT COUNT(DISTINCT description) FROM products WHERE cost >= 100 AND size = '1L' AND belongs_to = 90 AND deleted_at IS NULL  

        $res = DB::table('products')
        ->where([ ['cost', 100, '>='], ['size', '1L'], ['belongs_to', 90] ])
        ->distinct()
        ->count('description');

        dd($res);
        dd(DB::getLog());
    }

    function count2b(){
        // SELECT COUNT(DISTINCT description) FROM products WHERE cost >= 100 AND size = '1L' AND belongs_to = 90 AND deleted_at IS NULL  

        $res = DB::table('products')
        ->where([ ['cost', 100, '>='], ['size', '1L'], ['belongs_to', 90] ])
        ->distinct()
        ->count('description', 'count');

        dd($res);
        dd(DB::getLog());
    }

    function count3(){
        $uid = 415;

        $count = (int) DB::table('user_roles')
		->where(['user_id' => $uid])->setFetchMode('COLUMN')
		->count();
		
        dd($count);
        dd(DB::getLog());
    }

    function avg(){
        // SELECT AVG(cost) FROM products WHERE cost >= 100 AND size = '1L' AND belongs_to = 90 AND deleted_at IS NULL; 

        $res = DB::table('products')
        ->where([ ['cost', 100, '>='], ['size', '1L'], ['belongs_to', 90] ])
        ->avg('cost', 'prom');

        dd($res);
    }

    function sum(){
        // SELECT SUM(cost) FROM products WHERE cost >= 100 AND size = '1L' AND belongs_to = 90 AND deleted_at IS NULL; 

        $res = DB::table('products')
        ->where([ ['cost', 100, '>='], ['size', '1L'], ['belongs_to', 90] ])
        ->sum('cost', 'suma');

        dd($res);
        dd(DB::getLog());
    }

    function min(){
        // SELECT MIN(cost) FROM products WHERE cost >= 100 AND size = '1L' AND belongs_to = 90 AND deleted_at IS NULL; 

        $res = DB::table('products')
        ->where([ ['cost', 100, '>='], ['size', '1L'], ['belongs_to', 90] ])
        ->min('cost', 'minimo');

        dd($res);
    }

    function max(){
        // SELECT MIN(cost) FROM products WHERE cost >= 100 AND size = '1L' AND belongs_to = 90 AND deleted_at IS NULL; 

        $res =  DB::table('products')
        ->where([ ['cost', 100, '>='], ['size', '1L'], ['belongs_to', 90] ])
        ->max('cost', 'maximo');

        dd($res);
    }

    /*
        select and addSelect
    */
    function select() {
        dd(DB::table('products')
        ->random()
        ->select(['id', 'name'])
        ->addSelect('active')
        ->where(['active', true])
        ->first());
    }

    /*
        RAW select

        pluck() no se puede usar con selectRaw() si posee un "as" pero la forma de lograr lo mismo
        es seteando el "fetch mode" en "COLUMN"

        Investigar como funciona el pluck() de Larvel
        https://stackoverflow.com/a/40964361/980631
    */
    function select2() {
        $m = DB::table('products')->setFetchMode('COLUMN')
        ->selectRaw('cost * ? as cost_after_inc', [1.05]);

        dd($m->get());
        dd($m->dd());
    }

    function select3() {
        dd(DB::table('products')->setFetchMode('COLUMN')
        ->where([ ['cost', 100, '>='], ['size', '1L'], ['belongs_to', 90] ])
        ->selectRaw('cost * ? as cost_after_inc', [1.05])->get());
    }

    function select3a() {
        dd(DB::table('products')
        ->where([ ['cost', 100, '>='], ['size', '1L'], ['belongs_to', 90] ])
        ->selectRaw('cost * ? as cost_after_inc', [1.05])->distinct()->get());
    }

    function select3b() {
        dd(DB::table('products')->setFetchMode('COLUMN')
        ->where([ ['cost', 100, '>='], ['size', '1L'], ['belongs_to', 90] ])
        ->selectRaw('cost * ? as cost_after_inc', [1.05])->distinct()->get());
    }

    function select4() {
        dd(DB::table('products')
        ->where([ ['cost', 100, '>='], ['size', '1L'], ['belongs_to', 90] ])
        ->selectRaw('cost * ? as cost_after_inc', [1.05])
        ->addSelect('name')
        ->addSelect('cost')
        ->get());
    }

    /*
        La ventaja de usar select() - por sobre usar get() - es que se ejecuta antes que count() permitiendo combinar selección de campos con COUNT() 

        SELECT size, COUNT(*) FROM products GROUP BY size
    */
    function select_group_count(){
        dd(DB::table('products')->showDeleted()
        ->groupBy(['size'])->select(['size'])->count());
		dd(DB::getLog());
    }

    /*
        SELECT size, AVG(cost) FROM products GROUP BY size
    */
    function select_group_avg(){
        dd(DB::table('products')->showDeleted()
        ->groupBy(['size'])->select(['size'])
        ->avg('cost'));    
    }

    function filter_products1(){
        dd(DB::table('products')->showDeleted()->where([ 
            ['size', '2L']
        ])->get());
    }
    
    function filter_products2(){
        $m = DB::table('products')
        ->where([ 
            ['name', ['Vodka', 'Wisky', 'Tekila','CocaCola']], // IN 
            ['locked', 0],
            ['belongs_to', 90]
        ])
        ->whereNotNull('description');

        dd($m->get());
        var_dump(DB::getLog());
        //var_dump($m->dd());
    }

    // SELECT * FROM products WHERE name IN ('CocaCola', 'PesiLoca') OR cost IN (100, 200)  OR cost >= 550 AND deleted_at IS NULL
    function filter_products3(){

        dd(DB::table('products')->where([ 
            ['name', ['CocaCola', 'PesiLoca']], 
            ['cost', 550, '>='],
            ['cost', [100, 200]]
        ], 'OR')->get());    
    }

    function filter_products4(){    
        dd(DB::table('products')->where([ 
            ['name', ['CocaCola', 'PesiLoca', 'Wisky', 'Vodka'], 'NOT IN']
        ])->get());
    }

    function filter_products5(){
        // implicit 'AND'
        dd(DB::table('products')->where([ 
            ['cost', 200, '<'],
            ['name', 'CocaCola'] 
        ])->get());        
    }

    function filter_products6(){
        dd(DB::table('products')->where([ 
            ['cost', 200, '>='],
            ['cost', 270, '<=']
        ])->get());            
    }

    // WHERE IN
    function where1(){
        dd(DB::table('products')->where(['size', ['0.5L', '3L'], 'IN'])->get());
    }

    // WHERE IN
    function where2(){
        dd(DB::table('products')->where(['size', ['0.5L', '3L']])->get());
    }

    // WHERE IN
    function where3(){
        dd(DB::table('products')->whereIn('size', ['0.5L', '3L'])->get());
    }

    //WHERE NOT IN
    function where4(){
        dd(DB::table('products')->where(['size', ['0.5L', '3L'], 'NOT IN'])->get());
    }

    //WHERE NOT IN
    function where5(){
        dd(DB::table('products')->whereNotIn('size', ['0.5L', '3L'])->get());
    }

    // WHERE NULL
    function where6(){  
        dd(DB::table('products')->where(['workspace', null])->get());   
    }

    // WHERE NULL
    function where7(){  
        dd(DB::table('products')->whereNull('workspace')->get());
    }

    // WHERE NOT NULL
    function where8(){  
        dd(DB::table('products')->where(['workspace', null, 'IS NOT'])->get());   
    }

    // WHERE NOT NULL
    function where9(){  
        dd(DB::table('products')->whereNotNull('workspace')->get());
    }

    // WHERE BETWEEN
    function where10(){
        dd(DB::table('products')
        ->select(['name', 'cost'])
        ->whereBetween('cost', [100, 250])->get());
    }

    // WHERE BETWEEN
    function where11(){
        dd(DB::table('products')
        ->select(['name', 'cost'])
        ->whereNotBetween('cost', [100, 250])->get());
    }
    
    function where12(){
        dd(DB::table('products')
        ->find(103));
    }

    function where13(){
        dd(DB::table('products')
        ->where(['cost', 150])
        ->value('name'));
    }

    /*
        SELECT  name, cost, id FROM products WHERE belongs_to = '90' AND (cost >= 100 AND cost < 500) AND description IS NOT NULL
    */
    function where14(){
        dd(DB::table('products')->showDeleted()
        ->select(['name', 'cost', 'id'])
        ->where(['belongs_to', 90])
        ->where([ 
            ['cost', 100, '>='],
            ['cost', 500, '<']
        ])
        ->whereNotNull('description')
        ->get());
    }

    
    /* 
        A OR B OR (C AND D)

       SELECT name, cost, id FROM products WHERE 
       belongs_to = 90 OR 
       name IN (\'CocaCola\', \'PesiLoca\') OR 
       (cost <= 550 AND cost >= 100)
    */
    function or_where(){
        $q = DB::table('products')->showDeleted()
        ->select(['name', 'cost', 'id'])
        ->where(['belongs_to', 90])
        ->orWhere(['name', ['CocaCola', 'PesiLoca']])
        ->orWhere([
            ['cost', 550, '<='],
            ['cost', 100, '>=']
        ]);

        //dd($q->get());
        dd($q->dd());
    }
    
    // A OR (B AND C)
    function or_where2(){
        $q = DB::table('products')->showDeleted()
        ->select(['name', 'cost', 'id', 'description'])
        ->whereNotNull('description')
        ->orWhere([ 
                    ['cost', 100, '>='],
                    ['cost', 500, '<']
        ]);

        dd($q->get());
        dd($q->dd());
    }


     /*
        SELECT  name, cost, id FROM products WHERE 
        belongs_to = '90' AND 
        (
            name IN ('CocaCola', 'PesiLoca') OR 
            cost >= 550 OR 
            cost < 100
        ) AND 
        description IS NOT NULL
    */
    function where_or(){
        $q = DB::table('products')->showDeleted()
        ->select(['name', 'cost', 'id'])
        ->where(['belongs_to', 90])
        ->where([                           // <--- whereOr() === where([], 'OR')
            ['name', ['CocaCola', 'PesiLoca']], 
            ['cost', 550, '>='],
            ['cost', 100, '<']
        ], 'OR')
        ->whereNotNull('description');

        dd($q->get());
        dd($q->dd());
    }

     /*
        SELECT  name, cost, id FROM products WHERE 
        belongs_to = '90' AND 
        (
            name IN ('CocaCola', 'PesiLoca') OR 
            cost >= 550 OR 
            cost < 100
        ) AND 
        description IS NOT NULL
    */
    function where_or1(){
        $q = DB::table('products')->showDeleted()
        ->select(['name', 'cost', 'id'])
        ->where(['belongs_to', 90])
        ->whereOr([ 
            ['name', ['CocaCola', 'PesiLoca']], 
            ['cost', 550, '>='],
            ['cost', 100, '<']
        ])
        ->whereNotNull('description');

        dd($q->get());
        dd($q->dd());
    }
        
    /*
        SELECT  name, cost, id FROM products WHERE (belongs_to = '90' AND (name IN ('CocaCola', 'PesiLoca')  OR cost >= 550 OR cost < 100) AND description IS NOT NULL) AND deleted_at IS NULL OR  (cost >= 100 AND cost < 500)
    */
    function where_or2(){
        dd(DB::table('products')
        ->select(['id', 'name', 'cost', 'description'])
        ->where(['belongs_to', 90])
        ->where([ 
            ['name', ['CocaCola', 'PesiLoca']], 
            ['cost', 550, '>='],
            ['cost', 100, '<']
        ], 'OR')
        ->whereNotNull('description')
        ->get());
    }

    // SELECT * FROM users WHERE (email = 'nano@g.c' OR  username = 'nano') AND deleted_at IS NULL
    function or_where3(){
        $email = 'nano@g.c';
        $username = 'nano';

        $rows = DB::table('users')->assoc()->unhide(['password'])
            ->where([ 'email'=> $email, 
                      'username' => $username 
            ], 'OR') 
            ->setValidator((new Validator())->setRequired(false))  
            ->get();

        dd($rows);
    }

    // SELECT * FROM users WHERE (email = 'nano@g.c' OR  username = 'nano') AND deleted_at IS NULL
    function or_where3b(){
        $email = 'nano@g.c';
        $username = 'nano';

        $rows = DB::table('users')->assoc()
            ->where([ 'email'=> $email ]) 
            ->orWhere(['username' => $username ])
            ->setValidator((new Validator())->setRequired(false))  
            ->get();

        dd($rows);
    }



    /*
    array (
        'op' => 'and,
        'q' => array (
            array (
                'op' => 'or',
                'q' => array (
                        array (
                            0 => ' cost > ?',
                            1 => ' id < ',
                        ),        

                        array (
                            0 => ' cost <= ?',
                            1 => ' description IS NOT ?',
                        )
                )
            ),

            array(
                0 => 'id = ?'
            )
        )
    )
    */

    /*
        SSELECT id, cost, size, description, belongs_to FROM products WHERE 
        
        (name LIKE '%a%') AND 
        (cost > 100 AND id < 50) AND 
        (
            active = 1 OR 
            (cost <= 100 AND description IS NOT NULL)
        ) 
        AND belongs_to > 150;
    */
    function where_adv()
    {
        $m = (new Model())
        ->table('products')

        ->where([
            ['cost', 100, '>'], // AND
            ['id', 50, '<']
        ]) 
        // AND
        ->whereRaw('name LIKE ?', ['%a%'])
        // AND
        ->group(function($q){  
            $q->where(['active', 1])
            // OR
              ->orWhere([
                ['cost', 100, '<='], 
                ['description', NULL, 'IS NOT']
            ]);  
        })
        // AND
        ->where(['belongs_to', 150, '>'])
        //->dontExec()
        ->select(['id', 'cost', 'size', 'description', 'belongs_to']);

       dd($m->get());  
	   var_dump($m->dd());
    }

    /*
        SELECT id, cost, size, description, belongs_to FROM products WHERE 
        
            (cost > 100 AND id < 50) OR <--- Ok
            (
                (name LIKE '%a') AND 
                (cost <= 100 AND description IS NOT NULL)
            ) AND 
            belongs_to > 150;
    */
    function where_adv2()
    {
        $m = (new Model())
        ->table('products')

        ->where([
            ['cost', 100, '>'], // AND
            ['id', 50, '<']
        ]) 
        // OR
        ->or(function($q){  
            $q->whereRaw('name LIKE ?', ['%a'])
              // AND  
              ->where([
                ['cost', 100, '<='], 
                ['description', NULL, 'IS NOT']
            ]);  
        })
        // AND
        ->where(['belongs_to', 150, '>'])
        
        ->select(['id', 'cost', 'size', 'description', 'belongs_to']);

       //dd($m->get()); 
	   var_dump($m->dd());
    }

    /*
        Negador de wheres

        SELECT id, cost, size, description, belongs_to FROM products WHERE 

        (
            NOT 
            (
                (cost > 100 AND id < 50) OR 
                (cost <= 100 AND description IS NOT NULL) 

            ) AND belongs_to > 150

        ) AND 

        deleted_at IS NULL;
    */
    function not(){
        $m = DB::table('products')

        ->not(function($q){  // <-- group *
            $q->where([
                 ['cost', 100, '>'],
                 ['id', 50, '<']
             ]) 
             // OR
             ->orWhere([
                 ['cost', 100, '<='],
                 ['description', NULL, 'IS NOT']
             ]);  
         })
         // AND
         ->where(['belongs_to', 150, '>'])
         
         ->select(['id', 'cost', 'size', 'description', 'belongs_to']);
 
        dd($m->get()); 
        var_dump($m->dd());
    }

    // ok
    function notor(){
        $m = DB::table('products')

        ->where(['belongs_to', 150, '>'])
        ->not(function($q) {
            $q->where(['name', 'a$'])
            ->or(function($q){
                $q->where([
                    ['cost', 100, '<='],
                    ['description', NULL, 'IS NOT']
                ]);
            });             
        })
        ->dontExec()
        ->where(['size', '1L', '>=']);

        //dd($m->get());
        dd($m->dd());
    }


    /*
        SELECT * FROM products WHERE 
        
        (
            belongs_to > 150 AND 
            NOT (
                    (name REGEXP 'a$') OR
                    ((cost <= 100 AND 
                        description IS NOT NULL
                    ))
                ) AND 
            size >= \'1L\'
        ) AND 
        deleted_at IS NULL;

    */
    function notor_whereraw(){
        $m = DB::table('products')

        ->where(['belongs_to', 150, '>'])
        ->not(function($q) {
            $q->whereRegEx('name', 'a$')
            ->or(function($q){ 
                $q->where([
                    ['cost', 100, '<='],
                    ['description', NULL, 'IS NOT']
                ]);
            });             
        })
        ->dontExec()
        ->where(['size', '1L', '>=']);

        //dd($m->get());
        dd($m->dd());
    }

    // ok
    function or_problematico(){
        $m = DB::table('products')
    
        ->whereRegEx('name', 'a$')
        ->or(function($q){
            $q->where(['cost', 100, '<=']);
        })     
        ->showDeleted()        
        ->dontExec();

        //dd($m->get());
        dd($m->dd());
    }

    // ok
    function or__problematico_b(){
        $m = DB::table('products')
    
        ->whereRegEx('name', 'a$')
        ->or(function($q){
            $q->group(function($q){
                $q->where(['cost', 100, '<='])
                ->orWhere(['id', 90]);
            });
        })     
        ->showDeleted()        
        ->dontExec();

        //dd($m->get());
        dd($m->dd());
    }

    function or_otro_bug(){
        $m = DB::table('products')    

        ->whereRegEx('name', 'a$')  // <--- impone un 'AND' y no debería
        ->orWhere(['description', NULL, 'IS NOT'])
        
        ->showDeleted()        
        ->dontExec();

        //dd($m->get());
        dd($m->dd());
    }

    function or_otro(){
        $m = DB::table('products')    

        ->group(function($q){
            $q->whereRegEx('name', 'a$');
        })
        
        ->orWhere(['description', NULL, 'IS NOT'])
        
        ->showDeleted()        
        ->dontExec();

        //dd($m->get());
        dd($m->dd());
    }

    function or_otro2(){
        $m = DB::table('products')
    
        ->group(function($q){
            $q->whereRegEx('name', 'a$');
        })

        ->or(function($q){
            $q->where(['cost', 100, '<=']);
        })   
        
        
        ->showDeleted()        
        ->dontExec();

        //dd($m->get());
        dd($m->dd());
    }

    function test000001(){
        $m = DB::table('products')
    
        ->group(function($q){
            $q->where(['description', NULL, 'IS NOT'])
            ->where(['id', 90]);
        })
        
        ->or(function($q){
            $q->where(['cost', 100, '<=']);
        })     
        ->showDeleted()        
        ->dontExec();

        //dd($m->get());
        dd($m->dd());
    }

    function notor_whereraw2(){
        $m = DB::table('products')

        ->where(['belongs_to', 150, '>'])
        ->not(function($q) {
            $q->where([
                ['cost', 100, '<='],
                ['description', NULL, 'IS NOT']
            ])
            ->or(function($q){
                $q->whereRegEx('name', 'a$');
            });             
        })
        ->dontExec()
        ->where(['size', '1L', '>=']);

        //dd($m->get());
        dd($m->dd());
    }   


    /*
        "SELECT id, name, cost, size, description, belongs_to FROM products WHERE
        (cost > 50 AND id <= 190) AND 
        (active = 1 OR (name LIKE '%a%')) AND 
        belongs_to > 1;"
    */
    function or_whereraw()
    {
        $m = (new Model())
        ->table('products')

        ->where([
            ['cost', 50, '>'], // AND
            ['id', 190, '<=']
        ]) 
        // AND
        ->group(function($q){  
            $q->where(['active', 1])
            // OR
            ->orWhereRaw('name LIKE ?', ['%a%']);  
        })
        // AND
        ->where(['belongs_to', 1, '>'])
        
        ->select(['id', 'name', 'cost', 'size', 'description', 'belongs_to']);

       dd($m->get()); 
	   var_dump($m->dd());
    }

    function when(){
        $lastname = 'Bozzo';

        $m = DB::table('users')
        ->when($lastname, function ($q) use ($lastname) {
            $q->where(['lastname', $lastname]);
        });

        dd($m->get());
        dd($m->dd());
    }

    function when2(){
        $sortBy = ['name' => 'ASC'];

        $m = DB::table('products')
        ->when($sortBy, function ($q) use ($sortBy) {
            $q->orderBy($sortBy);
        }, function ($q) {
            $q->orderBy(['id' => 'DESC']);
        });

        dd($m->get());
        dd($m->dd());
    }
    

    function where_col(){
        $m = (DB::table('users'))
        ->whereColumn('firstname', 'lastname', '=');  

        dd($m->get()); 
	    var_dump($m->dd());
    }
   

    // SELECT * FROM products WHERE ((cost < IF(size = "1L", 300, 100) AND size = '1L' ) AND belongs_to = 90) AND deleted_at IS NULL ORDER BY cost ASC
    function where_raw(){
        $m = DB::table('products')
        ->where(['belongs_to' => 90])
        ->whereRaw('cost < IF(size = "1L", ?, 100) AND size = ?', [300, '1L'])
        ->orderBy(['cost' => 'ASC']);

        dd($m->get()); 
	    var_dump($m->dd());
    }

    /*
        SELECT * FROM products WHERE 

        (
            cost < IF(size = "1L", 300, 100) AND 
            size = '1L'
        ) AND 

        belongs_to = 90 AND 

        (
            size = '1L' OR (cost <= 550 AND cost >= 100)
        ) AND 

        deleted_at IS NULL 


        ORDER BY cost ASC;

    */
    function where_raw1(){
        $m = DB::table('products')
        
        ->where(['belongs_to', 90])

        ->group(function($q){
        	$q->where(['size', '1L'])
	          ->orWhere([
	            ['cost', 550, '<='],
	            ['cost', 100, '>=']
	        ]);
        })
        
        // AND WHERE(...)
        ->whereRaw('cost < IF(size = "1L", ?, 100) AND size = ?', [300, '1L'])
        
        ->orderBy(['cost' => 'ASC']);

        dd($m->get()); 
	    var_dump($m->dd());
    }

    function where_raw1b()
    {
        $m = (new Model())
        ->table('products')

        ->group(function($q){  // <-- group *
           $q->where([
                ['cost', 100, '>'],
                ['id', 50, '<']
            ]) 
            // OR
            ->orWhere([
                ['cost', 100, '<='],
                ['description', NULL, 'IS NOT']
            ]);  
        })
        
        // AND
        ->where(['belongs_to', 150, '>'])

        // AND WHERE (...)
        ->whereRaw('cost < IF(size = "1L", ?, 100) AND size = ?', [300, '1L'])
        
        ->select(['id', 'cost', 'size', 'description', 'belongs_to']);

       dd($m->get()); 
	   var_dump($m->dd());
    }

    /*
        Dentro de un group() no funciona whereRaw()

        Tampoco funciona whereIn() dentro de grupos.

        Invalid parameter number: number of bound variables does not match number of tokens in /home/www/az/app/core/Model.php:xxxx

    */
    function where_raw1c()
    {
        $m = (new Model())
        ->table('products')

        ->group(function($q){  // <-- group *
           $q->whereRaw('cost < IF(size = "1L", ?, 100) AND size = ?', [300, '1L']) // falla
            // OR
             ->orWhere([
                ['cost', 100, '<='],
                ['description', NULL, 'IS NOT']
            ]);  
        })
        
        // AND
        ->where(['belongs_to', 150, '>'])        
        
        ->select(['id', 'cost', 'size', 'description', 'belongs_to']);

       dd($m->get()); 
	   var_dump($m->dd());
    }


   
    /*
        SELECT * FROM products WHERE EXISTS (SELECT 1 FROM users WHERE products.belongs_to = users.id AND users.lastname IS NOT NULL);
    */
    function where_raw2(){
        dd(DB::table('products')->showDeleted()
        ->whereRaw('EXISTS (SELECT 1 FROM users WHERE products.belongs_to = users.id AND users.lastname = ?  )', ['AB'])
        ->get());
    }

    function regex(){
        $m = DB::table('products')
        ->whereRegEx('name', 'a$');

        dd($m->get());
        dd($m->dd());
    }

    function regex2(){
        $m = DB::table('products')
        ->whereNotRegEx('name', 'a$');

        dd($m->get());
        dd($m->dd());
    }


    /*
        WHERE EXISTS

        SELECT * FROM products WHERE EXISTS (SELECT 1 FROM users WHERE products.belongs_to = users.id AND users.lastname IS NOT NULL);
    */
    function where_exists(){
        $m = DB::table('products')->showDeleted()
        ->whereExists('(SELECT 1 FROM users WHERE products.belongs_to = users.id AND users.lastname = ?)', ['AB']);

        dd($m->get());
        dd($m->dd());
    }

    /*
        SELECT * FROM products WHERE 1 = 1 AND deleted_at IS NULL ORDER BY cost ASC, id DESC LIMIT 1, 4
    */
    function order(){    
        dd(DB::table('products')->orderBy(['cost'=>'ASC', 'id'=>'DESC'])->take(4)->offset(1)->get());

        dd(DB::table('products')->orderBy(['cost'=>'ASC'])->orderBy(['id'=>'DESC'])->take(4)->offset(1)->get());

        dd(DB::table('products')->orderBy(['cost'=>'ASC'])->take(4)->offset(1)->get(null, ['id'=>'DESC']));

        dd(DB::table('products')->orderBy(['cost'=>'ASC'])->orderBy(['id'=>'DESC'])->take(4)->offset(1)->get());

        dd(DB::table('products')->take(4)->offset(1)->get(null, ['cost'=>'ASC', 'id'=>'DESC']));
    }

    /*
        RAW
        
        SELECT * FROM products WHERE 1 = 1 AND deleted_at IS NULL ORDER BY locked + active ASC
    */
    function order2(){
        dd(DB::table('products')->orderByRaw('locked * active DESC')->get()); 
    }

    function grouping(){
        dd(DB::table('products')->where([ 
            ['cost', 100, '>=']
        ])->orderBy(['size' => 'DESC'])
        ->groupBy(['size'])
        ->select(['size'])
        //->take(5)
        //->offset(5)
        ->avg('cost'));
    }


    function where(){        

        // Ok
        dd(DB::table('products')->where([ 
            ['cost', 200, '>='],
            ['cost', 270, '<='],
            ['belongs_to',  90]
        ])->get());  
        

        /*    
        // No es posible mezclar arrays asociativos y no-asociativos 
        dd(DB::table('products')->where([ 
            ['cost', 200, '>='],
            ['cost', 270, '<='],
            ['belongs_to' =>  90]
        ])->get());
        */        

        // Ok
        dd(DB::table('products')
        ->where([ 
                ['cost', 150, '>='],
                ['cost', 270, '<=']            
            ])
        ->where(['belongs_to' =>  90])->get());         
    }
        
    function having(){  
        dd(DB::table('products')
			//->dontExec()
            ->groupBy(['size'])
            ->having(['AVG(cost)', 150, '>='])
            ->select(['size'])
			->selectRaw('AVG(cost)')
			->get());
			
		dd(DB::getLog()); 
    }  

	/*
		Array
		(
			[0] => stdClass Object
				(
					[c] => 3
					[name] => Agua
				)

			[1] => stdClass Object
				(
					[c] => 5
					[name] => Vodka
				)

		)
		
		SELECT COUNT(name) as c, name FROM products WHERE deleted_at IS NULL GROUP BY name HAVING c >= 3
	*/	
	function having0(){  
        dd(DB::table('products')
			//->dontExec()
            ->groupBy(['name'])
            ->having(['c', 3, '>='])
            ->select(['name'])
			->selectRaw('COUNT(name) as c')
			->get());
			
		dd(DB::getLog()); 
    }  
	
	/*
		Array
		(
			[0] => stdClass Object
				(
					[c] => 5
					[name] => Agua 
				)

			[1] => stdClass Object
				(
					[c] => 3
					[name] => Ron
				)

			[2] => stdClass Object
				(
					[c] => 9
					[name] => Vodka
				)

		)

		SELECT COUNT(name) as c, name FROM products GROUP BY name HAVING c >= 3
	*/
	function havingx(){  
        dd(DB::table('products')->showDeleted()
			//->dontExec()
            ->groupBy(['name'])
            ->having(['c', 3, '>='])
            ->select(['name'])
			->selectRaw('COUNT(name) as c')
			->get());
			
		dd(DB::getLog()); 
    }  

    /*       
        En caso de tener múltiples condiciones se debe enviar un 
        array de arrays pero para una sola condición basta con enviar un simple array

        Cuando la condición es por igualdad (ejemplo: HAVING cost = 100), no es necesario
        enviar el operador "=" ya que es implícito y en este caso se puede usar un array asociativo:

            ->having(['cost' => 100])

        en vez de

            ->having(['cost', 100])

        En el caso de múltiples condiciones estas se concatenan implícitamente con "AND" excepto 
        se espcifique "OR" como segundo parámetro de having()    
    */     
	
	/*
		SELECT cost, size FROM products WHERE deleted_at IS NULL GROUP BY cost,size HAVING cost = 100
	*/
    function having1(){        
        dd(DB::table('products')
            ->groupBy(['cost', 'size'])
            ->having(['cost', 100])
            ->get(['cost', 'size']));
		
		dd(DB::getLog()); 
    }    
	
	// SELECT cost, size FROM products GROUP BY cost,size HAVING cost = 100
	function having1b(){        
        dd(DB::table('products')->showDeleted()
            ->groupBy(['cost', 'size'])
            ->having(['cost', 100])
            ->get(['cost', 'size']));
		
		dd(DB::getLog()); 
    }   
	
    /*
        HAVING ... OR ... OR ...

        SELECT  cost, size, belongs_to FROM products WHERE deleted_at IS NULL GROUP BY cost,size,belongs_to HAVING belongs_to = 90 AND (cost >= 100 OR size = '1L') ORDER BY size DESC
    */
    function having2(){
        dd(DB::table('products')
            ->groupBy(['cost', 'size', 'belongs_to'])
            ->having(['belongs_to', 90])
            ->having([  
                        ['cost', 100, '>='],
                        ['size' => '1L'] ], 
            'OR')
            ->orderBy(['size' => 'DESC'])
            ->get(['cost', 'size', 'belongs_to'])); 
    }

    /*
        OR HAVING
    
        SELECT  cost, size, belongs_to FROM products WHERE deleted_at IS NULL GROUP BY cost,size,belongs_to HAVING  belongs_to = 90 OR  cost >= 100 OR  size = '1L'  ORDER BY size DESC
    */
    function having2b(){
        dd(DB::table('products')
            ->groupBy(['cost', 'size', 'belongs_to'])
            ->having(['belongs_to', 90])
            ->orHaving(['cost', 100, '>='])
            ->orHaving(['size' => '1L'])
            ->orderBy(['size' => 'DESC'])
            ->get(['cost', 'size', 'belongs_to'])); 
    }

    /*
        SELECT  cost, size, belongs_to FROM products WHERE deleted_at IS NULL GROUP BY cost,size,belongs_to HAVING  belongs_to = 90 OR  (cost >= 100 AND size = '1L')  ORDER BY size DESC
    */
    function having2c(){
        dd(DB::table('products')
            ->groupBy(['cost', 'size', 'belongs_to'])
            ->having(['belongs_to', 90])
            ->orHaving([  
                        ['cost', 100, '>='],
                        ['size' => '1L'] ] 
            )
            ->orderBy(['size' => 'DESC'])
            ->get(['cost', 'size', 'belongs_to'])); 
    }

    /*
        RAW HAVING
    */
    function having3(){
        dd(DB::table('products')
            ->selectRaw('SUM(cost) as total_cost')
            ->where(['size', '1L'])
            ->groupBy(['belongs_to']) 
            ->havingRaw('SUM(cost) > ?', [500])
            ->limit(3)
            ->offset(1)
            ->get());
    }

    /*
        SELECT * FROM other_permissions as op 
        
        INNER JOIN folders ON op.folder_id=folders.id 
        INNER JOIN users ON folders.belongs_to=users.id 
        INNER JOIN user_roles ON users.id=user_roles.user_id 
        
        WHERE (guest = 1 AND table = \'products\' AND r = 1) 
        ORDER BY users.id DESC;
    */
    function joins(){
        $m = (new Model())->table('other_permissions', 'op')
        ->join('folders', 'op.folder_id', '=',  'folders.id')
        ->join('users', 'folders.belongs_to', '=', 'users.id')
        ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
        ->where([
            ['guest', 1],
            ['table', 'products'],
            ['r', 1]
        ])
        ->orderByRaw('users.id DESC')
        ->dontExec();  
        
        dd($m->dd()); 
    }

    
    function j(){
        $m = DB::table('products')
        ->join('products_product_categories', 'products.id', '=',  'products_product_categories.product_id')
        ->join('product_comments', 'products.id', '=', 'product_comments.product_id');

        dd($m->get()); 
        dd($m->dd()); 
    }    

    function j_auto(){
        $m = DB::table('products')
        ->join('products_product_categories')
        ->join('product_comments');

        dd($m->get()); 
        dd($m->dd()); 
    }

    function j1(){
        $m = DB::table('books')
        ->join('book_reviews', 'book_reviews.book_id', '=',  'books.id')
        ->join('users as authors', 'authors.id', '=', 'books.author_id')
        ->join('users as editors', 'editors.id', '=', 'books.editor_id');

        dd($m->get()); 
        dd($m->dd()); 
    }    

   
    function j1_auto(){

        $m = DB::table('books')
        ->join('book_reviews')
        ->join('users as authors')
        ->join('users as editors');

        dd($m->get()); 
        dd($m->dd()); 
          
        /*
            SELECT * FROM books 
            INNER JOIN book_reviews     ON book_reviews.book_id=books.id 
            INNER JOIN users as authors ON authors.id=books.author_id 
            INNER JOIN users as editors ON editors.id=books.editor_id;
        */

    }    



    function j2(){
        $m = DB::table('users')
        ->join('user_sp_permissions', 'users.id', '=',  'user_sp_permissions.user_id')
        ->join('sp_permissions', 'sp_permissions.id', '=', 'user_sp_permissions.id')

        ->select(['sp_permissions.name as perm', 'username', 'active']);

        //dd($m->get()); 
        dd($m->dd()); 
    }

    function j2_auto(){
        $m = DB::table('users')
        ->join('user_sp_permissions')
        ->join('sp_permissions')

        ->select(['sp_permissions.name as perm', 'username', 'active']);

        //dd($m->get()); 
        dd($m->dd()); 
    }

    // 'SELECT users.id, users.name, users.email, countries.name as country_name FROM users LEFT JOIN countries ON countries.id=users.country_id WHERE deleted_at IS NULL;'
    function leftjoin(){
        $users = DB::table('users')->select([
            "users.id",
            "users.name",
            "users.email",
            "countries.name as country_name"
        ])
        ->leftJoin("countries", "countries.id", "=", "users.country_id")
        ->dontExec()
        ->get();

        //dd($users);
        dd(DB::getLog());    
    }

    /*
        Se generan ambiguedades sino especifican las tablas tanto en las cláuslas SELECT como el WHERE
    */
    function crossjoin(){
        DB::table('users')
        ->crossJoin('products')
        ->where(['users.id', 90])
        ->unhideAll()
        ->showDeleted()
        ->dontExec()->get();
        
        dd(DB::getLog());    
    }

    function naturaljoin(){
        $m = (new Model())->table('employee')
        ->naturalJoin('department')
        ->unhideAll()
        ->showDeleted()
        ->dontExec();
        
        dd($m->dd());    
    }
 
    // SELECT COUNT(*) from users CROSS JOIN products CROSS JOIN roles;
    function crossjoin2(){
        DB::table('users')->crossJoin('products')->crossJoin('roles')
        ->unhideAll()
        ->showDeleted()
        ->dontExec()->get();
        
        dd(DB::getLog());    
    }

    // SELECT * FROM users CROSS JOIN products CROSS JOIN roles WHERE users.id = 90;'
    function crossjoin2b(){
        DB::table('users')->crossJoin('products')->crossJoin('roles')
        ->where(['users.id', 90])
        ->unhideAll()
        ->showDeleted()
        ->dontExec()->get();
        
        dd(DB::getLog());    
    }


    // SELECT COUNT(*) from users CROSS JOIN products CROSS JOIN roles INNER JOIN user_sp_permissions ON users.id = user_sp_permissions.user_id;
    function crossjoin3(){
        DB::table('users')->crossJoin('products')->crossJoin('roles')
        ->join('user_sp_permissions', 'users.id', '=', 'user_sp_permissions.user_id')
        ->unhideAll()
        ->showDeleted()
        ->dontExec()->get();
        
        dd(DB::getLog());    
    }

    /*

        SELECT ot.*, ld.distance FROM other_table AS ot 
        INNER JOIN location_distance ld ON (ld.fromLocid = ot.fromLocid OR ld.fromLocid = ot.toLocid) AND 
        (ld.toLocid = ot.fromLocid OR ld.toLocid = ot.fromLocid)

    */

    /*
        INNER JOIN location_distance ld1 ON ld1.fromLocid = ot.fromLocid AND ld1.toLocid = ot.toLocid
    */

    /*
        select ot.id,
        ot.fromlocid,
        ot.tolocid,
        ot.otherdata,
        coalesce(ld1.distance, ld2.distance) distance
        from other_table ot
        left join location_distance ld1
        on ld1.fromLocid = ot.toLocid
        and ld1.toLocid = ot.fromLocid 
        left join location_distance ld2
        on ld2.toLocid = ot.toLocid
        and ld2.fromLocid = ot.fromLocid 

        https://stackoverflow.com/questions/11702294/mysql-inner-join-with-or-condition#14824595
    */


    function get_nulls(){    
        // Get products where workspace IS NULL
        dd(DB::table('products')->where(['workspace', null])->get());   
   
        // Or
        dd(DB::table('products')->whereNull('workspace')->get());
    }

    /*
        Debug without exec the query
    */
    function dontExec(){
        DB::table('products')
        ->dontExec() 
        ->where([ 
                ['cost', 150, '>='],
                ['cost', 270, '<=']            
            ])
        ->where(['belongs_to' =>  90])->get(); 
        
        dd(DB::getLog()); 
    }

    /*
        Pretty response 
    */
    function get_users(){
        $array = DB::table('users')->orderBy(['id'=>'DESC'])->get();

        echo '<pre>';
        Factory::response()->setPretty(true)->send($array);
        echo '</pre>';
    }

    function get_user($id){
        $u = DB::table('users');
        $u->unhide(['password']);
        $u->hide(['id', 'username', 'confirmed_email', 'firstname','lastname', 'deleted_at', 'belongs_to']);
        $u->where(['id'=>$id]);

        dd($u->get());
        dd($u->dd2());
    }

    function del_user($id){
        $u = DB::table('users');
        $ok = (bool) $u->where(['id' => $id])->setSoftDelete(false)->delete();
        
        dd($ok);
    }

 
    function update_user($id) {
        $u = DB::table('users');

        $count = $u->where(['firstname' => 'HHH', 'lastname' => 'AAA', 'id' => 17])->update(['firstname'=>'Nico', 'lastname'=>'Buzzi', 'belongs_to' => 17]);
        
        dd($count);
    }

    function update_user2() 
    {
        $firstname = '';
        for ($i=0;$i<20;$i++)
            $firstname .= chr(rand(97,122));

        $lastname = strtoupper($firstname);    

        $u = DB::table('users');

        $ok = $u->where([ [ 'email', 'nano@'], ['deleted_at', NULL] ])
        ->update([ 
                    'firstname' => $firstname, 
                    'lastname' => $lastname
        ]);
        
        dd($ok);
    }

    function update_users() {
        $u = DB::table('users');
        $count = $u->where([ ['lastname', ['AAA', 'Buzzi']] ])->update(['firstname'=>'Nicos']);
        
        dd($count);
    }

    function create_user($username, $email, $password, $firstname, $lastname)
     {        
        for ($i=0;$i<20;$i++)
            $email = chr(rand(97,122)) . $email;
        
        $u = DB::table('users');
        $u->fill(['email']);
        //$u->unfill(['password']);
        $id = $u->create(['username' => $username, 'email'=>$email, 'password'=>$password, 'firstname'=>$firstname, 'lastname'=>$lastname]);
        
        dd($id);
        dd(DB::getLog());
    }

    function fillables(){
        $m = DB::table('files');
        $affected = $m->where(['id' => 240])->update([
            "filename_as_stored" => "xxxxxxxxxxxxxxxxx.jpg"
        ]);

        dd($affected, 'Affected:');

        // Show result
        $rows = DB::table('files')->where(['id' => 240])->get();
        dd($rows);
    }

    function update_products() {
        $p = DB::table('products');
        $count = $p->where([['cost', 100, '<'], ['belongs_to', 90]])->update(['description' => 'x_x']);
        
        dd($count);
    }

    function respuesta(){
        Factory::response()->sendError('Acceso no autorizado', 401, 'Header vacio');
    }
   
      // ok
    function sender(){
        dd(Utils::sendMail('boctulus@gmail.com', 'Pablo ZZ', 'Pruebita', 'Hola!<p/>Esto es una <b>prueba</b><p/>Chau'));     
    }

    function validation_test(){
        $rules = [
            'nombre' => ['type' => 'alpha_spaces_utf8', 'min'=>3, 'max'=>40],
            'username' => ['type' => 'alpha_dash', 'min'=> 3, 'max' => '15'],
            'rol' => ['type' => 'int', 'not_in' => [2, 4, 5]],
            'poder' => ['not_between' => [4,7] ],
            'edad' => ['between' => [18, 100]],
            'magia' => [ 'in' => [3,21,81] ],
            'active' => ['type' => 'bool', 'messages' => [ 'type' => 'Value should be 0 or 1'] ]
        ];
        
        $data = [
            'nombre' => 'Juan Español',
            'username' => 'juan_el_mejor',
            'rol' => 5,
            'poder' => 6,
            'edad' => 101,
            'magia' => 22,
            'active' => 3
        ];

        $v = new Validator();
        dd($v->validate($rules,$data));
    }

    function validacion(){
        $u = DB::table('users');
        dd($u->where(['username' => 'nano_'])->get());
    }

    function validacion1(){
        $u = DB::table('users')->setValidator(new Validator());
        $affected = $u->where(['email' => 'nano@'])->update(['firstname' => 'NA']);
    }

    function validacion2(){
        $u = DB::table('users')->setValidator(new Validator());
        $affected = $u->where(['email' => 'nano@'])->update(['firstname' => 'NA']);
    }

    function validacion3(){
        $p = DB::table('products')->setValidator(new Validator());
        $rows = $p->where(['cost' => '100X', 'belongs_to' => 90])->get();

        dd($rows);
    }

    function validacion4(){
        $p = DB::table('products')->setValidator(new Validator());
        $affected = $p->where(['cost' => '100X', 'belongs_to' => 90])->delete();

        dd($affected);
    }
  
    /*
        Intento #1 de sub-consultas en el WHERE

        SELECT id, name, size, cost, belongs_to FROM products WHERE belongs_to IN (SELECT id FROM users WHERE password IS NULL);

    */
    function sub(){
        $st = DB::table('products')->showDeleted()
        ->select(['id', 'name', 'size', 'cost', 'belongs_to'])
        ->whereRaw('belongs_to IN (SELECT id FROM users WHERE password IS NULL)')
        ->get();

        dd(DB::getLog());  
        dd($st);         
    }

    /*
        Intento #2 de sub-consultas en el WHERE

        SELECT id, name, size, cost, belongs_to FROM products WHERE belongs_to IN (SELECT id FROM users WHERE password IS NULL);

    */
    function sub2(){
        $sub = DB::table('users')
        ->select(['id'])
        ->whereRaw('password IS NULL');

        $st = DB::table('products')->showDeleted()
        ->select(['id', 'name', 'size', 'cost', 'belongs_to'])
        ->whereRaw("belongs_to IN ({$sub->toSql()})")
        ->get();

        dd(DB::getLog());
        dd($st);            
    }

    /*
        Subconsultas en el WHERE --ok

        SELECT id, name, size, cost, belongs_to FROM products WHERE (belongs_to IN (SELECT id FROM users WHERE (confirmed_email = 1) AND password < 100)) AND size = \'1L\';
    */
    function sub3(){
        $sub = DB::table('users')->showDeleted()
        ->select(['id'])
        ->whereRaw('confirmed_email = 1')
        ->where(['password', 100, '<']);

        $res = DB::table('products')->showDeleted()
        ->mergeBindings($sub)
        ->select(['id', 'name', 'size', 'cost', 'belongs_to'])
        ->where(['size', '1L'])
        ->whereRaw("belongs_to IN ({$sub->toSql()})")
        ->get();

        dd($res);  
        dd(DB::getLog());  
    }

    /*
        SELECT  id, name, size, cost, belongs_to FROM products WHERE belongs_to IN (SELECT  users.id FROM users  INNER JOIN user_roles ON users.id=user_roles.user_id WHERE confirmed_email = 1  AND password < 100 AND role_id = 2  )  AND size = '1L' ORDER BY id DESC

    */
    function sub3b(){
        $sub = DB::table('users')->showDeleted()
        ->selectRaw('users.id')
        ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
        ->whereRaw('confirmed_email = 1')
        ->where(['password', 100, '<'])
        ->where(['role_id', 2]);

        $res = DB::table('products')->showDeleted()
        ->mergeBindings($sub)
        ->select(['id', 'name', 'size', 'cost', 'belongs_to'])
        ->where(['size', '1L'])
        ->whereRaw("belongs_to IN ({$sub->toSql()})")
        ->orderBy(['id' => 'desc'])
        ->get();
  
        dd($res);  
        dd(DB::getLog());  
    }

    function sub3c(){
        $sub = DB::table('users')->showDeleted()
        ->selectRaw('users.id')
        ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
        ->whereRaw('confirmed_email = 1')
        ->where(['password', 100, '<'])
        ->where(['role_id', 3]);

        $res = DB::table('products')->showDeleted()
        ->mergeBindings($sub)
        ->select(['size'])
        ->whereRaw("belongs_to IN ({$sub->toSql()})")
        ->groupBy(['size'])
        ->avg('cost');

        dd($res);   
        dd(DB::getLog()); 
    }


    /*
        RAW select

    */

    function sub4(){
        // SELECT COUNT(*) FROM (SELECT  name, size FROM products  GROUP BY size ) as sub 
        //
        // <-- en SQL no tiene sentido.

        try {
            $sub = DB::table('products')
            ->select(['name', 'size'])
            ->groupBy(['size']);

            $m = new Model(true);
            $res = $m->fromRaw("({$sub->toSql()}) as sub")->dontExec()
            ->count();

            dd($sub->toSql(), 'toSql()');
            dd($m->getLastPrecompiledQuery(), 'getLastPrecompiledQuery()');
            dd(DB::getLog(), 'getLog()');   
            dd($res, 'count');  

        } catch (\Exception $e){
            dd($e->getMessage());
            dd($m->dd());
        }
    }

    // SELECT  COUNT(*) FROM (SELECT  id, name, size FROM products  WHERE (cost >= ?) AND deleted_at IS NULL) as sub
    function sub4a(){
        try {
            $sub = DB::table('products')
            ->select(['id', 'name', 'size'])
            ->where(['cost', 150, '>=']);
        
            $m = new Model(true);    
            $res = $m->fromRaw("({$sub->toSql()}) as sub")
            ->mergeBindings($sub)
            ->count();
      
            dd($sub->toSql(), 'toSql()');
            dd($m->getLastPrecompiledQuery(), 'getLastPrecompiledQuery()');
            dd(DB::getLog(), 'getLog()');   
            dd($res, 'count'); 

        } catch (\Exception $e){
            dd($e->getMessage());
            dd($m->dd());
        }    
    }


    function sub4b(){
        $sub = DB::table('products')->showDeleted()
        ->select(['size'])
        ->groupBy(['size']);

        $m = new Model(true);
        $res = $m->fromRaw("({$sub->toSql()}) as sub")->count();

        dd($res);    
    }

    /*
        SELECT  COUNT(*) FROM (SELECT  size FROM products WHERE belongs_to = 90 GROUP BY size ) as sub WHERE 1 = 1
    */
    function sub4c(){
        $sub = DB::table('products')->showDeleted()
        ->select(['size'])
        ->where(['belongs_to', 90])
        ->groupBy(['size']);

        $main = new \simplerest\core\Model(true);
        $res = $main
        ->fromRaw("({$sub->toSql()}) as sub")
        ->mergeBindings($sub)
        ->count();

        dd($res); 
        dd($main->getLastPrecompiledQuery());   
    }

    /*
        FROM RAW

        SELECT  COUNT(*) FROM (SELECT  size FROM products WHERE belongs_to = 90 GROUP BY size ) as sub WHERE 1 = 1
    */
    function sub4d(){
        $sub = DB::table('products')->showDeleted()
        ->select(['size'])
        ->where(['belongs_to', 90])
        ->groupBy(['size']);

        $res = DB::table("({$sub->toSql()}) as sub")
        ->mergeBindings($sub)
        ->count();

        dd($res);    
    }
    
    /*
        Subconsulta (rudimentaria) en el SELECT
    */
    function sub5(){
        $m = DB::table('products')->showDeleted()
        ->select(['name', 'cost'])
        ->selectRaw('cost - (SELECT MAX(cost) FROM products) as diferencia')
        ->where(['belongs_to', 90]);

        $res = $m->get();

        dd($res);
        dd($m->getLastPrecompiledQuery()); 
        dd(DB::getLog()); 
        
    }

    /*
        UNION

        SELECT id, name, description, belongs_to FROM products WHERE belongs_to = 90 UNION SELECT id, name, description, belongs_to FROM products WHERE belongs_to = 4 ORDER by id ASC LIMIT 5;
    */
    function union(){
        $uno = DB::table('products')->showDeleted()
        ->select(['id', 'name', 'description', 'belongs_to'])
        ->where(['belongs_to', 90]);

        $dos = DB::table('products')->showDeleted()
        ->select(['id', 'name', 'description', 'belongs_to'])
        ->where(['belongs_to', 4])
        ->union($uno)
        ->orderBy(['id' => 'ASC'])
        ->limit(5)
        ->get();

        dd($dos);
    }

    function union2(){
        $uno = DB::table('products')->showDeleted()
        ->select(['id', 'name', 'description', 'belongs_to'])
        ->where(['belongs_to', 90]);

        $m2  = DB::table('products')->showDeleted();
        $dos = $m2
        ->select(['id', 'name', 'description', 'belongs_to'])
        ->where(['belongs_to', 4])
        ->where(['cost', 200, '>='])
        ->union($uno)
        ->orderBy(['id' => 'ASC'])
        ->get();

        //dd(DB::getLog());
        //dd($m2->getLastPrecompiledQuery());
        //dd($dos);
    }

    /*
        UNION ALL
    */
    function union_all(){
        $uno = DB::table('products')->showDeleted()
        ->select(['id', 'name', 'description', 'belongs_to'])
        ->where(['belongs_to', 90]);

        $dos = DB::table('products')->showDeleted()
        ->select(['id', 'name', 'description', 'belongs_to'])
        ->where(['cost', 200, '>='])
        ->unionAll($uno)
        ->orderBy(['id' => 'ASC'])
        ->limit(5);

        dd($dos->get());
        dd($dos->dd());
    }
       
    function insert_messages() {
        function get_words($sentence, $count = 10) {
            preg_match("/(?:\w+(?:\W+|$)){0,$count}/", $sentence, $matches);
            return $matches[0];
        }

        $m = DB::table('messages');

        for ($i=0; $i<1500; $i++){

            $name = '';
            for ($i=0;$i<10;$i++){
                $name .= chr(rand(97,122));
            }   

            $email = '';
            for ($i=0;$i<20;$i++){
                $email .= chr(rand(97,122));
            }   

            $email .= '@gmail.com';

            $title = file_get_contents('http://loripsum.net/api/1/short/plaintext/short');
            $title = get_words($title, 10);

            $content = file_get_contents('http://loripsum.net/api/1/long/plaintext/short');

            $phone = '0000000000';

            $m->create([ 
                        'name' => $name, 
                        'email' => $email,
                        'phone' => $phone,
                        'subject' => $title,
                        'content' => $content
            ]);

        }        
    }

    // utiliza FPM, sin probar
    function some_test() {
       ignore_user_abort(true);
       fastcgi_finish_request();

       echo json_encode(['data' => 'Proceso terminado']);
       header('Connection: close');

       sleep(10);
       file_put_contents('output.txt', date('l jS \of F Y h:i:s A')."\n", FILE_APPEND);
    }

    function json(){
        $id = DB::table('collections')->create([
            'entity' => 'messages',
            'refs' => json_encode([195,196]),
            'belongs_to' => 332
        ]);

        Factory::response()->sendJson($id);
    }


    function get_env(){
        dd($_ENV['APP_NAME']);
        dd($_ENV['APP_URL']);
    }


    function test_get(){
        dd(DB::table('products')->first(), 'FIRST'); 
        dd(DB::getLog(), 'QUERY');
    }

    function test_get_raw(){
        $raw_sql = 'SELECT * FROM baz';

        $conn = DB::getConnection();
        
        $st = $conn->prepare($raw_sql);
        $st->execute();

        $result = $st->fetch(\PDO::FETCH_ASSOC);

        // additional casting
        $result['cost'] = (float) $result['cost'];
        
        echo '<pre>';
        var_export($result);
        echo '</pre>';
    }

    function test_raw(){
        $res = DB::select('SELECT * FROM baz');
        dd($res);
    }

    function get_role_permissions(){
        $acl = Factory::acl();

        dd($acl->hasResourcePermission('show_all', ['guest'], 'products'));
        //var_export($acl->getRolePermissions());
    }

    function boom(){
        throw new \Exception('BOOOOM');
    }

    function ops(){
        $this->boom();
    }

    function hi($name = null){
        return 'hi ' . $name;
    }

  
    function xxx(){ 
        dd(Validator::isType('8', 'str'));
    }

    function speed(){
        $rules = [
            'nombre' => ['type' => 'alpha_spaces_utf8', 'min'=>3, 'max'=>40],
            'username' => ['type' => 'alpha_dash', 'min'=> 3, 'max' => '15'],
            'rol' => ['type' => 'int', 'not_in' => [2, 4, 5]],
            'poder' => ['not_between' => [4,7] ],
            'edad' => ['between' => [18, 100]],
            'magia' => [ 'in' => [3,21,81] ],
            'active' => ['type' => 'bool', 'messages' => [ 'type' => 'Value should be 0 or 1'] ]
        ];
        
        $data = [
            'nombre' => 'Juan Español',
            'username' => 'juan_el_mejor',
            'rol' => 5,
            'poder' => 6,
            'edad' => 101,
            'magia' => 22,
            'active' => 3
        ];

        Time::setUnit('MILI');
        $t1 = Time::exec(function() use($data, $rules){ 
            Factory::validador()->validate($rules,$data);
        }, 100); 
        
        dd("Time: $t1 ms");
    }

    function get_con(){
        DB::setConnection('db2');       
        $conn = DB::getConnection();

        $m = new \simplerest\models\ProductsModel($conn);
    }

    /*
        MySql: show status where `variable_name` = 'Threads_connected
        MySql: show processlist;
    */
    function test_active_connnections(){
        dd(DB::countConnections(), 'Number of active connections');

        DB::setConnection('db2');  
        dd(DB::table('users')->count(), 'Users DB2:'); 

        DB::setConnection('db1');  
        dd(DB::table('users')->count(), 'Users DB1');

        DB::setConnection('db2');  
        dd(DB::table('users')->first(), 'Users DB2:');

        dd(DB::countConnections(), 'Number of active connections'); // 2 y no 3 ;)

        DB::closeConnection();
        dd(DB::countConnections(), 'Number of active connections'); // 1

        DB::closeAllConnections();
        dd(DB::countConnections(), 'Number of active connections'); // 0
    }

    function read_table(){
        $tb = 'products';

        $fields = DB::select("SHOW COLUMNS FROM $tb");
        
        $field_names = [];
        $nullables = [];

        foreach ($fields as $field){
            $field_names[] = $field['Field'];
            if ($field['Null']  == 'YES') { $nullables[] = $field['Field']; }
            if ($field['Extra'] == 'auto_increment') { $not_fillable[] = $field['Field']; }
        }

        dd($field_names);
    }

    function zzz(){
        $arr = ['el', 'dia', 'que', 'me', 'quieras'];
        $arr = array_map(function($x){ return "'$x'"; }, $arr);
        
        dd($arr);
        
        //echo implode('-', $arr);
    }

    function speed2(){

        Time::setUnit('MILI');
        //Time::noOutput();
        
        $conn = DB::getConnection();
        $t = Time::exec(function() use ($conn){         
            $sql = "INSERT INTO `baz2` (`name`, `cost`) VALUES ('hhh', '789')";
            $conn->exec($sql);
        }, 1);  
        dd("Time: $t ms");    

        exit;

        $m = (new Model(true))
        ->table('baz2');
        $t = Time::exec(function() use ($m){             
            //$m->setValidator(new Validator());
            //$m->dontExec();

            $id = $m->create([
                'name' => 'BAZ',
                'cost' => '100',
            ]);

        }, 1);  
        dd("Time: $t ms");
        dd($m->getLog());

        /*
        Time::setUnit('MILI');
        //Time::noOutput();

        $this->model_name  = null;
        $this->model_table = 'users';

        $t = Time::exec(function(){ 
            
            $id = DB::table('collections')->create([
                'entity' => 'messages',
                'refs' => json_encode([195,196]),
                'belongs_to' => 332
            ]);

        }, 1);       
        dd("Time: $t ms");
        */
    }


    function speed_show()
    {
        Time::setUnit('MILI');

        $m = (new Model(true))
            ->table('bar')  
            ->where(['uuid', '0fefc2b1-f0d3-47aa-a875-5dbca85855f9'])
            ->select(['uuid', 'price']);

        //dd($m->dd());
        //exit;     

        $t = Time::exec(function() use($m) {
            $row = $m->get();
        }, 1);

        //dd("Time: $t ms");
        Files::logger("Time(show) : $t ms");
    }

    function speed_list()
    {
        Time::setUnit('MILI');

        $m = (new Model(true))
            ->table('bar')  
            ->select(['uuid', 'price'])
            ->take(10);

        //dd($m->dd());
        //exit;         

        $t = Time::exec(function() use($m) {
            $row = $m->get();
        }, 1);

        //dd("Time: $t ms");
        Files::logger("Time(list) : $t ms");
    }

    function get_bulk(){
        $t1a = [];
        $t2a = [];

        Time::setUnit('MILI');

        $m1 = (new Model(true))
            ->table('bar')  
            ->select(['uuid', 'price'])
            ->take(10);

        $m2 = (new Model(true))
            ->table('bar')  
            ->where(['uuid', '0fefc2b1-f0d3-47aa-a875-5dbca85855f9'])
            ->select(['uuid', 'price']);    

        //dd($m->dd());
        //exit;         

        $m3 = DB::select("SELECT AVG(price) FROM bar;");

        for ($i=0; $i<4; $i++){
            $t1a[] = Time::exec(function() use($m1) {
                $m1->get();
            }, 500);

            $t2a[] = Time::exec(function() use($m2) {
                $m2->get();
            }, 500);
        }    
            
        foreach ($t1a as $t1){
            Files::logger("Time(list) : $t1 ms");
        }

        foreach ($t2a as $t2){
            Files::logger("Time(show) : $t2 ms");;
        }    
    }

    function create(){
        $m = (new BarModel(true));

        $name = '    ';
        for ($i=0;$i<46;$i++)
            $name .= chr(rand(97,122));

        $name = str_shuffle($name);

        $email = '@';
        $cnt = rand(10,78);
        for ($i=0;$i<$cnt;$i++)
            $email .= chr(rand(97,122));    

        $email =  chr(rand(97,122)) . str_shuffle($email);

        $data = [
            'name' => $name,
            'price' => rand(5,999) . '.' . rand(0,99),
            'email' => $email,
            'belongs_to' => 1
        ];

        $id = $m->create($data);
        
        //dd($data, 'DATA');
        //dd($id, 'ID');
    }


    function create_bulk(){
        for ($i=0; $i<10000; $i++){
            $this->create();
            usleep((450 + rand(50, 150)) * 1000);
        }
    }


    /*
        Genera excepción con 
        
        PDO::ATTR_EMULATE_PREPARES] = false

    */
    function test000002(){
        $m = DB::table('products')
        ->where([ 
            ['name', ['Vodka', 'Wisky', 'Tekila','CocaCola']], // IN 
            ['locked', 0],
            ['belongs_to', 90]
        ])
        ->whereNotNull('description');

        dd($m->get());
        var_dump(DB::getLog());
        //var_dump($m->dd());
    }

    /*
        Genera excepción con 
        
        PDO::ATTR_EMULATE_PREPARES] = false

    */
    function test000003(){
        $m = DB::table('products')
        /*
        ->where([ 
            ['name', ['Vodka', 'Wisky', 'Tekila','CocaCola']], // IN 
            ['locked', 0],
            ['belongs_to', 90]
        ])
        */
        ->showDeleted()
        //->whereNotNull('description');
        ->where(['description', NULL]);

        dd($m->first());
        var_dump(DB::getLog());
        //var_dump($m->dd());
    }

    /*

        https://www.w3resource.com/mysql/mysql-data-types.php
        https://manuales.guebs.com/mysql-5.0/spatial-extensions.html

    */
    function create_table()
    {       
        //Factory::config()['db_connection_default'] = 'db2';
        $sc = (new Schema('facturas'))

        ->setEngine('InnoDB')
        ->setCharset('utf8')
        ->setCollation('utf8_general_ci')

        ->integer('id')->auto()->unsigned()->pri()
        ->int('edad')->unsigned()
        ->varchar('firstname')
        ->varchar('lastname')->nullable()->charset('utf8')->collation('utf8_unicode_ci')
        ->varchar('username')->unique()
        ->varchar('password', 128)
        ->char('password_char')->nullable()
        ->varbinary('texto_vb', 300)

        // BLOB and TEXT columns cannot have DEFAULT values.
        ->text('texto')
        ->tinytext('texto_tiny')
        ->mediumtext('texto_md')
        ->longtext('texto_long')
        ->blob('codigo')
        ->tinyblob('blob_tiny')
        ->mediumblob('blob_md')
        ->longblob('blob_long')
        ->binary('bb', 255)
        ->json('json_str')

        
        ->int('karma')->default(100)
        ->int('code')->zeroFill()
        ->bigint('big_num')
        ->bigint('ubig')->unsigned()
        ->mediumint('medium')
        ->smallint('small')
        ->tinyint('tiny')
        ->decimal('saldo')
        ->float('flotante')
        ->double('doble_p')
        ->real('num_real')

        ->bit('some_bits', 3)->index()
        ->boolean('active')->default(1)
        ->boolean('paused')->default(true)

        ->set('flavors', ['strawberry', 'vanilla'])
        ->enum('role', ['admin', 'normal'])


        /*
            The major difference between DATETIME and TIMESTAMP is that TIMESTAMP values are converted from the current time zone to UTC while storing, and converted back from UTC to the current time zone when accessd. The datetime data type value is unchanged.
        */

        ->time('hora')
        ->year('birth_year')
        ->date('fecha')
        ->datetime('vencimiento')->nullable()->after('num_real') /* no está funcionando el AFTER */
        ->timestamp('ts')->currentTimestamp()->comment('some comment') // solo un first


        ->softDeletes() // agrega DATETIME deleted_at 
        ->datetimes()  // agrega DATETIME(s) no-nullables created_at y deleted_at

        ->varchar('correo')->unique()

        ->int('user_id')->index()
        ->foreign('user_id')->references('id')->on('users')->onDelete('cascade')
        //->foreign('user_id')->references('id')->on('users')->constraint('fk_uid')->onDelete('cascade')->onUpdate('restrict')
        
        ;

        //dd($sc->getSchema(), 'SCHEMA');
        /////exit;

        $res = $sc->create();
        dd($res, 'Succeded?');
        //var_dump($sc->dd());
    }    

    function alter_table()
    { 
        Schema::FKcheck(false);
        
        $sc = new Schema('facturas');
        //var_dump($sc->columnExists('correo'));
       
        $res = $sc

        
        //->timestamp('vencimiento')
        //->varchar('lastname', 50)->collate('utf8_esperanto_ci')
        //->varchar('username', 50)
        //->column('ts')->nullable()
        //->field('deleted_at')->nullable()
        //->column('correo')->unique()
        // ->field('correo')->default(false)->nullable(true)
        

        //->renameColumn('karma', 'carma')
        //->field('id')->index()
        //->renameIndex('id', 'idx')
        ->dropColumn('saldo')
        //->dropIndex('correo')
        //->dropPrimary('id')
        //->renameTable('boletas')
        //->dropTable()

        //->field('password_char')->default(false)->nullable(false)
        

        /*
         creo campos nuevos
        */
        
        //->varchar('nuevo_campito', 50)->nullable()->after('ts')
        //->text('aaa')->first()->nullable()

        //->dropFK('facturas_ibfk_1')
        //->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('restrict')
                
        //->field('id')->auto(false)
        

        ->change();

        Schema::FKcheck(true);
        dd($sc->dd());
    }

    function has_table(){
        dd(Schema::hasTable('users'));
        dd((new Schema('users'))->tableExists());
    }

    function xxy(){
        $str = "`lastname` varchar(60) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT 'NN',
        ";
        dd($str, 'STR');

        $charset    = Strings::slice($str, '/CHARACTER SET ([a-z0-9_]+)/');
        dd($charset, 'CHARSET');
        dd($str, 'STR');

        $collation  = Strings::slice($str, '/COLLATE ([a-z0-9_]+)/');
        dd($collation, 'COLLATION');
        dd($str, 'STR');
        
        $default    = Strings::slice($str, '/DEFAULT (\'?[a-zA-Z0-9_]+\'?)/');
        dd($default, "DEFAULT");
        dd($str, 'STR');

        $nullable   = Strings::slice($str, '/(NOT NULL)/') == NULL;
        dd($nullable, "NULLABLE");
        dd($str, 'STR');

        $auto       = Strings::slice($str, '/(AUTO_INCREMENT)/') == 'AUTO_INCREMENT';
        dd($auto, "AUTO");
        dd($str, 'STR');
    }

    function xxxz(){
        $str = 'CONSTRAINT `facturas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE';
        dd($str, 'STR');

        $constraint = Strings::slice($str, '/CONSTRAINT `([a-zA-Z0-9_]+)` /', function($s){
            //var_dump($s);
                return ($s == null) ? 'DEFAULT' : $s;
            }); 

        dd($constraint, 'CONSTRAINT');    

        //dd($constraint);
        //exit; //
        dd($str, 'STR');

        $primary = Strings::slice($str, '/PRIMARY KEY \(([a-zA-Z0-9_`,]+)\)/');
        dd($str, 'STR');	
        dd($primary, 'PRIMARY');

        /*

        Compuesto:
        UNIQUE KEY `correo` (`correo`,`hora`) USING BTREE,

        */
        $unique  = Strings::sliceAll($str, '/UNIQUE KEY `([a-zA-Z0-9_]+)` \(([a-zA-Z0-9_`,]+)\)/');
        dd($str, 'STR');	
        dd($unique, 'UNIQUE');					

        /*
            CONSTRAINT `facturas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCAD

        */
        $fk     = Strings::slice($str, '/FOREIGN KEY \(`([a-zA-Z0-9_]+)`\)/');
        $fk_ref = Strings::slice($str, '/REFERENCES `([a-zA-Z0-9_]+)`/');

        dd($str, 'STR');	
        dd($fk, 'FK');
        dd($fk, 'REFERENCES');

        /*
        IDEM
        */
        $index   = Strings::sliceAll($str, '/KEY `([a-zA-Z0-9_]+)` \(([a-zA-Z0-9_`,]+)\)/');
        dd($str, 'STR');
        dd($index, 'INDEX');


    }


    function test_get_conn(){
        Factory::config()['db_connection_default'] = 'db1';

        dd(DB::select('SELECT * FROM users'));
    }

    function test_conn2(){
        Factory::config()['db_connection_default'] = 'db2';

        $sc = new Schema('cables');

        $sc
        ->int('id')->unsigned()->auto()->pri()
        ->varchar('nombre', 40)
        ->float('calibre')

        ->create();
    }

    function test_route(){
        /*
        Route::get('dumbo/kalc', function(){
            echo 'Hello from Kalc!';
        })->name('dumbo.kalc');
        
        
        Route::get('has_table', 'DumbController@has_table')
        ->name('dumbo.has_table');
        */
    
        //dd(route('dumbo.has_table'), 'URL');
        //dd(route('dumbo.kalc'), 'URL');
    }

    function curl(){
        define('HOST', $this->config['APP_URL']);
        define('BASE_URL', HOST .'/');

        $url = BASE_URL . "api/v1/auth/login";

        $credentials = [
            'email' => "tester3@g.c",
            'password' => "gogogo8"
        ];

        if ($credentials == []){
            throw new \Exception("Empty credentials");
        }

		$data = json_encode($credentials);

        $com = <<<HDOC
        curl -s --location --request POST '$url' \
        --header 'Content-Type: text/plain' \
        --data-raw '$data' /tmp/output.html
        HDOC;

        $response = json_decode(exec($com), true);

        $data      = $response['data'] ?? [];
        $http_code = $response['status_code'];
        $error_msg = $response['error'];

       dd($data, 'data');
       dd($http_code, 'http code');
       dd($error_msg, 'error');


    }       

}