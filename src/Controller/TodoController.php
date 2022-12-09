<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

 #[Route('/todo')] 
class TodoController extends AbstractController
{   /**
    * @Route("/",name="todo")
    */
    public function index(Request $request): Response
    {   
        $session=$request->getSession();
        if(!$session->has('todos'))
        {
            $todos=[
                'achat'=>'acheter clé usb',
                'cours'=>'Finaliser mon cours',
                'correction'=>'corriger mes examens'
              ];
            $session->set('todos',$todos);
            $this->addFlash('info',"La liste des todos viens d'être initialisé");
        }
        //Afficher un tableau de todo
        //sinon je l'initialiase puis j'affihe
        //si j'ai un tableau  de todo dans ma session je ne fait que l'afficher
        return $this->render('todo/index.html.twig');
    }
    #[Route('/add/{name?test}/{content?test}', name: 'todo.add')]
    public function addTodo(Request $request,$name,$content):RedirectResponse{
    $session=$request->getSession();  
     if($session->has('todos'))
     {
       $todos=$session->get('todos');
       if(isset($todos[$name]))
       {
        $this->addFlash('error',"Le todo existe d'id $name  déja dans la liste");
       }
       else
       {
           $todos[$name]=$content;
           $session->set('todos',$todos);
           $this->addFlash('success',"Le todo  d'id $name a été ajouté avec succès");
       }
     }else{
        $this->addFlash('error',"La liste des todos n'est pas encore  initialisé");
     }
     return $this->redirectToRoute('todo');
    }
    #[Route('/update/{name}/{content}', name: 'todo.update')]
    public function updateTodo(Request $request,$name,$content):RedirectResponse{
    $session=$request->getSession();  
     if($session->has('todos'))
     {
       $todos=$session->get('todos');
       if(!isset($todos[$name]))
       {
        $this->addFlash('error',"Le todo  d'id $name  n'existe pas dans la liste");
       }
       else
       {
           $todos[$name]=$content;
           $session->set('todos',$todos);
           $this->addFlash('success',"Le todo  d'id $name a été modifié avec succès");
       }
     }else{
        $this->addFlash('error',"La liste des todos n'est pas encore  initialisé");
     }
     return $this->redirectToRoute('todo');
    }

    #[Route('/delete/{name}', name: 'todo.delete')]
    public function deleteTodo(Request $request,$name):RedirectResponse{
    $session=$request->getSession();  
     if($session->has('todos'))
     {
       $todos=$session->get('todos');
       if(!isset($todos[$name]))
       {
        $this->addFlash('error',"Le todo  d'id $name  n'existe pas dans la liste");
       }
       else
       {
           unset($todos[$name]);
           $session->set('todos',$todos);
           $this->addFlash('success',"Le todo  d'id $name a été supprimé avec succès");
       }
     }else{
        $this->addFlash('error',"La liste des todos n'est pas encore  initialisé");
     }
     return $this->redirectToRoute('todo');
    }
    #[Route('/reset', name: 'todo.reset')]
    public function resetTodo(Request $request):RedirectResponse{
        $session=$request->getSession();  
        $session->remove('todos'); 
        return $this->redirectToRoute('todo');
        }

    #[Route('multi/{entier1<\d+>}/{entier2}',name:'multiplication',requirements:['entier1'=>'\d+','entier2'=>'\d+'])]
    public function multiplication($entier1,$entier2)
    {
     $resultat=$entier1 * $entier2;
     return new Response("<h1>$resultat</h1>");
    }
}
