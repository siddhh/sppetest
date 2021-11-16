<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Shapecode\Bundle\CronBundle\Annotation\CronJob;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @CronJob("*\/5 * * * *")
 * Sera exécutée toutes les 5 minutes.
 */
class ProcessusUpdateCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'sppe:processus:update';

    /** @var EntityManagerInterface  */
    private $em;

    /**
     * Constructeur de la commande.
     * Permet notamment de récupérer dépendances
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    /**
     * Configure la commande
     */
    protected function configure()
    {
        $this
            ->setDescription('Recherche les fichiers processus modifiés dernierement afin de mettre à jour les plans d\'exploitation afférents.')
        ;
    }

    /**
     * Défini l'éxécution de la commande
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->caution('TODO: cette commande ne fait rien (à compléter) !');
        return Command::SUCCESS; 
    }
}
