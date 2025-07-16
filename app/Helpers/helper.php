<?php

use Illuminate\Support\Facades\Auth as Auth;
use Carbon\Carbon;

//use Vizir\KeycloakWebGuard\Auth as Auth;
function getToken()
{
    return (Auth::token())?json_decode(Auth::token(), true):null;
}

function getUser()
{
    $user = new \stdClass();    
    $jsonToken = getToken();
    
    $user->id = $jsonToken['sub'];
    $user->user = $jsonToken['preferred_username'];
    $user->firstName = $jsonToken['given_name'];
    $user->lastName = $jsonToken['family_name'];
    $user->email = $jsonToken['email'];
    $user->groups = getGroupsUser();

    return json_encode($user);
}

function getTenant()
{
    $jsonToken = json_decode(Auth::token());
    
    //$arrayTenant = $jsonToken->foo_tenants;

    //para que siga funcionando como antes
    $arrayTenant = isset($jsonToken->foo_tenants) && $jsonToken->foo_tenants[0] == "acara" ? $jsonToken->foo_tenants : false;
    
    // para que tome el nuevo tenant
    if (!$arrayTenant)
        $arrayTenant[0] = isset($jsonToken->tenantId) ? $jsonToken->tenantId : false;

    return $arrayTenant[0];

    //return 'acara';
}

function getDb()
{
    $arrayTenant = getTenant();

    switch ($arrayTenant) {
        case "acara": //id
        case "837da060-cce1-405a-843d-3a2618221eb6": //id del scope dev
        case "a3e24a39-d050-4262-a589-da13154fb45d": //id del scope test
            return "acara";
            break;
    }

    return false;
}

function getUuid()
{
    $jsonToken = getToken();
    return ($jsonToken) ? $jsonToken['sub'] : '';
}

function checkRole($rol)
{
    $roles = getRolesUser();
    //$jsonToken = json_decode(Auth::token(), true);
    //$client = \Config::get("keycloak-web.client_id");
    //cliente el cual genero el token
    //$client =$jsonToken["azp"];
    //$roles = $jsonToken["resource_access"][$client]["roles"];            
    //verifica si existen todos los elementos del array rol esta en el array d roles    
    //return in_array($rol, $roles, true);
    //verifica si al menos 1 elemento del array rol esta en el array d roles    
    return array_intersect($rol, $roles); 
}
/*
function decodeRoles()
{    
    $jsonToken = json_decode(Auth::token(), true);    
    //cliente el cual genero el token
    $client = $jsonToken["azp"];
    $roles = $jsonToken["resource_access"][$client]["roles"];

    return $roles;
}
*/
function getRolesUser()
{
    $jsonToken = getToken();    
    //cliente el cual genero el token
    $client = getClient();
    $roles = $jsonToken["resource_access"][$client]["roles"];    

    return $roles;
}

function getClient()
{
    $jsonToken = getToken();      
    //cliente el cual genero el token
    return ($jsonToken)?$jsonToken["azp"]:null;
}

function getGroupsUser()
{
    $jsonToken = getToken();
    $userGroups = array();

    if($jsonToken){
        $client = getClient();
        $groups = $jsonToken["groups"];    
        for($i=0; $i<count($groups); $i++){            
            $clientGroup = str_replace(' - ', '', substr($groups[$i], strrpos($groups[$i], ' - ')));            
            if($clientGroup == $client){
                $userGrup = str_replace('/', '', substr($groups[$i], 0, strrpos($groups[$i], ' - ')));
                array_push($userGroups, $userGrup);
            }
        }        
    }                      

    return $userGroups;
}

