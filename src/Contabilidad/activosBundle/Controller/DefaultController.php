<?php

namespace Contabilidad\activosBundle\Controller;

use GuzzleHttp\Pool;
use Guzzle\Http\Client;
use GuzzleHttp\Event\BeforeEvent;



use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
      private $ldap_dn="DC=upr,DC=edu,DC=cu";
      private $ldap_usr_dom="@upr.edu.cu";
      private $ldap_host="ldap://ad.upr.edu.cu";

     function isLdapUser($username,$password,$ldap){
            global $ldap_dn,$ldap_usr_dom;
            
            //$ldap = ldap_connect($this->ldap_host,389);    

            ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION,3);
            ldap_set_option($ldap, LDAP_OPT_REFERRALS,0);
     
            $ldapBind= ldap_bind($ldap, $username. $this->ldap_usr_dom, $password);            
            //ldap_unbind($ldap);   
                

            return   $ldapBind; 
     }
     
    function Auth($username, $password, $adGroupName = false){
        global $ldap_host,$ldap_dn;
            
        $ldap = ldap_connect($this->ldap_host);
        if (!$ldap)
            throw new Exception("Cant connect ldap server", 1);
        
          return isLdapUser($username, $password, $ldap);     
        
    }

    function Info($username, $password, $adGroupName = false,$attrib){
        global $ldap_host,$ldap_dn;
            
        $ldap = ldap_connect($this->ldap_host);
        if (!$ldap)
            throw new Exception("Cant connect ldap server", 1);
        
       if($this->isLdapUser($username, $password, $ldap)){     
               
            
            $results = ldap_search($ldap,$this->ldap_dn,'(sAMAccountName=' . $username . ')',$attrib);
      
            $user_data = ldap_get_entries($ldap, $results);
            
              
            return $user_data;
        }
        
    }

    function Exist($username){
        global $ldap_host,$ldap_dn,$ldap_usr_dom;
        
        $exist = true;  
        $ldap = ldap_connect($this->ldap_host);
        if (!$ldap)
            throw new Exception("Cant connect ldap server", 1);
            
        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION,3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS,0);
     
        $usernameBiblo = 'biblioteca';
        $passwordBiblo = '$biblioteca1$';
        $ldapBind= ldap_bind($ldap, $usernameBiblo. $this->ldap_usr_dom, $passwordBiblo);
        
        $attrib = array('distinguishedname');           
          //isLdapUser($username, $password, $ldap);    
               
        $results = ldap_search($ldap,$this->ldap_dn,'(sAMAccountName=' . $username . ')',$attrib);  
        $user_data = ldap_get_entries($ldap, $results);
            
        if($user_data[0]['distinguishedname'][0] == "")$exist = false;  
        if(strstr($user_data[0]['distinguishedname'][0], '_Bajas')) $exist = false;
        
        return $exist;

    }

    public function load_ResponsableAction()
    {
        $peticion = $this->getRequest();//objeto POST or GET de una peticion URL
        $user=$peticion->get('user');//obtener el Objeto "user" pasado por la peticion          
        $password=$peticion->get('password');//obtener el Objeto "password" pasado por la peticion          

        
        $ldapClass= $this->Info($user,$password,false,array("employeeID","employeeNumber"));
        //$ldapClass= $this->isLdapUser($user,$password,$this->ldap_host);
        
        //var_dump($ldapClass);
        //var_dump($ldapClass[0]["employeeid"][0]);
        if (isset($ldapClass[0]["employeenumber"][0])) {
            var_dump($ldapClass[0]["employeenumber"][0]);
        }
        



        $client = new Client("http://apiassets.upr.edu.cu");//abrir un nuevo cliente guzzle
        $json=Array();//crear una variable json de tipo array
       

      
      //renderizar al html los objetos json capturados
        return $this->render('activosBundle:Default:activos.html.twig',array(
            "json"=>$json, 
            //"ldap"=>$ldapClass,         
            
            ));

    }
    
    public function responsableAction()
    {
        return $this->render('activosBundle:Default:responsable.html.twig');
    }
    public function ObtenerNombreEmpleado($client)
    {
        $request = $client;
        $response = $request->send();            
        $data = $response->json(); 
        $area_respo=$data['hydra:member'];  
        if (isset($area_respo[0])) {

            return $area_respo[0]['nombre'].' '.$area_respo[0]['apellido1'].' '.$area_respo[0]['apellido2'];
        }
        else
        {
            return "---";
        }
        //return $nombre;  
    }
    public function ObtenerAreaResponsabilidad($client){
        $request = $client;
        $response = $request->send();            
        $data = $response->json(); 
        $area_respo=$data['hydra:member']; 
        if (isset($area_respo[0])) {                  
            return $area_respo[0]['descArearesponsabilidad'];
            
        }
        else
        {
            return "---";
        }
        
    }
    public function Locate_inventarioAction(){

        $peticion = $this->getRequest();//objeto POST or GET de una peticion URL
        $NoInventario=$peticion->get('NoInventario');//obtener el Objeto "id" pasado por la peticion          

        //$tipocnmb=$this->GetID($tipocnmb);//separar el objeto id para obtener los numeros reales del id

        //$id=$peticion->get('id');//obtener el Objeto "id" pasado por la peticion        
        //$id=$this->GetID($id);//separar el objeto id para obtener los numeros reales del id
            
        
            
        $client = new Client("http://apiassets.upr.edu.cu");//abrir un nuevo cliente guzzle
        $json=Array();//crear una variable json de tipo array

            $request = $client->get("activo_fijos?idRotulo=".(string)$NoInventario);//hacerle una peticion GET a la paguina consecutiva
            $response = $request->send();//enviar la peticion
            $data = $response->json(); //recoger el json de la peticion            
            
            $count=$data['hydra:lastPage'];  


            $count=$this->GetCantPageFilter($count);  
            
                                        

            if ($count!=0) {
                for ($i=1; $i <$count ; $i++) { 

                    $request = $client->get("activo_fijos?idRotulo=".(string)$NoInventario."&page=".(string)$i);
                    $response = $request->send();            
                    $data = $response->json(); 
                    $src=$data['hydra:member'];   

                    

                    for ($j=0; $j < count($src) ; $j++) //recorrer los elementos del array que esta en "hydra:member"
                        {     
                            $src[$j]['idArearesp']=$this->ObtenerAreaResponsabilidad($client->get("areas_responsabilidads?idArearesponsabilidad=".(string)$src[$j]['idArearesp']));                        

                            $src[$j]['idEmpleado']=$this->ObtenerNombreEmpleado($client->get("empleados_gras?idExpediente=".(string)$src[$j]['idEmpleado']));

                            array_push($json, $src[$j]);//adicionarle al json los elementos del array que pertenesca al centro de costo                        
                        }
                        
                }
            } 
            else{

                $src=$data['hydra:member'];   
                  
                for ($j=0; $j < count($src) ; $j++) //recorrer los elementos del array que esta en "hydra:member"
                    {      
                        $src[$j]['idArearesp']=$this->ObtenerAreaResponsabilidad($client->get("areas_responsabilidads?idArearesponsabilidad=".(string)$src[$j]['idArearesp']));       
                        $src[$j]['idEmpleado']=$this->ObtenerNombreEmpleado($client->get("empleados_gras?idExpediente=".(string)$src[$j]['idEmpleado']));    
                        array_push($json, $src[$j]);//adicionarle al json los elementos del array que pertenesca al centro de costo                        
                    }
                    
                    
            }         
            
            
        
      
      //renderizar al html los objetos json capturados
        return $this->render('activosBundle:Default:activos.html.twig',array(
            "json"=>$json,          
            
            ));
    }
    public function inventarioAction()
    {
        return $this->render('activosBundle:Default:inventario.html.twig');
    }
   
    public function uprCnmbAction(){
        $peticion = $this->getRequest();//objeto POST or GET de una peticion URL
        $tipocnmb=$peticion->get('tipocnmb');//obtener el Objeto "id" pasado por la peticion          

        $tipocnmb=$this->GetID($tipocnmb);//separar el objeto id para obtener los numeros reales del id       
        
        
        $client = new Client("http://apiassets.upr.edu.cu");//abrir un nuevo cliente guzzle
        $json=Array();//crear una variable json de tipo array

            $request = $client->get("activo_fijos?cnmb=".(string)$tipocnmb);//hacerle una peticion GET a la paguina consecutiva
            $response = $request->send();//enviar la peticion
            $data = $response->json(); //recoger el json de la peticion            
            
            $count=$data['hydra:lastPage'];            

            $count=$this->GetCantPageFilter($count);                                 

            if ($count!=0) {
                for ($i=1; $i < $count ; $i++) { 

                    $request = $client->get("activo_fijos?cnmb=".(string)$tipocnmb."&page=".(string)$i);
                    $response = $request->send();            
                    $data = $response->json(); 
                    $src=$data['hydra:member'];   

                    

                    for ($j=0; $j < count($src) ; $j++) //recorrer los elementos del array que esta en "hydra:member"
                        {  
                           // $src[$j]['idEmpleado']=$this->ObtenerNombreEmpleado($client->get("empleados_gras?idExpediente=".(string)$src[$j]['idEmpleado']));  
                            //$src[$j]['idArearesp']=$this->ObtenerAreaResponsabilidad($client->get("areas_responsabilidads?idArearesponsabilidad=".(string)$src[$j]['idArearesp']));         
                            
                            array_push($json, $src[$j]);//adicionarle al json los elementos del array que pertenesca al centro de costo                        
                            
                           
                        }
                        
                }
            } 
            else{

                
                $src=$data['hydra:member'];   

                for ($j=0; $j < count($src) ; $j++) //recorrer los elementos del array que esta en "hydra:member"
                    {     
                        //$src[$j]['idEmpleado']=$this->ObtenerNombreEmpleado($client->get("empleados_gras?idExpediente=".(string)$src[$j]['idEmpleado']));    
                        //$src[$j]['idArearesp']=$this->ObtenerAreaResponsabilidad($client->get("areas_responsabilidads?idArearesponsabilidad=".(string)$src[$j]['idArearesp']));    
                        array_push($json, $src[$j]);//adicionarle al json los elementos del array que pertenesca al centro de costo                        
                    }
                
                
            }         
            

           
            
            
      
      //renderizar al html los objetos json capturados
        return $this->render('activosBundle:Default:activos.html.twig',array(
            "json"=>$json,          
            
            ));
    }

    public function tipocnmbAction(){
        $peticion = $this->getRequest();//objeto POST or GET de una peticion URL
        $tipocnmb=$peticion->get('tipocnmb');//obtener el Objeto "id" pasado por la peticion          

        $tipocnmb=$this->GetID($tipocnmb);//separar el objeto id para obtener los numeros reales del id

        $id=$peticion->get('id');//obtener el Objeto "id" pasado por la peticion        
        $id=$this->GetID($id);//separar el objeto id para obtener los numeros reales del id
        

        $client = new Client("http://apiassets.upr.edu.cu");//abrir un nuevo cliente guzzle
        $json=Array();//crear una variable json de tipo array

            $request = $client->get("activo_fijos?idCcosto=".(string)$id."&cnmb=".(string)$tipocnmb);//hacerle una peticion GET a la paguina consecutiva
            $response = $request->send();//enviar la peticion
            $data = $response->json(); //recoger el json de la peticion            
            
            $count=$data['hydra:lastPage'];;         
            $count=$this->GetCantMultiFilter($count);                     

            if ($count!=0) {
                for ($i=1; $i <$count ; $i++) { 

                    $request = $client->get("activo_fijos?idCcosto=".(string)$id."&cnmb=".(string)$tipocnmb."&page=".(string)$i);
                    $response = $request->send();            
                    $data = $response->json(); 
                    $src=$data['hydra:member'];   

                    for ($j=0; $j < count($src) ; $j++) //recorrer los elementos del array que esta en "hydra:member"
                        {  
                           $src[$j]['idEmpleado']=$this->ObtenerNombreEmpleado($client->get("empleados_gras?idExpediente=".(string)$src[$j]['idEmpleado']));  
                            $src[$j]['idArearesp']=$this->ObtenerAreaResponsabilidad($client->get("areas_responsabilidads?idArearesponsabilidad=".(string)$src[$j]['idArearesp']));         
                            array_push($json, $src[$j]);//adicionarle al json los elementos del array que pertenesca al centro de costo                        
                        }
                }
            } 
            else{

                $src=$data['hydra:member'];   

                for ($j=0; $j < count($src) ; $j++) //recorrer los elementos del array que esta en "hydra:member"
                    {     
                       $src[$j]['idEmpleado']=$this->ObtenerNombreEmpleado($client->get("empleados_gras?idExpediente=".(string)$src[$j]['idEmpleado']));    
                        $src[$j]['idArearesp']=$this->ObtenerAreaResponsabilidad($client->get("areas_responsabilidads?idArearesponsabilidad=".(string)$src[$j]['idArearesp']));    
                        array_push($json, $src[$j]);//adicionarle al json los elementos del array que pertenesca al centro de costo                        
                    }
            }         
            
            
        
      
      //renderizar al html los objetos json capturados
        return $this->render('activosBundle:Default:activos.html.twig',array(
            "json"=>$json,          
            
            ));
        
    }
    public function homeAction(){
         return $this->render('activosBundle:Default:home.html.twig');
    }

    
    public function activos_cnmbAction()
    {
        $peticion = $this->getRequest();//objeto POST or GET de una peticion URL
        $idcnmb=$peticion->get('idcnmb');//obtener el Objeto "id" pasado por la peticion          

        //$idcnmb=$this->GetID($idcnmb);//separar el objeto id para obtener los numeros reales del id

        $id=$peticion->get('id');//obtener el Objeto "id" pasado por la peticion        
        $id=$this->GetID($id);//separar el objeto id para obtener los numeros reales del id
        

        $client = new Client("http://apiassets.upr.edu.cu");//abrir un nuevo cliente guzzle
        $json=Array();//crear una variable json de tipo array

            $request = $client->get("activo_fijos?idCcosto=".(string)$id."&descActivofijo=".(string)$idcnmb);//hacerle una peticion GET a la paguina consecutiva
            $response = $request->send();//enviar la peticion
            $data = $response->json(); //recoger el json de la peticion            
            
            $count=$data['hydra:lastPage'];;         
            $count=$this->GetCantMultiFilter($count);                     

            if ($count!=0) {
                for ($i=1; $i <$count ; $i++) { 

                    $request = $client->get("activo_fijos?idCcosto=".(string)$id."&descActivofijo=".(string)$idcnmb."&page=".(string)$i);
                    $response = $request->send();            
                    $data = $response->json(); 
                    $src=$data['hydra:member'];   

                    for ($j=0; $j < count($src) ; $j++) //recorrer los elementos del array que esta en "hydra:member"
                        {  
                            $src[$j]['idEmpleado']=$this->ObtenerNombreEmpleado($client->get("empleados_gras?idExpediente=".(string)$src[$j]['idEmpleado']));
                            $src[$j]['idArearesp']=$this->ObtenerAreaResponsabilidad($client->get("areas_responsabilidads?idArearesponsabilidad=".(string)$src[$j]['idArearesp']));           
                            array_push($json, $src[$j]);//adicionarle al json los elementos del array que pertenesca al centro de costo                        
                        }
                }
            } 
            else{

                $src=$data['hydra:member'];   

                for ($j=0; $j < count($src) ; $j++) //recorrer los elementos del array que esta en "hydra:member"
                    {   
                        $src[$j]['idEmpleado']=$this->ObtenerNombreEmpleado($client->get("empleados_gras?idExpediente=".(string)$src[$j]['idEmpleado']));  
                        $src[$j]['idArearesp']=$this->ObtenerAreaResponsabilidad($client->get("areas_responsabilidads?idArearesponsabilidad=".(string)$src[$j]['idArearesp']));                            
                        array_push($json, $src[$j]);//adicionarle al json los elementos del array que pertenesca al centro de costo                        
                    }
            }         
            
            
        
      
      //renderizar al html los objetos json capturados
        return $this->render('activosBundle:Default:activos.html.twig',array(
            "json"=>$json,          
            
            ));       
        
    }

    public function activos_upr_cnmbAction()
    {
        $peticion = $this->getRequest();//objeto POST or GET de una peticion URL
        $idcnmb=$peticion->get('idcnmb');//obtener el Objeto "id" pasado por la peticion          

        //echo $idcnmb;
        $client = new Client("http://apiassets.upr.edu.cu");//abrir un nuevo cliente guzzle
        $json=Array();//crear una variable json de tipo array

            $request = $client->get("activo_fijos?descActivofijo=".(string)$idcnmb);//hacerle una peticion GET a la paguina consecutiva
            $response = $request->send();//enviar la peticion
            $data = $response->json(); //recoger el json de la peticion            
            
            $count=$data['hydra:lastPage'];           

            $count=$this->GetCantPageFilter($count);               



            if ($count!=0) {
                for ($i=1; $i <$count ; $i++) { 

                    $request = $client->get("activo_fijos?descActivofijo=".(string)$idcnmb."&page=".(string)$i);
                    $response = $request->send();            
                    $data = $response->json(); 
                    $src=$data['hydra:member'];   

                    for ($j=0; $j < count($src) ; $j++) //recorrer los elementos del array que esta en "hydra:member"
                        {  
                            $src[$j]['idEmpleado']=$this->ObtenerNombreEmpleado($client->get("empleados_gras?idExpediente=".(string)$src[$j]['idEmpleado']));
                            $src[$j]['idArearesp']=$this->ObtenerAreaResponsabilidad($client->get("areas_responsabilidads?idArearesponsabilidad=".(string)$src[$j]['idArearesp']));           
                            array_push($json, $src[$j]);//adicionarle al json los elementos del array que pertenesca al centro de costo                        
                        }
                }
            } 
            else{

                $src=$data['hydra:member'];   

                for ($j=0; $j < count($src) ; $j++) //recorrer los elementos del array que esta en "hydra:member"
                    {   
                        $src[$j]['idEmpleado']=$this->ObtenerNombreEmpleado($client->get("empleados_gras?idExpediente=".(string)$src[$j]['idEmpleado']));  
                        $src[$j]['idArearesp']=$this->ObtenerAreaResponsabilidad($client->get("areas_responsabilidads?idArearesponsabilidad=".(string)$src[$j]['idArearesp']));                            
                        array_push($json, $src[$j]);//adicionarle al json los elementos del array que pertenesca al centro de costo                        
                    }
            }  
                 
            
        return $this->render('activosBundle:Default:activos.html.twig',array(
            "json"=>$json
            ));
    }

    public function activosAction()//saber los activos fijos  dado el id de un area
    {
        
        $peticion = $this->getRequest();//objeto POST or GET de una peticion URL
        $id=$peticion->get('id');//obtener el Objeto "id" pasado por la peticion        
        $id=$this->GetID($id);//separar el objeto id para obtener los numeros reales del id
        
        $client = new Client("http://apiassets.upr.edu.cu");//abrir un nuevo cliente guzzle
        $json=Array();//crear una variable json de tipo array

            $request = $client->get("activo_fijos?idCcosto=".(string)$id);//hacerle una peticion GET a la paguina consecutiva
            $response = $request->send();//enviar la peticion
            $data = $response->json(); //recoger el json de la peticion
                 

            $count=$data['hydra:lastPage'];         
            $count=$this->GetCantPageFilter($count);  

            if ($count!=0) {
                for ($i=1; $i <$count ; $i++) { 

                    $request = $client->get("activo_fijos?idCcosto=".(string)$id."&page=".(string)$i);
                    $response = $request->send();            
                    $data = $response->json(); 
                    $src=$data['hydra:member'];   

                    for ($j=0; $j < count($src) ; $j++) //recorrer los elementos del array que esta en "hydra:member"
                        {    
                            $src[$j]['idEmpleado']=$this->ObtenerNombreEmpleado($client->get("empleados_gras?idExpediente=".(string)$src[$j]['idEmpleado'])); 
                            $src[$j]['idArearesp']=$this->ObtenerAreaResponsabilidad($client->get("areas_responsabilidads?idArearesponsabilidad=".(string)$src[$j]['idArearesp']));                                
                            array_push($json, $src[$j]);//adicionarle al json los elementos del array que pertenesca al centro de costo                        
                        }
                }
            }
            
        
      
      //renderizar al html los objetos json capturados
        return $this->render('activosBundle:Default:activos.html.twig',array(
            "json"=>$json
            ));

    }
    public function indexAction()//mostrar todos los "centros de costos" existentes en la UPR
    {

    	$count=$this->GetCantPage('centro_costos');
        $countcnmb=$this->GetCantPage('cnmb_activofijos');
        $json=Array();
        $jsoncnmb= Array();
        $client = new Client("http://apiassets.upr.edu.cu");

        if ($count!=0) {
            for ($i=1; $i <$count ; $i++) {  
              
                $request = $client->get("centro_costos?page=".(string)$i);                    
                $response = $request->send();            
                $data = $response->json(); 
                $src=$data['hydra:member']; 
                for ($j=0; $j < count($src) ; $j++) { 
                           array_push($json, $src[$j]);
                       } 
            
            }
        }
        else
        {
            $request = $client->get("centro_costos");                    
            $response = $request->send();            
            $data = $response->json(); 
            $src=$data['hydra:member']; 
            for ($j=0; $j < count($src) ; $j++) { 
                       array_push($json, $src[$j]);
                   }   
            
        }

        for ($i=1; $i <$countcnmb ; $i++) {  
              
                $request = $client->get("cnmb_activofijos?page=".(string)$i);                    
                $response = $request->send();            
                $data = $response->json(); 
                $src=$data['hydra:member'];                 
                for ($j=0; $j < count($src) ; $j++) { 
                           array_push($jsoncnmb, $src[$j]);
                       } 
            
            }
    	
    	
        return $this->render('activosBundle:Default:index.html.twig',array(
        	"json"=>$json,
            "jsoncnmb"=>$jsoncnmb,
            
        	));
    }
    public function multiexplode ($delimiters,$string) {
   
        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);
        return  $launch;
    }

    public function GetID($id)//Desglosar el "id" de /activo_fijos/%20972537%20%20%20%20%20%20%20%20" a "20972537"
    {
        $aux= $this->multiexplode(array("/","%","+","?","="),$id);
        return $aux[2];
        //print_r($aux);
    }
    public function GetCantPage($tabla)//obtener la cantidad de paguinas que tiene el json
    {
    	$client = new Client('http://apiassets.upr.edu.cu');
        $request = $client->get($tabla);
        $response = $request->send();
        $data = $response->json();        
        $count= $data['hydra:totalItems'];
        $total = $data['hydra:itemsPerPage'];
        if ($count < $total ) {
           return 0;
        }
        else
        {        
            $count= $data['hydra:lastPage'];
            $a=preg_split("/[\s,=]+/", $count);        
            return $a[1];    
           //print_r($a);
        }
    }

    public function GetCantPageFilter($count)//obtener la cantidad de paguinas que tiene el json
    {
           
            $a=preg_split("/[\s,=]+/", $count);  
            
            if(count($a)>2)
                return $a[2];    
            else
                return 0;   
    }

    public function GetCantMultiFilter($count)
    {
        
        $a=preg_split("/[\s,=,%]+/", $count);                
        if(count($a)>3)
            return $a[3];    
        else
            return 0;                
    }
    
    
}
