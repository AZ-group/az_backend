<?php

namespace simplerest\core\api\v1;

use Exception;
use simplerest\core\Controller;
use simplerest\core\interfaces\IAuth;
use simplerest\libs\Factory;
use simplerest\libs\DB;
use simplerest\libs\Utils;
use simplerest\models\UsersModel;
use simplerest\models\RolesModel;
use simplerest\models\UserRolesModel;
use simplerest\libs\Debug;
use simplerest\libs\Validator;
use simplerest\core\exceptions\InvalidValidationException;


class AuthController extends Controller implements IAuth
{
    function __construct()
    { 
        header('Access-Control-Allow-Credentials: True');
        header('Access-Control-Allow-Headers: Origin,Content-Type,X-Auth-Token,AccountKey,X-requested-with,Authorization,Accept, Client-Security-Token,Host,Date,Cookie,Cookie2'); 
        header('Access-Control-Allow-Methods: POST,OPTIONS'); 
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=UTF-8');

        parent::__construct();
    }
       
    protected function gen_jwt(array $props, string $token_type, int $expires_in = null){
        $time = time();

        $payload = [
            'alg' => $this->config[$token_type]['encryption'],
            'typ' => 'JWT',
            'iat' => $time, 
            'exp' => $time + ($expires_in != null ? $expires_in : $this->config[$token_type]['expiration_time']),
            'ip'  => $_SERVER['REMOTE_ADDR']
        ];
        
        $payload = array_merge($payload, $props);

        return \Firebase\JWT\JWT::encode($payload, $this->config[$token_type]['secret_key'],  $this->config[$token_type]['encryption']);
    }

    protected function gen_jwt_email_conf(string $email){
        $time = time();

        $payload = [
            'alg' => $this->config['email']['encryption'],
            'typ' => 'JWT',
            'iat' => $time, 
            'exp' => $time + $this->config['email']['expires_in'],
            'ip'  => $_SERVER['REMOTE_ADDR'],
            'email' => $email
         ];

        return \Firebase\JWT\JWT::encode($payload, $this->config['email']['secret_key'],  $this->config['email']['encryption']);
    }

    protected function gen_jwt_rememberme($uid, $roles, $perms, $confirmed_email){
        $time = time();

        $payload = [
            'alg' => $this->config['email']['encryption'],
            'typ' => 'JWT',
            'iat' => $time, 
            'exp' => $time + $this->config['email']['expires_in'],
            'ip'  => $_SERVER['REMOTE_ADDR'],
            'roles' => $roles,
            'permissions' => $perms,
            'uid' => $uid,
            'confirmed_email' => $confirmed_email
         ];

        return \Firebase\JWT\JWT::encode($payload, $this->config['email']['secret_key'],  $this->config['email']['encryption']);
    }

    private function fetchRoles($uid) : Array {
        $rows = DB::table('user_roles')->setFetchMode('ASSOC')->where(['belongs_to', $uid])->select(['role_id as role'])->get();	

        //Debug::dd(DB::getQueryLog());

        $roles = [];
        if (count($rows) != 0){            
            $r = new RolesModel();
        
            foreach ($rows as $row){
                $roles[] = $r->getRoleName($row['role']);
            }
        }

        return $roles;
    }

    private function fetchPermissions($uid) : Array {
        $_permissions = DB::table('permissions')->setFetchMode('ASSOC')->select(['tb', 'can_create as c', 'can_read as r', 'can_update as u', 'can_delete as d', 'can_list as l'])->where(['user_id' => $uid])->get();

        //print_r($rows);
        //exit; //

        $perms = [];
        foreach ((array) $_permissions as $p){
            $tb = $p['tb'];
            $perms[$tb] = $p['l'] * 16 + $p['c'] * 8 + $p['r'] * 4 + $p['u'] * 2 + $p['d'];
        }

        return $perms;
    }

