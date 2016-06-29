<?php

namespace Contabilidad\activosBundle\Controller;

use GuzzleHttp\Pool;
use Guzzle\Http\Client;
use GuzzleHttp\Event\BeforeEvent;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    

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
            
            $src=$data['hydra:member']; //separar el json de la respuesta
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
        $json=Array();
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
    	
    	
        return $this->render('activosBundle:Default:index.html.twig',array(
        	"json"=>$json,
            
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
        return $aux[3];
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

    public function GetCantPageFilter($tabla)//obtener la cantidad de paguinas que tiene el json
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
            return $a[2];    
            //echo $count;
           //print_r($a);
        }
    }

    
}
