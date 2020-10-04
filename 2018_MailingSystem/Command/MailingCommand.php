<?php
namespace Phinedo\OutilsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MailingCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('phinedo:envoiMailing')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $listeActifs = $this->getContainer()->get('doctrine')->getRepository("PhinedoUserBundle:User")->listeActifs();
        
        $mails = $this->getContainer()->get('doctrine')->getRepository('PhinedoOutilsBundle:Mailing')->mailToSend();

        //$listeUsersActifs = $this->getContainer()->get('doctrine')->getRepository('PhinedoUserBundle:User')->findByActive(true);

        $em = $this->getContainer()->get('doctrine')->getManager(); 

        foreach ($mails as $key => $mail) {
            $patterns = array();
            $patterns[0] = '/{{PRENOM}}/';
            $patterns[1] = '/{{NOM}}/';
            $patterns[2] = '/{{SURNOM}}/';
            $patterns[3] = '/{{MAIL}}/';

            if ($mail->getMail() != null){
                $replacements = array();
                $replacements[0] = 'Webmaster';
                $replacements[1] = 'at';
                $replacements[2] = 'Phinedo';
                $replacements[3] = $mail->getMail();

                $message = \Swift_Message::newInstance()
                ->setSubject($mail->getObjet())
                ->setFrom(array('mailing@phinedo.com' => 'Phinedo'))
                ->setTo($mail->getMail())
                ->setBody($this->getContainer()->get('templating')->render('PhinedoOutilsBundle::afficher_raw.html.twig', array('mail' => preg_replace($patterns, $replacements, $mail->getContent()))), 'text/html')
                ;
                $this->getContainer()->get('mailer')->send($message);
            }
            

            if($mail->getTous()){
                $i=0;
                 foreach($listeActifs as $user){
                    $i++;
                    if ($user->getEmail() != null && \Swift_Validate::email($user->getEmail())) {
                        //On met en place les remplacements pour le contenu du mail
                        $replacements = array();
                        $replacements[0] = $user->getProfile()->getPrenom();
                        $replacements[1] = $user->getProfile()->getNom();
                        $replacements[2] = $user->getProfile()->getSurnom();
                        $replacements[3] = $user->getEmail();

                        $output->writeln("  ".$i." - ".$user->getEmail());
                        $message = \Swift_Message::newInstance()
                        ->setSubject($mail->getObjet())
                        ->setFrom(array('mailing@phinedo.com' => 'Phinedo'))
                        ->setTo($user->getEmail())
                        ->setBody($this->getContainer()->get('templating')->render('PhinedoOutilsBundle::afficher_raw.html.twig', array('mail' => preg_replace($patterns, $replacements, $mail->getContent()))), 'text/html')
                        ;
                       $this->getContainer()->get('swiftmailer.mailer.spool')->send($message);
                    }
                 }
            }

            $mail->setState(true);
            $em->persist($mail);
            $output->writeln(date("Y-m-d H:i:s").' - mail ajouté !');
        }

        $em->flush();
        
        $output->writeln(date("Y-m-d H:i:s").' - command called !');
    }
}