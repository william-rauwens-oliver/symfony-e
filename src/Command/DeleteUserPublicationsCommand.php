<?php

namespace App\Command;

use App\Entity\Publication;
use App\Repository\PublicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:delete-user-publications',
    description: 'Supprime toutes les publications d\'un utilisateur',
)]
class DeleteUserPublicationsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PublicationRepository $publicationRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('userId', InputArgument::REQUIRED, 'ID de l\'utilisateur')
            ->setHelp('Cette commande supprime toutes les publications d\'un utilisateur spécifique');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $userId = $input->getArgument('userId');

        $io->title('Suppression des publications de l\'utilisateur ' . $userId);

        // Récupérer toutes les publications de l'utilisateur
        $publications = $this->publicationRepository->findBy(['user' => $userId]);

        if (empty($publications)) {
            $io->warning('Aucune publication trouvée pour l\'utilisateur ' . $userId);
            return Command::SUCCESS;
        }

        $io->note(sprintf('Trouvé %d publication(s) à supprimer', count($publications)));

        // Supprimer chaque publication
        foreach ($publications as $publication) {
            $this->entityManager->remove($publication);
            $io->text('Suppression de la publication ID: ' . $publication->getId());
        }

        // Persister les changements
        $this->entityManager->flush();

        $io->success(sprintf('%d publication(s) supprimée(s) avec succès', count($publications)));

        return Command::SUCCESS;
    }
}
