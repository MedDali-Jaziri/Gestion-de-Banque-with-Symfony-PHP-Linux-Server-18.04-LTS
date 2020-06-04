<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Entity\User;
use App\Form\UserType;
use App\Form\compteType;
use App\Entity\Client;
use App\Entity\CompteCourant;
use App\Entity\CompteEpargne;
use App\Entity\Versement;
use App\Entity\Retrait;
use App\Entity\Compte;

// Include paginator interface
use Knp\Component\Pager\PaginatorInterface;


class BanqueApplicationController extends AbstractController
{
    public $codeActuelle;

    /**
     * @Route("/banque/application", name="banque_application")
     */
    public function index()
    {
        return $this->render('banque_application/index.html.twig', [
            'controller_name' => 'BanqueApplicationController',
        ]);
    }

    /**
     * @Route("/banque/register", name="user_registration")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {

        $user = new User();
        $form = $this->createForm(UserType::class, $user);


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            /*return $this->redirectToRoute('show', [
                'id' => $user->getId(),
            ]);*/
            return $this->redirectToRoute('login');
        }

        return $this->render('Authentification/form.html.twig',
            array('form' => $form->createView())
        );
    }


    /**
     * @Route("/banque/add", name="add")
     */
    public function add(Request $request)
    {

        $compte = new CompteCourant();

        $form = $this->createForm(compteType::class, $compte);


        $form->handleRequest($request);
        

        return $this->render('banque_application/add.html.twig',
            array('form' => $form->createView())
        );
    }


     /**
     * @Route("/show/{id}", name="show")
     */
    public function show(User $user){
        return $this->render('Authentification/show.html.twig',
            array('user' => $user)
        );
    }

    /**
     * @Route("/banque/login", name="login")
     */
    public function loginPage(AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('banque_application/loginPage.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

    /**
     * @Route("/banque/logout", name="logout")
     */
    public function logout(){

    }

    /**
     * @Route("/", name="comptes")
     */
    public function comptesPage(Request $request)
    {

        $codecompte;

        $form = $this ->createFormBuilder()
                      ->add('codeCompte')
                      //->add('montant')
                      ->getForm();

        $form->handleRequest($request);

        if( $form->isSubmitted())
        {
            $form_data = $form->getData();
            $data['form'] = [];
            $data['form'] = $form_data;
            $codecompte =$form_data['codeCompte'];

            //return new Response($form_data['codeCompte']);
            return $this->redirectToRoute("consulterPage",array('codecompte'=>$codecompte));
            
        }       

        return $this->render('banque_application/consulterPage.html.twig', [
            'controller_name' => 'BanqueApplicationController',
        ]);
    }

    /**
     * @Route("/banque/consulterPage/{codecompte}", name="consulterPage")
     */
    
    public function consulterPage(Request $request,$codecompte, PaginatorInterface $paginator)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
       
        $form = $this ->createFormBuilder()
                      ->add('codeCompte')
                      //->add('montant')
                      ->getForm();



        $form->handleRequest($request);

        $dataExport;
        $solde=0;
        $typeCompte="";
        $montant=0;
        //$codecompte="c4";

        if( $form->isSubmitted())
        {
            $form_data = $form->getData();
            $data['form'] = [];
            $data['form'] = $form_data;
            $codecompte =$form_data['codeCompte'];
            
        }

        $compte = $this->getDoctrine()
        ->getRepository('App:Compte')
        ->find($codecompte);

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('App:Operation');
        $operation = $repository->findAllOperationByCodeCompte($codecompte);

        // Paginate the results of the query
        $appointments = $paginator->paginate(
            // Doctrine Query, not results
            $operation,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            4
        );
        
        $nobreRows = count($operation);
        $nbrePage = \ceil($nobreRows/4);

        //return new Response($nbrePage);

        if (!$compte){
            throw $this->createNotFoundException("Aucune compte ne corespons ".$codecompte);
        }

        $solde = $compte->getSolde();


        $CC = \get_class($compte);
        $dataExport = $compte;

        $type = \get_class($compte);
        $CompteCourant="App\Entity\CompteCourant";
        $CompteEpargne="App\Entity\CompteEpargne";

        if (\strcmp($type,$CompteCourant)==0){

            $typeCompte = \substr($type,11,100);

            $resultat = $compte->getDecouvert();

            $decouvert =  $resultat;

        }
        elseif((\strcmp($type,$CompteEpargne)==0)){

            $typeCompte = \substr($type,11,100);

            $resultat = $compte->getTaux();

            $decouvert =  $resultat;
        }


        //$montant = $operation->getMontant();
        //$date = $operation->getDateOperation();

        //var_dump($operation);
        //exit();
        //$numero = $operation->getNumero();

        //return new Response($numero);
        return $this->render('banque_application/comptesPage.html.twig', ['comptes' => $dataExport,'codeCompte'=>$codecompte,'typeCompte'=>$typeCompte, 'decouvert'=>$decouvert, 'operation'=>$operation,'nbrePage'=>$nbrePage,'appointments' => $appointments]);

    }


    /**
     * @Route("/banque/OperationPage", name="OperationPage")
     */
    public function OperationPage(Request $request, PaginatorInterface $paginator)
    {
        $code = "c1";
        $codeC;
        $form = $this ->createFormBuilder()
                      ->add('CodeC')
                      ->add('montant')
                      ->add('typeOperation')
                      ->add('codeCompte2')
                      ->getForm();



        $form->handleRequest($request);

        $montant;
        $typeOperation="";
        $versement="VERS";
        $retrer="RETR";
        $virement="VIR";
        $codecompte2;
        $codeC;
        $soldeActuelle;
        $solde = 0;
        $soldeAjoute;
        if( $form->isSubmitted())
        {
            $form_data = $form->getData();
            $data['form'] = [];
            $data['form'] = $form_data;
            $codeC = $form_data['CodeC'];
            $montant = $form_data['montant'];
            $typeOperation = $form_data['typeOperation'];
            $codecompte2 = $form_data['codeCompte2'];

            
        }

        //return new Response("Hello ".$codeC);

        $compte = $this->getDoctrine()
                            ->getRepository('App:Compte')
                            ->find($codeC);

        $CC = \get_class($compte);
        $dataExport = $compte;

        $type = \get_class($compte);
        $CompteCourant="App\Entity\CompteCourant";
        $CompteEpargne="App\Entity\CompteEpargne";

        if (\strcmp($type,$CompteCourant)==0){

            $typeCompte = \substr($type,11,100);

            $resultat = $compte->getDecouvert();

            $decouvert =  $resultat;

        }
        elseif((\strcmp($type,$CompteEpargne)==0)){

            $typeCompte = \substr($type,11,100);

            $resultat = $compte->getTaux();

            $decouvert =  $resultat;
        }

            
        //$solde = $compte->getSolde();
        $solde = $compte->getSolde();

        if (\strcmp($typeOperation,$versement)==0){

            $entityManager = $this->getDoctrine()->getManager();
            $compte->setSolde($solde + $montant);
            $entityManager->persist($compte);
            $entityManager->flush();


            $Operation = new Versement();
    
            $Operation->setCompte($compte);
            $Operation->setMontant($montant);
            $dt = new \DateTime('@'.strtotime('now'));
            $Operation->setDateOperation($dt);
    
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($Operation);
            $entityManager->flush();


            //return new Response("Hello ".$typeOperation." ".$solde);

        }
        elseif (\strcmp($typeOperation,$retrer)==0) {
            //return new Response("Hello ".$typeOperation);
            $entityManager = $this->getDoctrine()->getManager();
            $soldeActuelle = $compte->getSolde();

            if ( $soldeActuelle < $montant){
                throw $this->createNotFoundException("Solde Insuffisant !! ");
                
            }
            $compte->setSolde($soldeActuelle - $montant);
            $entityManager->persist($compte);
            $entityManager->flush();

            $Operation = new Retrait();
    
            $Operation->setCompte($compte);
            $Operation->setMontant($montant);
            $dt = new \DateTime('@'.strtotime('now'));
            $Operation->setDateOperation($dt);
    
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($Operation);
            $entityManager->flush();

        }
        elseif (\strcmp($typeOperation,$virement)==0) {
            //return new Response("Hello ".$typeOperation." ".$codecompte2);
            if (\strcmp($codeC,$codecompte2)==0){
                throw $this->createNotFoundException("Vérifier le compte SVP !! ");
            }

            $compte2 = $this->getDoctrine()
                       ->getRepository('App:Compte')
                       ->find($codecompte2);            

            if($compte2==null){
                throw $this->createNotFoundException("le Compte n'existe pas");
            }

            $entityManager = $this->getDoctrine()->getManager();
            $soldeActuelle = $compte->getSolde();
            if ( $soldeActuelle < $montant){
                throw $this->createNotFoundException("Solde Insuffisant !! ");
                
            }
            $compte->setSolde($soldeActuelle - $montant);
            $entityManager->persist($compte);
            $entityManager->flush();

            $entityManager = $this->getDoctrine()->getManager();
            $compte2->setSolde($compte2->getSolde() + $montant);
            $entityManager->persist($compte2);
            $entityManager->flush();

            $Operation = new Retrait();
    
            $Operation->setCompte($compte);
            $Operation->setMontant($montant);
            $dt = new \DateTime('@'.strtotime('now'));
            $Operation->setDateOperation($dt);
    
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($Operation);
            $entityManager->flush();


            $Operation2 = new Versement();
    
            $Operation2->setCompte($compte2);
            $Operation2->setMontant($montant);
            $dt = new \DateTime('@'.strtotime('now'));
            $Operation2->setDateOperation($dt);
    
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($Operation2);
            $entityManager->flush();

            
        }
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('App:Operation');
        $operation = $repository->findAllOperationByCodeCompte($codeC);

        // Paginate the results of the query
        $appointments = $paginator->paginate(
            // Doctrine Query, not results
            $operation,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            4
        );

        //return new Response($resultat);
        return $this->render('banque_application/comptesPage.html.twig', ['comptes' => $compte,'codeCompte'=>$codeC,'typeCompte'=>$typeCompte, 'decouvert'=>$decouvert, 'operation'=>$operation,'appointments' => $appointments]);

    }

    /**
     * @Route("/banque/AddComptePage", name="AddComptePage")
     */
    public function AddComptePage(Request $request)
    {
        /*return $this->render('banque_application/AddComptePage.html.twig', [
            'controller_name' => 'BanqueApplicationController',
        ]);*/
        $form = $this ->createFormBuilder()
                      ->add('codeCompte')
                      ->add('email')
                      ->add('name')
                      ->add('soldeCompte')
                      ->add('typeCompte')
                      ->getForm();

        $form->handleRequest($request);


        if( $form->isSubmitted() )
        {
            
            $form_data = $form->getData();
            $data['form'] = [];
            $data['form'] = $form_data;

            $client = new Client();

            $entityManager = $this->getDoctrine()->getManager();

            $client->setNom($form_data['name']);
            $client->setEmail($form_data['email']);
            $client->setCompte($form_data['codeCompte']);

            $compteRefuse = $this->getDoctrine()
                            ->getRepository('App:Compte')
                            ->find($form_data['codeCompte']);            

            if($compteRefuse!=null){
                if (\strcmp($form_data['codeCompte'],$compteRefuse->getcodeCompte())==0){
                    throw $this->createNotFoundException("Un compte à un et un seul client !!! Veuillez réessayer !!!!");
                }
                else{
                    echo "Sucees Update";
                }
            }
            

        
            // tell Doctrine you want to (eventually) save the Compte (no queries yet)
            $entityManager->persist($client);

            // actually executes the queries (i.e. the INSERT query)
            $entityManager->flush();

            //if (strcmp($form_data['typeCompte'],"CC")==0){
            if($form_data['typeCompte']==1){

                $compte = new CompteCourant();
                $entityManager = $this->getDoctrine()->getManager();
                $compte->setcodeCompte($form_data['codeCompte']);
                $compte->setClient($client);
                $dt = new \DateTime('@'.strtotime('now'));
                $compte->setDateCreation($dt);
                $compte->setSolde($form_data['soldeCompte']);
                $compte->setDecouvert(6);
                // tell Doctrine you want to (eventually) save the Compte (no queries yet)
                $entityManager->persist($compte);
                // actually executes the queries (i.e. the INSERT query)
                if($entityManager->flush());
                return $this->redirectToRoute("comptes");
            }
            else{
                $compte = new CompteEpargne();
                $entityManager = $this->getDoctrine()->getManager();
                $compte->setcodeCompte($form_data['codeCompte']);
                $compte->setClient($client);
                $dt = new \DateTime('@'.strtotime('now'));
                $compte->setDateCreation($dt);
                $compte->setSolde($form_data['soldeCompte']);
                $compte->setTaux(9);
                // tell Doctrine you want to (eventually) save the Compte (no queries yet)
                $entityManager->persist($compte);
                // actually executes the queries (i.e. the INSERT query)
                $entityManager->flush();
                return $this->redirectToRoute("comptes");
            }
        
        }  
        return $this->render('banque_application/AddComptePage.html.twig', [
            'controller_name' => 'BanqueApplicationController',
        ]); 
    }

    /**
     * @Route("/banque/ListeComptesPage", name="ListeComptesPage")
     */
    public function ListeComptesPage()
    {
        $client = $this->getDoctrine()
        ->getRepository('App:Client')
        ->findAll();


        return $this->render('banque_application/ListeComptesPage.html.twig', [
            'client' => $client
        ]);
    }

}
?>