    function login()
    {
        if (!in_array($_SERVER['REQUEST_METHOD'], ['POST','OPTIONS']))
            Factory::response()->sendError('Incorrect verb ('.$_SERVER['REQUEST_METHOD'].'), expecting POST',405);

        $data  = Factory::request()->getBody(false);

        if ($data == null)
            return;
            
        $email = $data->email ?? null;
        $username = $data->username ?? null;  
        $password = $data->password ?? null;         
        
        if (empty($email) && empty($username) ){
            Factory::response()->sendError('email or username are required',400);
        }else if (empty($password)){
            Factory::response()->sendError('password is required',400);
        }

        try {              

            $row = DB::table('users')->setFetchMode('ASSOC')->unhide(['password'])
            ->where([ 'email'=> $email, 'username' => $username ], 'OR')
            ->setValidator((new Validator())->setRequired(false))  
            ->first();

            if (!$row)
                throw new Exception("Incorrect username / email or password");

            $hash = $row['password'];

            if (!password_verify($password, $hash))
                Factory::response()->sendError('Incorrect username / email or password', 401);

            if ($row['active'] == 0) // enabled?
                Factory::response()->sendError('Non authorized', 403, 'Unactivated or deactivate account');

            $confirmed_email = $row['confirmed_email']; 
            $username = $row['username'];   
    
            // Fetch roles && permissions
            $uid = $row['id'];

            $roles = $this->fetchRoles($uid);
            $perms = $this->fetchPermissions($uid);

            $access  = $this->gen_jwt([ 'uid' => $uid, 
                                        'roles' => $roles, 
                                        'confirmed_email' => $confirmed_email,
                                        'permissions' => $perms //
            ], 'access_token');

            // el refresh no debe llevar ni roles ni permisos por seguridad !
            $refresh = $this->gen_jwt([ 'uid' => $uid, 
                                        'confirmed_email' => $confirmed_email
            ], 'refresh_token');

            Factory::response()->send([ 
                                        'access_token'=> $access,
                                        'token_type' => 'bearer', 
                                        'expires_in' => $this->config['access_token']['expiration_time'],
                                        'refresh_token' => $refresh,   
                                        'roles' => $roles,
                                        'uid' => $uid
                                        ]);
          
        } catch (InvalidValidationException $e) { 
            Factory::response()->sendError('Validation Error', 400, json_decode($e->getMessage()));
        } catch(\Exception $e){
            Factory::response()->sendError($e->getMessage());
        }	
        
    }


    // Recibe un refresh_token y en el body un campo "impersonate" 
    function impersonate()
    {
        if (!in_array($_SERVER['REQUEST_METHOD'], ['POST','OPTIONS']))
            Factory::response()->sendError('Incorrect verb ('.$_SERVER['REQUEST_METHOD'].'), expecting POST',405);

        $data  = Factory::request()->getBody(false);

        if ($data == null)
            return;
            
        $impersonate = $data->impersonate ?? null;

        $request = Factory::request();

        $headers = $request->headers();
        $auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (empty($auth)){
            Factory::response()->sendError('Authorization not found',400);
        }

        //print_r($auth);

        try {                                      
            list($refresh) = sscanf($auth, 'Bearer %s');

            $payload = \Firebase\JWT\JWT::decode($refresh, $this->config['refresh_token']['secret_key'], [ $this->config['refresh_token']['encryption'] ]);
            
            if (empty($payload))
                Factory::response()->sendError('Unauthorized!',401);                     

            if (empty($payload->uid)){
                Factory::response()->sendError('uid is needed',400);
            }

            $roles = $this->fetchRoles($payload->uid);

            if (!in_array("admin",$roles) && !(isset($payload->impersonated_by) && !empty($payload->impersonated_by)) ){
                Factory::response()->sendError('Unauthorized!',401, 'Impersonate requires you are admin');
            }    

            if ($impersonate == 'guest'){
                $uid = -1;
                $roles = ['guest'];
                $confirmed_email = 0;
                $perms = [];

            } else {
    
                $uid = $impersonate;

                $row = DB::table('users')
                ->where([ 'id' =>  $uid ] ) 
                ->first();

                if (!$row)
                    throw new Exception("User to impersonate does not exist");

            
                $confirmed_email = $row['confirmed_email'];    

                $roles = $this->fetchRoles($uid);
                $perms = $this->fetchPermissions($uid);
            }    

            $impersonated_by = $payload->impersonated_by ?? $payload->uid;
            
            $access  = $this->gen_jwt(['uid' => $uid, 
                                        'confirmed_email' => $confirmed_email,          
                                        'roles' => $roles, 
                                        'permissions' => $perms,
                                        'impersonated_by' => $impersonated_by
            ], 'access_token');

            $refresh  = $this->gen_jwt(['uid' => $uid, 
                                        'confirmed_email' => $confirmed_email,
                                        'impersonated_by' => $impersonated_by
            ], 'refresh_token');

            Factory::response()->send([ 
                                        'access_token'=> $access,
                                        'refresh_token' => $refresh,
                                        'token_type' => 'bearer', 
                                        'expires_in' => $this->config['access_token']['expiration_time'],
                                        'roles' => $roles,
                                        'uid' => $uid,
                                        'impersonated_by' => $impersonated_by
                                        ]);      

        } catch (\Exception $e) {
            Factory::response()->sendError($e->getMessage(), 400);
        }	
                                                    
    }

