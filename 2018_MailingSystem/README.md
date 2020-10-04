This is the code for the mailing system of Phinedo. (Symfony2)

A first version was made by Lucas Fougeras and I improved in 2018 the mailing system modifying the Controller, MailingCommand and Twig templates.
This is based on SWIFT Mailing.

This modification improves the code with:
- Less Doctrine queries, that were making the system quite slow;
- A clear separation between TWIG templates for better code reading and patching a problem with templates.
- Adding new actions in the controller with new options for the Pole Site.
