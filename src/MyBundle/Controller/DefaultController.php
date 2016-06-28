<?php

namespace MyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use MyBundle\Entity\Area;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('MyBundle:Default:index.html.twig');
    }

    public function qwertyAction(Request $request)
    {
        $dato=$request->get('lol');
        $entidad=new Area();
        $entidad->setChoice($dato);
        $em = $this->getDoctrine()->getManager();
        $em->persist($entidad);
        $em->flush();

        return $this->render('area/new.html.twig', array(

        ));
    }
}