    // a diferencia de token() si bien renueva el access_token no lo hace a partir de ....
    function stop_impersonate() 
    {
        if (!in_array($_SERVER['REQUEST_METHOD'], ['POST','OPTIONS']))
            Factory::response()->sendError('Incorrect verb ('.$_SERVER['REQUEST_METHOD'].'), expecting POST',405);

        $request = Factory::request();

        $headers = $request->headers();
        $auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (empty($auth)){
            Factory::response()->sendError('Authorization not found',400);
        }

        try {                                      
            list($refresh) = sscanf($auth, 'Bearer %s');

            $payload = \Firebase\JWT\JWT::decode($refresh, $this->config['refresh_token']['secret_key'], [ $this->config['refresh_token']['encryption'] ]);
            
            if (empty($payload))
                Factory::response()->sendError('Unauthorized!',401);                     

            if (empty($payload->uid)){
                Factory::response()->sendError('uid is needed',400);
            }

            if (empty($payload->impersonated_by)){
                Factory::response()->sendError('Unauthorized!', 401, 'There is no admin behind this');
            }
            
        } catch (\Exception $e) {
            Factory::response()->sendError($e->getMessage(), 400);
        }	

        $uid = $payload->impersonated_by;
        $roles = ["admin"];

        //////
        
        try {              
            
            $access  = $this->gen_jwt([ 'uid' => $uid, 
                                        'confirmed_email' => 1,     
                                        'roles' => $roles, 
                                        'permissions' => []
            ], 'access_token');

            $refresh = $this->gen_jwt([ 'uid' => $uid, 
                                        'confirmed_email' => 1
            ], 'refresh_token');

            Factory::response()->send([ 
                                        'uid' => $uid,           
                                        'access_token'=> $access,
                                        'token_type' => 'bearer', 
                                        'expires_in' => $this->config['access_token']['expiration_time'],
                                        'refresh_token' => $refresh,   
                                        'roles' => $roles
                                    ]);
          
        } catch (InvalidValidationException $e) { 
            Factory::response()->sendError('Validation Error', 400, json_decode($e->getMessage()));
        } catch(\Exception $e){
            Factory::response()->sendError($e->getMessage());
        }	
    }