function getMsg($type)
{
    $message["required"] = "El campo :attribute es requerido";
    $message["unique"] = "El campo :attribute ya existe";
    $message['accepted'] = 'El campo :attribute debe ser aceptado.';
    $message['active_url'] = 'El campo :attribute no es una URL válida.';
    $message['after'] = 'El campo :attribute debe ser una fecha posterior a :date.';
    $message['after_or_equal'] = 'El campo :attribute debe ser una fecha posterior o igual a :date.';
    $message['alpha'] = 'El campo :attribute solo puede contener letras.';
    $message['alpha_dash'] = 'El campo :attribute solo puede contener letras; números y guiones.';
    $message['alpha_num'] = 'El campo :attribute solo puede contener letras y números.';
    $message['array'] = 'El campo :attribute debe ser un arreglo.';
    $message['before'] = 'El campo :attribute debe ser una fecha anterior a :date.';
    $message['before_or_equal'] = 'El campo :attribute debe ser una fecha anterior o igual a :date.';
    $message['integer'] = 'El campo :attribute debe ser un entero.';
    $message['between'] = [
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
        'file' => 'El campo :attribute debe tener entre :min y :max kilobytes.',
        'string' => 'El campo :attribute debe tener entre :min y :max caracteres.',
        'array' => 'El campo :attribute debe tener entre :min y :max elementos.'
    ];
    $message['numeric'] = 'El campo :attribute debe ser un valor numérico.';
    $message['max'] = [
        'numeric' => 'El campo :attribute no debe exceder :max.',
        'file' => 'El campo :attribute no debe exceder :max kilobytes.',
        'string' => 'El campo :attribute no debe exceder :max caracteres.',
        'array' => 'El campo :attribute no debe exceder :max elementos.'
    ];
    $message['boolean'] = 'El campo :attribute debe ser verdadero o falso.';
    $message['confirmed'] = 'La confirmación del campo :attribute no coincide.';
    $message['date'] = 'El campo :attribute no es una fecha válida.';
    $message['date_equals'] = 'El campo :attribute debe ser una fecha igual a :date.';
    $message['date_format'] = 'El campo :attribute no coincide con el formato :format.';
    $message['different'] = 'El campo :attribute y :other deben ser diferentes.';
    $message['digits'] = 'El campo :attribute debe tener :digits dígitos.';
    $message['digits_between'] = 'El campo :attribute debe tener entre :min y :max dígitos.';
    $message['dimensions'] = 'El campo :attribute tiene dimensiones de imagen no válidas.';
    $message['distinct'] = 'El campo :attribute tiene un valor duplicado.';
    $message['email'] = 'El campo :attribute debe ser una dirección de correo electrónico válida.';
    $message['ends_with'] = 'El campo :attribute debe terminar con uno de los siguientes valores: :values.';
    $message['exists'] = 'El campo :attribute seleccionado no es válido.';
    $message['file'] = 'El campo :attribute debe ser un archivo.';
    $message['filled'] = 'El campo :attribute debe tener un valor.';
    $message['gt'] = [
        'numeric' => 'El campo :attribute debe ser mayor que :value.',
        'file' => 'El campo :attribute debe tener más de :value kilobytes.',
        'string' => 'El campo :attribute debe tener más de :value caracteres.',
        'array' => 'El campo :attribute debe tener más de :value elementos.'
    ];
    $message['gte'] = [
        'numeric' => 'El campo :attribute debe ser mayor o igual a :value.',
        'file' => 'El campo :attribute debe tener :value kilobytes o más.',
        'string' => 'El campo :attribute debe tener :value caracteres o más.',
        'array' => 'El campo :attribute debe tener :value elementos o más.'
    ];
    $message['image'] = 'El campo :attribute debe ser una imagen.';
    $message['in'] = 'El campo :attribute seleccionado';

    return isset($message[$type]) ? $message[$type] : '';

    /*
    return [
        'nombre.required' => 'El nombre es requerido'
    ];*/
}

function can($permission)
{
    if(!checkRole($permission))
    {
        return response()->json([
            'status' =>  'Unauthorized',
            'message' => "No autorizado",
            'data' => "",
            'code' => 401
        ], 401);
    }
    else
        return null;
}

function isUserAdmin()
{
    $rolesUser = getGroupsUser();
    $rolesApp = array('admin_gral');    

    foreach ($rolesUser as $role) {
        if (in_array($role, $rolesApp)) {
            return true;
        }        
    }

    return false;
}

function formatDate($date)
{

    if($date){
        $formattedDate = Carbon::createFromFormat('d/m/Y H:i', $date);
        return $formattedDate->format('Y-m-d H:i:s');
    }

    return null;
    
}

function formatDateInverse($date)
{

    if($date){
        dd($date);
        $formattedDate = Carbon::createFromFormat('Y-m-d H:i:s', $date);
        return $formattedDate->format('d/m/Y');
    }

    return null;
    
}