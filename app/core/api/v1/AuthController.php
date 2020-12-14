<?php

namespace simplerest\core\api\v1;

use Exception;
use simplerest\core\Controller;
use simplerest\core\interfaces\IAuth;
use simplerest\libs\Factory;
use simplerest\libs\DB;
use simplerest\libs\Utils;
use simplerest\libs\Debug;
use simplerest\libs\Validator;
use simplerest\core\exceptions\InvalidValidationException;


class AuthController extends Controller implements IAuth
{
    protected $users_table = 'users';
    public $uid;

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

    protected function gen_jwt_email_conf(string $email, array $roles, array $perms){
        $time = time();

        $payload = [
            'alg' => $this->config['email']['encryption'],
            'typ' => 'JWT',
            'iat' => $time, 
            'exp' => $time + $this->config['email']['expires_in'],
            'ip'  => $_SERVER['REMOTE_ADDR'],
            'email' => $email,
            'roles' => $roles,
            'permissions' => $perms
         ];

        return \Firebase\JWT\JWT::encode($payload, $this->config['email']['secret_key'],  $this->config['email']['encryption']);
    }

    protected function gen_jwt_rememberme($uid){
        $time = time();

        $payload = [
            'alg' => $this->config['email']['encryption'],
            'typ' => 'JWT',
            'iat' => $time, 
            'exp' => $time + $this->config['email']['expires_in'],
            'ip'  => $_SERVER['REMOTE_ADDR'],
            'uid' => $uid
         ];

        return \Firebase\JWT\JWT::encode($payload, $this->config['email']['secret_key'],  $this->config['email']['encryption']);
    }