    /*
        Access Token renewal
    */	
    function token()
    {
        if (!in_array($_SERVER['REQUEST_METHOD'], ['POST','OPTIONS']))
            Factory::response()->sendError('Incorrect verb ('.$_SERVER['REQUEST_METHOD'].'), expecting POST',405);

        $request = Factory::request();

        $headers = $request->headers();
        $auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (empty($auth)){
            Factory::response()->sendError('Authorization not found',400);
        }

        //print_r($auth);

        try {                                      
            // refresh token
            list($refresh) = sscanf($auth, 'Bearer %s');

            $payload = \Firebase\JWT\JWT::decode($refresh, $this->config['refresh_token']['secret_key'], [ $this->config['refresh_token']['encryption'] ]);

            if (empty($payload))
                Factory::response()->sendError('Unauthorized!',401);                     

            if (empty($payload->uid)){
                Factory::response()->sendError('uid is needed',400);
            }

            if ($payload->exp < time())
                Factory::response()->sendError('Token expired, please log in',401);

            $uid = $payload->uid;
            $impersonated_by = $payload->impersonated_by ?? null;

            $roles = $this->fetchRoles($uid);
            $permissions = $this->fetchPermissions($uid);

            $access  = $this->gen_jwt([ 'uid' => $payload->uid,
                                        'confirmed_email' => $payload->confirmed_email, 
                                        'roles' => $roles, 
                                        'permissions' => $permissions, 
                                        'impersonated_by' => $impersonated_by
                                    ], 
            'access_token');

            ///////////
            $res = [ 
                'uid' => $payload->uid,
                'access_token'=> $access,
                'token_type' => 'bearer', 
                'expires_in' => $this->config['access_token']['expiration_time'],
                'roles' => $roles
            ];

            if (isset($payload->impersonated_by) && $payload->impersonated_by != null){
                $res['impersonated_by'] = $impersonated_by;
            }

            Factory::response()->send($res);
            
        } catch (\Exception $e) {
            Factory::response()->sendError($e->getMessage(), 400);
        }	
    }

    function register()
    {
        if (!in_array($_SERVER['REQUEST_METHOD'], ['POST','OPTIONS']))
            Factory::response()->sendError('Incorrect verb ('.$_SERVER['REQUEST_METHOD'].'), expecting POST',405);
            
        try {
            $data  = Factory::request()->getBody();
            
            if ($data == null)
                Factory::response()->sendError('Invalid JSON',400);

            $u = new UsersModel();

            $missing = $u->getMissing($data);
            if (!empty($missing))
                Factory::response()->sendError('There are missing properties in your request: '.implode(',',$missing), 400);

            $email_in_schema = $u->inSchema(['email']);

            if ($email_in_schema){
                if (DB::table('users')->where(['email', $data['email']])->exists())
                    Factory::response()->sendError('Email already exists');  
            }            

            if (DB::table('users')->where(['username', $data['username']])->exists())
                Factory::response()->sendError('Username already exists');

            $uid = DB::table('users')->setValidator(new Validator())->create($data);
            if (empty($uid))
                Factory::response()->sendError("Error in user registration", 500, 'Error creating user');

            $u = DB::table('users');    
            if ($u->inSchema(['belongs_to'])){
                $affected = $u->where(['id', $uid])->update(['belongs_to' => $uid]);
            }


            if (!empty($this->config['registration_role'])){
                $role = $this->config['registration_role'];

                $r  = new RolesModel();
                $ur = DB::table('userRoles');

                $ur_id = $ur->create([ 'belongs_to' => $uid, 'role_id' => $r->get_role_id($role) ]);  

                if (empty($ur_id))
                    Factory::response()->sendError("Error in user registration", 500, 'Error registrating user role');  
            }else{
                $role = [];
            }        


            if ($email_in_schema){
                if (empty($data['email']))
                    throw new Exception("Email is empty");
                    
                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))
                    throw new Exception("Invalid email");

                // Email confirmation
                $exp = time() + $this->config['email']['expires_in'];	

                $base_url =  HTTP_PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . ($this->config['BASE_URL'] == '/' ? '/' : $this->config['BASE_URL']) ;
        
                $token = $this->gen_jwt_email_conf($data['email']);
                $url = $base_url . (!$this->config['REMOVE_API_SLUG'] ? 'api/v1' : 'v1') . '/auth/confirm_email/' . $token . '/' . $exp; 

                $firstname = $data['firstname'] ?? null;
                $lastname  = $data['lastname']  ?? null;
            }                

            $access  = $this->gen_jwt([
                                        'uid' => $uid, 
                                        'confirmed_email' => 0, 
                                        'roles' => $role,
                                        'permissions' => [] 
            ], 'access_token');

            $refresh = $this->gen_jwt([
                                        'uid' => $uid, 
                                        'confirmed_email' => 0, 
            ], 'refresh_token');

            $res = [ 
                'access_token'=> $access,
                'token_type' => 'bearer', 
                'expires_in' => $this->config['access_token']['expiration_time'],
                'refresh_token' => $refresh,
                'roles' => [$role],
                'uid' => $uid
            ];

            if ($email_in_schema)
                $res['email_confirmation_link'] = $url;


