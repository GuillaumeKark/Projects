<?php

namespace Phinedo\OutilsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

use Phinedo\OutilsBundle\Entity\Mailing;
use Phinedo\OutilsBundle\Form\MailingType;

class DefaultController extends Controller
{
    public function creerModifierMailingAction($mailId=null, Request $request, $repost = 0){

        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès non authorisé.');
        }

    	$mailing = $this->getDoctrine()->getRepository('PhinedoOutilsBundle:Mailing')->findOneById($mailId);

        if($mailing === null)
        {
            $type = "creation";
            $mailing = new Mailing();
            $form = $this->createForm(new MailingType(), $mailing);
        }
        else
        {
            $type = 'modification';
            if($repost)
            {
                $mailingOld = $mailing;
                $type = "creation";
                $mailing = new Mailing();
                $mailing->setContent($mailingOld->getContent());
                $mailing->setObjet($mailingOld->getObjet());
            }
            $form = $this->createForm(new MailingType(), $mailing);
        }
        $form->handleRequest($request);
        if($form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $em->persist($mailing);
            $em->flush();
            
            //LOG
            // ($type === 'modification') ? $this->get('log_creator')->add(LogCreator::UPDATE, $mailing) : $this->get('log_creator')->add(LogCreator::CREATE, $agenda);
            
            return $this->redirect($this->generateUrl('phinedo_outils_mailing_list'));
        }

    	return $this->render('PhinedoOutilsBundle::mail.html.twig', array(
            'form' => $form->createView(),
            'type' => $type,
    		));
    }

    public function listeMailingAction($page=1){
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès non authorisé.');
        }

        $this->get('fil_arianne')
            ->addFil('Mailing')
            ->addMenu('Créer une nouvelle mailing', 'phinedo_outils_mailing_creer')
        ;

        $liste = $this->getDoctrine()->getRepository('PhinedoOutilsBundle:Mailing')->liste();

        return $this->render('PhinedoOutilsBundle::liste.html.twig', array('liste'=>$liste));
    }

    public function listeMailingAjaxAction($page){
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            throw new AccessDeniedException('Accès non authorisé.');
        }
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        
        $liste = $this->getDoctrine()->getRepository('PhinedoOutilsBundle:Mailing')->liste($page);
        return new JsonResponse($serializer->serialize($liste, 'json'));
    }

    public function voirLMailsAction(){
        $listeActifs = $this->getDoctrine()->getRepository("PhinedoUserBundle:User")->listeActifs();
        $listeMails = array();
        $content = "";
        foreach ($listeActifs as $key => $user) {
            $i++;
            $listeMails[] = $user->getEmail();
            $content .= $i." - ".$user->getEmail();
        }
        $content .= count($listeMails);
        return $this->render('PhinedoOutilsBundle::lmails.html.twig', array('content' => $content));
    }

    public function afficherMailingAction($mailId){
        $mail = $this->getDoctrine()->getRepository('PhinedoOutilsBundle:Mailing')->find($mailId);

        if($mail == null){
            $this->redirectToUrl('phinedo_outils_mailing_list');
        }

        return $this->render('PhinedoOutilsBundle::afficher.html.twig', array('mail' => $mail));
    }

    public function afficherRawMailingAction($mailId){
        $mail = $this->getDoctrine()->getRepository('PhinedoOutilsBundle:Mailing')->find($mailId);

        if($mail == null){
            $this->redirectToUrl('PhinedoOutilsBundle::afficher_raw_base.html.twig');
        }

        $patterns = array();
        $patterns[0] = '/{{PRENOM}}/';
        $patterns[1] = '/{{NOM}}/';
        $patterns[2] = '/{{SURNOM}}/';
        $patterns[3] = '/{{MAIL}}/';

        $replacements = array();
        $replacements[0] = 'Webmaster';
        $replacements[1] = 'at';
        $replacements[2] = 'Phinedo';
        $replacements[3] = 'webmaster@phinedo.com';

        return $this->render('PhinedoOutilsBundle::afficher_raw.html.twig', array('mail' => preg_replace($patterns, $replacements, $mail->getContent())));
    }
}