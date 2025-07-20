<?php

namespace App\Command;

use App\Entity\Publication;
use App\Entity\Commentaire;
use App\Entity\Like;
use App\Entity\CommentLike;
use App\Entity\Repost;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:delete-all-publications-comments',
    description: 'Supprime toutes les publications, commentaires, likes et reposts de la base de données',
)]
class DeleteAllPublicationsCommentsCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Suppression de toutes les publications, commentaires, likes et reposts');

        // Supprimer les likes de commentaires
        $commentLikes = $this->entityManager->getRepository(CommentLike::class)->findAll();
        $io->note(sprintf('Suppression de %d likes de commentaires', count($commentLikes)));
        foreach ($commentLikes as $cl) {
            $this->entityManager->remove($cl);
        }

        // Supprimer les likes de publications
        $likes = $this->entityManager->getRepository(Like::class)->findAll();
        $io->note(sprintf('Suppression de %d likes de publications', count($likes)));
        foreach ($likes as $like) {
            $this->entityManager->remove($like);
        }

        // Supprimer les reposts
        $reposts = $this->entityManager->getRepository(Repost::class)->findAll();
        $io->note(sprintf('Suppression de %d reposts', count($reposts)));
        foreach ($reposts as $repost) {
            $this->entityManager->remove($repost);
        }

        // Supprimer les commentaires
        $commentaires = $this->entityManager->getRepository(Commentaire::class)->findAll();
        $io->note(sprintf('Suppression de %d commentaires', count($commentaires)));
        foreach ($commentaires as $commentaire) {
            $this->entityManager->remove($commentaire);
        }

        // Supprimer les publications
        $publications = $this->entityManager->getRepository(Publication::class)->findAll();
        $io->note(sprintf('Suppression de %d publications', count($publications)));
        foreach ($publications as $publication) {
            $this->entityManager->remove($publication);
        }

        $this->entityManager->flush();

        $io->success('Toutes les publications, commentaires, likes et reposts ont été supprimés.');
        return Command::SUCCESS;
    }
} 