            Factory::response()->send($res);

        } catch (InvalidValidationException $e) { 
            Factory::response()->sendError('Validation Error', 400, json_decode($e->getMessage()));
        }catch(\Exception $e){
            Factory::response()->sendError($e->getMessage());
        }	
            
    }

    /* 
    Authorization checkin
    
    @return mixed object | null
    */
    function check() {
        //file_put_contents('CHECK.txt', 'HTTP VERB: ' .  $_SERVER['REQUEST_METHOD']."\n", FILE_APPEND);

        $headers = Factory::request()->headers();
        $auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (empty($auth))
            return;
            
        list($jwt) = sscanf($auth, 'Bearer %s');

        if($jwt != null)
        {
            try{
                $payload = \Firebase\JWT\JWT::decode($jwt, $this->config['access_token']['secret_key'], [ $this->config['access_token']['encryption'] ]);
                
                if (empty($payload))
                    Factory::response()->sendError('Unauthorized!',401);                     

                if (empty($payload->ip))
                    Factory::response()->sendError('Unauthorized',401,'Lacks IP in web token');

                if ($payload->ip != $_SERVER['REMOTE_ADDR'])
                    Factory::response()->sendError('Unauthorized!',401, 'IP change'); 

                if (empty($payload->uid))
                    Factory::response()->sendError('Unauthorized',401,'Lacks id in web token');  
                                                  

                if (empty($payload->roles)){
                    $payload->roles = ['registered'];
                }

                if ($payload->exp < time())
                    Factory::response()->sendError('Token expired',401);

                //print_r($payload);
                //exit; ///

                return ($payload);

            } catch (\Exception $e) {
                /*
                * the token was not able to be decoded.
                * this is likely because the signature was not able to be verified (tampered token)
                *
                * reach this point if token is empty or invalid
                */
                Factory::response()->sendError($e->getMessage(),401);
            }	
        }else{
            Factory::response()->sendError('Authorization jwt token not found',400);
        }

        return false;
    }

    
	function confirm_email($jwt, $exp)
	{
		if (!in_array($_SERVER['REQUEST_METHOD'], ['GET','OPTIONS']))
            Factory::response()->sendError('Incorrect verb ('.$_SERVER['REQUEST_METHOD'].'), expecting GET',405);
    
		// Es menos costoso verificar así en principio
		if ((int) $exp < time()) {
            $error = 'Link is outdated';
		} else {

			if($jwt != null)
			{
				try {
					$payload = \Firebase\JWT\JWT::decode($jwt, $this->config['email']['secret_key'], [ $this->config['email']['encryption'] ]);
					
					if (empty($payload))
						$error = 'Unauthorized!';                     

					if (!isset($payload->email) || empty($payload->email)){
						$error = 'email is needed';
					}

					if ($payload->exp < time())
						$error = 'Token expired';
					
					$u = DB::table('users');
					$ok = (bool) $u->where(['email', $payload->email])
					->fill(['confirmed_email'])
					->update(['confirmed_email' => 1]);
										
					//if (!$ok)		
					//	$error = 'Error en activación';				

				} catch (\Exception $e) {
					/*
					* the token was not able to be decoded.
					* this is likely because the signature was not able to be verified (tampered token)
					*
					* reach this point if token is empty or invalid
					*/
					Factory::response()->sendError($e->getMessage(),401);
				}	
			}else{
				Factory::response()->sendError('Authorization jwt token not found',400);
			}     
		}	

		if (!isset($error)){

			$rows = DB::table('users')->setFetchMode('ASSOC')->where(['email', $payload->email])->get(['id']);
			$uid  = $rows[0]['id'];

			$affected_rows = DB::table('users')
			->where(['id' => $uid])
			->fill(['confirmed_email'])
			->update(['confirmed_email' => 1]);
			
			if ($affected_rows === false)
				Factory::response()->sendError('Error', 500);

			$rows = DB::table('user_roles')->setFetchMode('ASSOC')->where(['belongs_to', $uid])->get(['role_id as role']);	

			$r = new RolesModel();

			$roles = [];
			if (count($rows) != 0){            
				foreach ($rows as $row){
					$roles[] = $r->getRoleName($row['role']);
				}
			}else
				$roles[] = 'registered';


			$access  = $this->gen_jwt(['uid' => $uid, 'roles' => $roles, 'confirmed_email' => 1], 'access_token');
			$refresh = $this->gen_jwt(['uid' => $uid, 'roles' => $roles, 'confirmed_email' => 1], 'refresh_token');

            
            Factory::response()->send([
				'access_token' => $access,
				'expires_in' => $this->config['email']['expires_in'],
				'refresh_token' => $refresh
			]);
	
		}else {
			Factory::response()->sendError(' Email confirmation has failed', 200, $error);
		}	

    }     
    
    function change_pass(){
		if($_SERVER['REQUEST_METHOD']!='POST')
			Factory::response()->sendError('Incorrect verb ('.$_SERVER['REQUEST_METHOD'].'), expecting POST',405);
		
		$headers = Factory::request()->headers();
		$auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;
		
		$data  = Factory::request()->getBody();
            
        if ($data == null)
			Factory::response()->sendError('Invalid JSON',400);
			
		if (!isset($data['password']) || empty($data['password']))
			Factory::response()->sendError('Empty email',400);
		
		if (empty($auth))
			Factory::response()->sendError('No auth', 400);			
			
		list($jwt) = sscanf($auth, 'Bearer %s');

		if($jwt != null)
        {
            try{
                // Checking for token invalidation or outdated token
                
                $payload = \Firebase\JWT\JWT::decode($jwt, $this->config['email']['secret_key'], [ $this->config['email']['encryption'] ]);
                
                if (empty($payload))
					Factory::response()->sendError('Unauthorized!',401);                     
					
                if (empty($payload->email)){
                    Factory::response()->sendError('Undefined email',400);
                }

                if ($payload->exp < time())
                    Factory::response()->sendError('Token expired',401);
			
				
				$rows = DB::table('users')->setFetchMode('ASSOC')->where(['email', $payload->email])->get(['id']);
				$uid = $rows[0]['id'];

				$affected = DB::table('users')->where(['id', $rows[0]['id']])->update(['password' => $data['password']]);

				// Fetch roles
				$uid = $rows[0]['id'];
				$rows = DB::table('user_roles')->setFetchMode('ASSOC')->where(['belongs_to', $uid])->get(['role_id as role']);	
				
				$r = new RolesModel();

				$roles = [];
				if (count($rows) != 0){            
					foreach ($rows as $row){
						$roles[] = $r->getRoleName($row['role']);
					}
				}else
					$roles[] = 'registered';

				
				$access  = $this->gen_jwt(['uid' => $uid, 'roles' => $roles, 'confirmed_email' => 1], 'access_token');
				$refresh = $this->gen_jwt(['uid' => $uid, 'roles' => $roles, 'confirmed_email' => 1], 'refresh_token');
 
				Factory::response()->send([
					'access_token' => $access,
					'expires_in' => $this->config['email']['expires_in'],
					'refresh_token' => $refresh
				]);
				
            } catch (\Exception $e) {
                /*
                * the token was not able to be decoded.
                * this is likely because the signature was not able to be verified (tampered token)
                *
                * reach this point if token is empty or invalid
                */
                Factory::response()->sendError($e->getMessage(),401);
            }	
        }else{
            Factory::response()->sendError('Authorization jwt token not found',400);
        }
       
    }
    

    /*
        Si el correo es válido debe generar y enviar por correo un enlance para cambiar el password
        sino no hacer nada.
    */
	function rememberme(){
		$data  = Factory::request()->getBody(false);

		if ($data == null)
			Factory::response()->sendError('Invalid JSON',400);

		$email = $data->email ?? null;

		if ($email == null)
			Factory::response()->sendError('Empty email', 400);

		try {	

			$u = (DB::table('users'))->setFetchMode('ASSOC');
			$rows = $u->where(['email', $email])->get(['id', 'confirmed_email']);

			if (count($rows) === 0){
                // Email not found
                Factory::response()->send('Please check your e-mail'); 
            }
		
            $uid = $rows[0]['id'];	//
            $exp = time() + $this->config['email']['expires_in'];	
            $confirmed_email = $rows[0]['confirmed_email'];

			$base_url =  HTTP_PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . ($this->config['BASE_URL'] == '/' ? '/' : $this->config['BASE_URL']) ;


            //////

            // Fetch roles
            $rows = DB::table('user_roles')->setFetchMode('ASSOC')->where(['belongs_to', $uid])->select(['role_id as role'])->get();	

            $roles = [];
            if (count($rows) != 0){            
                $r = new RolesModel();
            
                foreach ($rows as $row){
                    $roles[] = $r->getRoleName($row['role']);
                }
            }
            
            $_permissions = DB::table('permissions')->setFetchMode('ASSOC')->select(['tb', 'can_create as c', 'can_read as r', 'can_update as u', 'can_delete as d'])->where(['user_id' => $uid])->get();

            //print_r($rows);
            //exit; //

            $perms = [];
            foreach ((array) $_permissions as $p){
                $tb = $p['tb'];
                $perms[$tb] = $p['c'] * 8 + $p['r'] * 4 + $p['u'] * 2 + $p['d'];
            }

            //var_dump($roles); 
            //var_export($perms); 

			$token = $this->gen_jwt_rememberme($uid, $roles, $perms, $confirmed_email);
            $url = $base_url . (!$this->config['REMOVE_API_SLUG'] ? 'api/v1' : 'v1') .'/auth/change_pass_by_link/' . $token . '/' . $exp; 	

		} catch (\Exception $e){
			Factory::response()->sendError($e->getMessage(), 500);
		}

    
        // Enviar correo con el LINK: $url

        Factory::response()->send(['link_sent' => $url]);  # solo para debug !!!!!
        //Factory::response()->send('Please check your e-mail'); 
    }
    

    /*
        Login by link
        User controller is resposible for redirect to the view for changing password
    */
    function change_pass_by_link($jwt = NULL, $exp = NULL){
        if (!in_array($_SERVER['REQUEST_METHOD'], ['GET','OPTIONS'])){
            Factory::response()->sendError('Incorrect verb ('.$_SERVER['REQUEST_METHOD'].'), expecting GET',405);
        }    

        if ($jwt == null || $exp == null){
            Factory::response()->sendError('Bad request', 400, 'Two paramters are expected');
        }

        // Es menos costoso verificar así en principio
        if ((int) $exp < time()) {
            Factory::response()->sendError('Link is outdated', 401);
        } else {

            if($jwt != null)
            {
                try {
                    $payload = \Firebase\JWT\JWT::decode($jwt, $this->config['email']['secret_key'], [ $this->config['email']['encryption'] ]);
                    
                    if (empty($payload))
                        Factory::response()->sendError('Unauthorized!',401);                     

                    if (empty($payload->uid)){
                        Factory::response()->sendError('uid is needed',400);
                    }

                    $roles = !empty($payload->roles) ? $payload->roles : [];
                    
                    $perms = $payload->permissions ?? [];
                    $confirmed_email = $payload->confirmed_email;

                    if ($payload->exp < time())
                        Factory::response()->sendError('Token expired, please log in',401);

                    $access  = $this->gen_jwt([ 'uid' => $payload->uid, 'roles' => $roles, 'permissions' => $perms, 'confirmed_email' => $confirmed_email ], 'access_token');
                    $refresh  = $this->gen_jwt([ 'uid' => $payload->uid, 'roles' => $roles, 'permissions' => $perms,'confirmed_email' => $confirmed_email ], 'refresh_token');

                    ///////////
                    Factory::response()->send([ 
                                                'access_token'=> $access,
                                                'refresh_token'=> $refresh,
                                                'token_type' => 'bearer', 
                                                'expires_in' => $this->config['access_token']['expiration_time']                                            
                    ]);
                    

                } catch (\Exception $e) {
                    /*
                    * the token was not able to be decoded.
                    * this is likely because the signature was not able to be verified (tampered token)
                    *
                    * reach this point if token is empty or invalid
                    */
                    Factory::response()->sendError($e->getMessage(),401);
                }	
            }else{
                Factory::response()->sendError('Authorization jwt token not found',400);
            }     
        }	

    }        

}
