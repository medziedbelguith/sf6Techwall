<?php

namespace App\Controller;

use App\Entity\Personne;
use App\Event\AddPersonneEvent;
use App\Event\ListAllPersonnesEvent;
use App\Form\PersonneType;
use App\Service\Helpers;
use App\Service\MailerService;
use App\Service\PdfService;
use App\Service\UploaderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use  Doctrine\Persistence\ManagerRegistry;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/personne')
 ,IsGranted('ROLE_USER')
]
class PersonneController extends AbstractController
{   public function __construct(private LoggerInterface $logger,private Helpers $helper,private EventDispatcherInterface $dispatcher)
    {
    }
    #[Route('/',name:'personne.list')]
    public function index(ManagerRegistry $doctrine){
      $repository=$doctrine->getRepository(Personne::class);
      $personnes=$repository->findAll();
      return $this->render('personne/index.html.twig',['personnes' => $personnes,'isPaginated'=>true]);
    }
    #[Route('/pdf/{id}',name:'personne.pdf')]
    public function generatePdfPersonne(Personne $personne = null,PdfService $pdf)
    {
      $html=$this->render('personne/detail.html.twig',['personne'=>$personne]);
      $pdf->showPdfProfile($html);
    }
    #[Route('/alls/age/{ageMin}/{ageMax}',name:'personne.list.age')]
    public function personneByAge(ManagerRegistry $doctrine,$ageMin,$ageMax):Response{
      $repository=$doctrine->getRepository(Personne::class);
      $personnes=$repository->findPersonnesByAgeInterval($ageMin,$ageMax);
      return $this->render('personne/index.html.twig',['personnes' => $personnes]);
    }
    #[Route('/stats/age/{ageMin}/{ageMax}',name:'personne.list.age')]
    public function statsPersonneByAge(ManagerRegistry $doctrine,$ageMin,$ageMax):Response{
      $repository=$doctrine->getRepository(Personne::class);
      $stats=$repository->statsPersonnesByAgeInterval($ageMin,$ageMax);
      return $this->render('personne/stats.html.twig',
      ['stats' => $stats[0],
      'ageMin'=>$ageMin,
      'ageMax'=>$ageMax] 
    );
    }
    #[Route('/alls/{page?1}/{nbre?12}',
    name:'personne.list.alls'),
    IsGranted("ROLE_USER")]
    public function indexAlls(ManagerRegistry $doctrine,$page,$nbre){
   // echo ($this->helper->sayCC());
      $repository=$doctrine->getRepository(Personne::class);
      $nbPersonne=$repository->count([]);
      $nbrePage=ceil($nbPersonne / $nbre);
      $personnes=$repository->findBy([],[],$nbre,($page-1)*$nbre);
      $listAllPersonneEvent=new ListAllPersonnesEvent(count($personnes));
      $this->dispatcher->dispatch($listAllPersonneEvent,ListAllPersonnesEvent::LIST_ALL_PERSONNE_EVENT);
      return $this->render('personne/index.html.twig',['personnes' => $personnes
       ,'isPaginated'=>true
       ,'nbrePage'=>$nbrePage
       ,'page'=>$page
       ,'nbre'=>$nbre
    ]);
    }
    #[Route('/{id<\d+>}',name:'personne.detail')]
    public function detail(Personne $personne = null):Response{
      /* $repository=$doctrine->getRepository(Personne::class);
      $personne=$repository->find($id); */
      if(!$personne)
      {
       $this->addFlash('error',"La personne  n'existe pas");
       return  $this->redirectToRoute('personne.list');
      }
      return $this->render('personne/detail.html.twig',['personne' => $personne]);
    }
    #[Route('/edit/{id?0}', name: 'personne.edit')]
    public function addPersonne(Personne $personne = null,ManagerRegistry $doctrine,Request $request,UploaderService $uploaderService,MailerService $mailer): Response
    {   $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $new=false;
        if(!$personne)
        {   $new=true;
            $personne=new Personne();
        }
        $form=$this->createForm(PersonneType::class,$personne);
        $form->remove('createdAt');
        $form->remove('updatedAt');
        //dump($request);
        $form=$form->handleRequest($request);
        
        if ($form->isSubmitted()&&$form->isValid()) {
            $photo = $form->get('photo')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($photo) {
                $directory=$this->getParameter('personne_directory');
                $personne->setImage($uploaderService->uploadFile($photo,$directory));
            }
            if($new)
            {
                $message="a été ajouté avec succés";
                $personne->getCreatedBy($this->getUser());
            }
            else
            {
                $message="a été mis à jour  avec succés";  
            }
            $manager=$doctrine->getManager();
            $manager->persist($personne);
            $manager->flush();
            if($new)
            {
              $addPersonneEvent=new AddPersonneEvent($personne);
              $this->dispatcher->dispatch($addPersonneEvent,AddPersonneEvent::ADD_PERSONNE_EVENT);
            }
            
            $this->addFlash('success',$personne->getName().$message);
            return  $this->redirectToRoute('personne.list.alls');
        }else{
            return $this->render('personne/add-personne.html.twig',[
                'form' => $form->createView()
            ]);
        }
    }
    #[Route('/delete/{id}',name:'personne.delete'),
       IsGranted('ROLE_ADMIN')
    ]
    public function deletePersonne(Personne $personne=null,ManagerRegistry $doctrine):RedirectResponse{
       // dd($personne);
      if($personne)
      {
       $manager=$doctrine->getManager();
       $manager->remove($personne);
       $manager->flush();
       $this->addFlash('success',"La personne a été supprimé avec succès");
      }else{
        $this->addFlash('error',"Personne inexistante");
      }
      return  $this->redirectToRoute('personne.list.alls');
    }
    #[Route('/update/{id}/{name}/{firstname}/{age}',name:'personne.update')]
    public function updatePersonne(Personne $personne=null,ManagerRegistry $doctrine,$name,$firstname,$age){
      if($personne)
      {
        $personne->setName($name);
        $personne->setFirstName($firstname);
        $personne->setAge($age);
        $manager=$doctrine->getManager();
        $manager->persist($personne);
        $manager->flush();
        $this->addFlash('success','La personne a été mis à jour avec succès');
      }else{
        $this->addFlash('error',"Personne inexistante");
      }
      return  $this->redirectToRoute('personne.list.alls');
    }
}