    function login()
    {
        if (!in_array($_SERVER['REQUEST_METHOD'], ['POST','OPTIONS']))
            Factory::response()->sendError('Incorrect verb ('.$_SERVER['REQUEST_METHOD'].'), expecting POST',405);

        $data  = Factory::request()->getBody();

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

            $u = DB::table($this->users_table);

            $row = $u->assoc()->unhide(['password'])
            ->where([ 'email'=> $email, 'username' => $username ], 'OR')
            ->setValidator((new Validator())->setRequired(false))  
            ->first();

            if (!$row)
                throw new Exception("Incorrect username / email or password");

            $hash = $row['password'];

            if (!password_verify($password, $hash))
                Factory::response()->sendError('Incorrect username / email or password', 401);

            $active = 1; 
            if ($u->inSchema(['active'])){
                $active = $row['active']; 

                if ($active == null) {

                    if ($row['confirmed_email'] === "0") {
                        Factory::response()->sendError('Non authorized', 403, 'Please confirm your e-mail');
                    } else {
                        Factory::response()->sendError('Non authorized', 403, 'Account pending for activation');
                    }
                }

                if ($active == 0 || (string) $active === "0") {
                    Factory::response()->sendError('Non authorized', 403, 'Deactivated account !');
                } 
            }        

            // Fetch roles && permissions
            $uid = $row['id'];

            $acl   = Factory::acl();
            $roles = $acl->fetchRoles($uid);
            $perms = $acl->fetchPermissions($uid);

            //var_export($perms);

            $access  = $this->gen_jwt([ 'uid' => $uid, 
                                        'roles' => $roles, 
                                        'permissions' => $perms,
                                        'active' => $active,
            ], 'access_token');

            // el refresh no debe llevar ni roles ni permisos por seguridad !
            $refresh = $this->gen_jwt([ 'uid' => $uid
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

        $data  = Factory::request()->getBody();

        if ($data == null)
            return;
            
        if (!isset($data->uid) && !isset($data->role))
            Factory::response()->sendError('Bad request', 400, 'Nothing to impersonate');

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

            $acl   = Factory::acl();
            $roles = $acl->fetchRoles($payload->uid);

            if (!$acl->hasSpecialPermission("impersonate", $roles) && !(isset($payload->impersonated_by) && !empty($payload->impersonated_by)) ){
                Factory::response()->sendError('Unauthorized!',401, 'Impersonate requires elevated privileges');
            }    

            
            $guest_role = $acl->getGuest();

            $impersonate_user = $data->uid ?? null;
            $impersonate_role = $data->role ?? null;
            
            if (!empty($impersonate_role)){
                if ($impersonate_role == $guest_role){
                    $uid = -1;
                    $roles = [$guest_role];
                    $perms = [];
                    $active = null;
                } else {

                    if (!$acl->roleExists($impersonate_role)){
                        Factory::response()->sendError("Bad request", 400, "Role $impersonate_role is not valid");
                    }

                    $uid = $payload->uid; // sigo siendo yo (el admin)
                    $roles = [$impersonate_role]; 
                    $perms = []; // permisos inalterados (rol puro)
                    $active = 1; // asumo está activo
                }    
            }


            if (!empty($impersonate_user)){ 
                $uid = $impersonate_user;

                $row = DB::table($this->users_table)->assoc()
                ->where([ 'id' =>  $uid ] ) 
                ->first();

                if (!$row)
                    throw new Exception("User to impersonate does not exist");

                $active = $row['active'];

                if ($active === NULL) {
                    Factory::response()->sendError('Account to be impersonated is pending for activation', 500);
                } elseif (((string) $active === "0")) {
                    Factory::response()->sendError('User account to be impersonated is deactivated', 500);
                }  
                
                $roles = $acl->fetchRoles($uid);
                $perms = $acl->fetchPermissions($uid);
            }    

            $impersonated_by = $payload->impersonated_by ?? $payload->uid;

            $access  = $this->gen_jwt([ 'uid' => $uid, 
                                        'roles' => $roles, 
                                        'permissions' => $perms,
                                        'impersonated_by' => $impersonated_by,
                                        'active' => $active,
            ], 'access_token');

            $refresh  = $this->gen_jwt(['uid' => $uid, 
                                        'impersonated_by' => $impersonated_by
            ], 'refresh_token');

            $res = [ 
                'access_token'=> $access,
                'refresh_token' => $refresh,
                'token_type' => 'bearer', 
                'expires_in' => $this->config['access_token']['expiration_time'],
                'roles' => $roles,
                'uid' => $uid,
                'impersonated_by' => $impersonated_by
            ];

    
            Factory::response()->send($res);      

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
        
        $acl   = Factory::acl();
        $roles = $acl->fetchRoles($uid);
        $perms = $acl->fetchPermissions($uid);

        //////
        
        try {              
            
            $access  = $this->gen_jwt([ 'uid' => $uid, 
                                        'roles' => $roles, 
                                        'permissions' => $perms,
                                        'active' => 1
            ], 'access_token');

            $refresh = $this->gen_jwt([ 'uid' => $uid,
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

            if (!isset($payload->uid) || empty($payload->uid)){
                Factory::response()->sendError('uid is needed',400);
            }

            if ($payload->exp < time())
                Factory::response()->sendError('Token expired, please log in',401);


            $uid = $payload->uid;
            $impersonated_by = $payload->impersonated_by ?? null;
            $impersonated_by_role = null;

            if ($impersonated_by) {
                // guest
                if ($payload->uid == -1) 
                {
                    $acl   = Factory::acl();

                    $active = false;
                    $roles = [$acl->getGuest()];
                    $perms = [];
                } else {
                    $impersonated_by_role = true;
                }
            }     

            if (!$impersonated_by || $impersonated_by_role) {
                $u = DB::table($this->users_table);

                $row = $u->assoc()
                ->where(['id' => $payload->uid])->first();

                if (!$row)
                    throw new Exception("User not found");

                $active = $row['active']; 

                if ($active == 0 || (string) $active === "0") {
                    Factory::response()
                    ->sendError('Non authorized', 403, 'Deactivated account !');
                }

                $acl   = $acl ?? Factory::acl();
                $roles = $acl->fetchRoles($uid);
                $perms = $acl->fetchPermissions($uid);
            }            

          
            $access  = $this->gen_jwt([ 'uid' => $payload->uid,
                                        'roles' => $roles, 
                                        'permissions' => $perms, 
                                        'impersonated_by' => $impersonated_by,
                                        'active' => $active
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

    /*
        Minimizar la cantidad de instancias de UsersModel !!!!!!!!!
    */
    function register()
    {
        global $api_version;

        if (!in_array($_SERVER['REQUEST_METHOD'], ['POST','OPTIONS']))
            Factory::response()->sendError('Incorrect verb ('.$_SERVER['REQUEST_METHOD'].'), expecting POST',405);
            
        DB::beginTransaction();

        try {
            $data  = Factory::request()->getBody(false);

            if ($data == null)
                Factory::response()->sendError('Bad request',400, 'Invalid JSON');

            $acl = Factory::acl();
            $u   = DB::table($this->users_table);

            $roles = [];    

            if ($u->inSchema(['rol'])){
                if (!empty($data['rol'])) {
                    if (isset($this->config['auto_approval_roles']) && !empty($this->config['auto_approval_roles'])) {
                    
                        if (!in_array($acl->getRoleName($data['rol']), $this->config['auto_approval_roles'])) {
                            throw new Exception("Role {$data['rol']} is not auto-approved");
                        }

                    }    
                }   

                $roles = [ $data['rol'] ];   
            } else {
                if (!empty($data['roles'])) {
                    if (isset($this->config['auto_approval_roles']) && !empty($this->config['auto_approval_roles'])) {

                        foreach ($data['roles'] as $r){
                            if (!in_array($r, $this->config['auto_approval_roles'])) {
                                throw new Exception("Role $r is not auto-approved");
                            }

                            $roles[] = $r;
                        }                    
                    }    
                
                    unset($data['roles']);
                }        
            }

            $missing = $u->getMissing($data);
            if (!empty($missing))
                Factory::response()->sendError('Bad request', 400, 'There are missing attributes in your request: '.implode(',',$missing));

            $email_in_schema = $u->inSchema(['email']);

            if ($email_in_schema){
                // se hace en el modelo pero es más rápido hacer chequeos acá

                if (empty($data['email']))
                    throw new Exception("Email is empty");
                    
                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))
                    throw new Exception("Invalid email");  

                if (DB::table($this->users_table)->where(['email', $data['email']])->exists())
                    Factory::response()->sendError('Email already exists');    
            }            

            if (DB::table($this->users_table)->where(['username', $data['username']])->exists())
                Factory::response()->sendError('Username already exists');

            $uid = DB::table($this->users_table)->setValidator(new Validator())->create($data);
            if (empty($uid))
                throw new Exception('Error creating user');
            

            $u = DB::table($this->users_table);    
            if ($u->inSchema(['belongs_to'])){
                $affected = $u->where(['id', $uid])->update(['belongs_to' => $uid]);
            }

            if (!empty($roles))
            {
                $acl->addUserRoles($roles, $uid);
            }       

            $email_confirmation = $email_in_schema && $u->inSchema(['confirmed_email']);

            if ($email_confirmation){ 
                // Email confirmation
                $exp = time() + $this->config['email']['expires_in'];	

                $base_url =  HTTP_PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . ($this->config['BASE_URL'] == '/' ? '/' : $this->config['BASE_URL']) ;

                $token = $this->gen_jwt_email_conf($data['email'], $roles, $perms);

                $url = $base_url . (!$this->config['REMOVE_API_SLUG'] ? "api/$api_version" : $api_version) . '/auth/confirm_email/' . $token . '/' . $exp; 
            }                
            

            $access  = $this->gen_jwt([
                                        'uid' => $uid, 
                                        'roles' => $roles,
                                        'permissions' => [],
                                        'active' => $this->config['pre_activated'] ? true : null
            ], 'access_token');

            $refresh = $this->gen_jwt([
                                        'uid' => $uid
            ], 'refresh_token');

            $res = [ 
                'uid' => $uid,
                'access_token'=> $access,
                'token_type' => 'bearer', 
                'expires_in' => $this->config['access_token']['expiration_time'],
                'refresh_token' => $refresh,
                'roles' => $roles
            ];

            if ($email_confirmation)
                $res['email_confirmation_link'] = $url;


            DB::commit();    
            Factory::response()->send($res);

        } catch (InvalidValidationException $e) { 
            DB::rollback();
            Factory::response()->sendError('Validation Error', 400, json_decode($e->getMessage()));
        }catch(\Exception $e){
            DB::rollback();
            Factory::response()->sendError($e->getMessage());
        }	
            
    }

    private function jwtPayload(){
        $auth = Factory::request()->getAuth();

        if (empty($auth))
            return;
            
        list($jwt) = sscanf($auth, 'Bearer %s');

        if($jwt != null)
        {
            try{
                $payload = \Firebase\JWT\JWT::decode($jwt, $this->config['access_token']['secret_key'], [ $this->config['access_token']['encryption'] ]);
                
                if (empty($payload))
                    Factory::response()->sendError('Unauthorized!',401);             

                if (!isset($payload->ip) || empty($payload->ip))
                    Factory::response()->sendError('Unauthorized',401,'Lacks IP in web token');

                if ($payload->ip != $_SERVER['REMOTE_ADDR'])
                    Factory::response()->sendError('Unauthorized!',401, 'IP change'); 

                if (!isset($payload->uid) || empty($payload->uid))
                    Factory::response()->sendError('Unauthorized',401,'Lacks id in web token');  

                // Lacks active status
                if (!isset($payload->active) && $payload->uid != -1){
                    Factory::response()->sendError('Unauthorized', 401, 'Lacks active status. Please log in.');
                }    

                if ($payload->active === false) {
                    Factory::response()->sendError('Non authorized', 403, 'Deactivated account');
                } 
                                                  
                if ($payload->exp < time())
                    Factory::response()->sendError('Token expired',401);

                //print_r($payload->roles);
                //exit; 

                return json_decode(json_encode($payload),true);

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
    Authorization checkin
    
        @return mixed array | null
    */
    function check() {
        static $ret;

        if ($ret != null)
            return $ret;

        switch (Factory::request()->authMethod()){
            case 'API_KEY': 
                $api_key = Factory::request()->getApiKey();

                $acl = Factory::acl();
                $uid = $acl->getUserIdFromApiKey($api_key);

                if ($uid == NULL){
                    Factory::response()->sendError('Invalid API Key', 401);
                }

                $u = DB::table($this->users_table);

                if ($u->inSchema(['rol'])){
                    $rid   = $u->where(['id' =>$uid])->value('rol');
                    $roles = [ $acl->getRoleName($rid) ]; 
                } else {
                    $roles = $acl->fetchRoles($uid);
                }
                
                $ret = [
                    'uid'           => $uid,
                    'roles'         => $roles,
                    'permissions'   => $acl->fetchPermissions($uid),
                    'active'        => true //
                ];
            break;
            case 'JWT':
                $ret = $this->jwtPayload();
            break;
            default:
                $ret = [
                    'uid' => null,
                    'roles' => [Factory::acl()->getGuest()],
                    'permissions' => [],
                    'active' => null
                ];
        }

        if (!empty($ret['roles']) && is_integer($ret['roles'])){
            $ret['roles'] = [ Factory::acl()->getRoleName($ret['roles']) ];
        }

        $this->uid = $ret['uid'];

        return $ret;
    }
    
	function confirm_email($jwt, $exp)
	{
		if (!in_array($_SERVER['REQUEST_METHOD'], ['GET','OPTIONS']))
            Factory::response()->sendError('Incorrect verb ('.$_SERVER['REQUEST_METHOD'].'), expecting GET',405);

		// Es menos costoso verificar así en principio
		if ((int) $exp < time()) {
            Factory::response()->sendError('Link is outdated', 400);
        }         

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
                
                //if (isset($payload->active) && $payload->active == 0) {
                //    Factory::response()->sendError('Non authorized', 403, 'Deactivated account');
                //}


                $u = DB::table($this->users_table);

                $rows = DB::table($this->users_table)->assoc()->where(['email', $payload->email])->get(['id', 'active']);

                if (count($rows) == 0){
                    Factory::response()->sendError("Not found", 404, "Email not found");
                }

                if ((string) $rows[0]['active'] === "0") {
                    Factory::response()->sendError('Non authorized', 403, 'Deactivated account !');
                }

                $uid  = $rows[0]['id'];
                
                $ok = (bool) DB::table($this->users_table)->where(['id', $uid])
                ->fill(['confirmed_email'])
                ->update(['confirmed_email' => 1]);

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

        $roles = $payload->roles ?? [];
        $perms = $payload->permissions ?? [];

        $access  = $this->gen_jwt([ 'uid' => $uid,   
                                    'roles' => $roles, 
                                    'permissions' => $perms,
                                    'active' => 1                     // *
        ], 'access_token');

        $refresh = $this->gen_jwt(['uid' => $uid, 
        ], 'refresh_token');

        
        Factory::response()->send([
            'uid' => $uid,  
            'access_token' => $access,
            'token_type' => 'bearer', 
            'expires_in' => $this->config['access_token']['expiration_time'],
            'refresh_token' => $refresh,
            'roles' => $roles   
        ]);	

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
			
				
				$rows = DB::table($this->users_table)->assoc()->where(['email', $payload->email])->get(['id', 'active']);
                $uid = $rows[0]['id'];
                
                $active = $rows[0]['active'];

                if ((string) $active === "0") {
                    Factory::response()->sendError('Non authorized', 403, 'Deactivated account !');
                }

                $affected = DB::table($this->users_table)
                ->where(['id', $rows[0]['id']])
                ->update(['password' => $data['password']]);

				// Fetch roles
				$uid = $rows[0]['id'];
            
                $acl   = Factory::acl();
                $roles = $acl->fetchRoles($uid);
                $perms = $acl->fetchPermissions($uid);

                $access  = $this->gen_jwt([ 'uid' => $uid, 
                                            'roles' => $roles, 
                                            'permissions' => $perms,
                                            'active' => $active,
                ], 'access_token');

                // el refresh no debe llevar ni roles ni permisos por seguridad !
                $refresh = $this->gen_jwt([ 'uid' => $uid
                ], 'refresh_token');
 
				Factory::response()->send([
                    'access_token' => $access,
                    'token_type' => 'bearer', 
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
        global $api_version;

		$data  = Factory::request()->getBody();

		if ($data == null)
			Factory::response()->sendError('Invalid JSON',400);

		$email = $data->email ?? null;

		if ($email == null)
			Factory::response()->sendError('Empty email', 400);

		try {	

			$u = (DB::table($this->users_table))->assoc();
			$rows = $u->where(['email', $email])->get(['id', 'active']);

			if (count($rows) === 0){
                // Email not found
                Factory::response()->sendError('Please check your e-mail', 400); 
            }
		
            $uid = $rows[0]['id'];	//
            $exp = time() + $this->config['email']['expires_in'];	

            $active = $rows[0]['active'];

            if ((string) $active === "0") {
                Factory::response()->sendError('Non authorized', 403, 'Deactivated account !');
            }

            $base_url =  HTTP_PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . ($this->config['BASE_URL'] == '/' ? '/' : $this->config['BASE_URL']) ;
            

            $token = $this->gen_jwt_rememberme($uid);
            
            $url = $base_url . (!$this->config['REMOVE_API_SLUG'] ? "api/$api_version" : $api_version) .'/auth/change_pass_by_link/' . $token . '/' . $exp; 	

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

                    $uid = $payload->uid;

                    $acl   = Factory::acl();
                    $roles = $acl->fetchRoles($uid);
                    $perms = $acl->fetchPermissions($uid);

                    $row = DB::table($this->users_table)->assoc()
                    ->where(['id'=> $uid]) 
                    ->first();

                    if (!$row)
                        throw new Exception("Uid not found");

                    $active = $row['active']; 
                    

                    if ($active === false) {
                        Factory::response()->sendError('Non authorized', 403, 'Deactivated account');
                    }

                    if ($payload->exp < time())
                        Factory::response()->sendError('Token expired, please log in',401);

                    $access  = $this->gen_jwt([ 'uid' => $uid,
                                                'roles' => $roles, 
                                                'permissions' => $perms, 
                                                'active' => $active
                    ], 'access_token');
                    
                    $refresh  = $this->gen_jwt([ 
                                                'uid' => $uid
                    ], 'refresh_token');

                    ///////////
                    Factory::response()->send([ 
                                    'uid' => $uid,
                                    'access_token'=> $access,
                                    'refresh_token'=> $refresh,
                                    'token_type' => 'bearer', 
                                    'expires_in' => $this->config['access_token']['expiration_time'],
                                    'roles' => $roles,
                                    'permissions' => $perms                                            
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
