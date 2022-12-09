<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FirstController extends AbstractController
{   #[Route('/template',name:'template')]
    public function template()
    {
        return $this->render('template.html.twig');
    }
    #[Route('/first', name: 'first')]
    public function index(): Response
    {   // chercher au la base de données de vos users
        return $this->render('first/index.html.twig', [
            'name' => 'Sellaouti',
            'firstname' => 'Aymen'
        ]);
    }
 /*    #[Route('/sayHello/{name}/{firstname}', name: 'say.hello')] */
    public function sayHello(Request $request,$name,$firstname): Response
    {   // chercher au la base de données de vos users
        return $this->render('first/hello.html.twig',[
            'nom'=>$name,
            'prenom'=>$firstname,
            'path'=>'tim.jpg'
        ]);
    }
}
