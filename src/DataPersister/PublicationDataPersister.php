<?php
namespace App\DataPersister;

use ApiPlatform\Metadata\Operation;
use App\Entity\Publication;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\SecurityBundle\Security;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\PublicationInput;

class PublicationDataPersister implements ProcessorInterface
{
    private $entityManager;
    private $security;
    private $uploadDir;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->uploadDir = __DIR__ . '/../../public/uploads/images';
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] Entrée process, type: " . (is_object($data) ? get_class($data) : gettype($data)) . "\n", FILE_APPEND);
        if ($data instanceof PublicationInput) {
            file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] Mapping PublicationInput vers Publication\n", FILE_APPEND);
            $publication = new Publication();
            $publication->setTexte($data->texte);
            if ($data->image) {
                $file = $data->image;
                $filename = uniqid('img_') . '.' . $file->guessExtension();
                $file->move($this->uploadDir, $filename);
                $publication->setImage('/uploads/images/' . $filename);
            }
            if ($data->video) {
                $file = $data->video;
                $filename = uniqid('vid_') . '.' . $file->guessExtension();
                $file->move($this->uploadDir, $filename);
                $publication->setVideo('/uploads/images/' . $filename);
            }
            if ($this->security->getUser()) {
                $publication->setUser($this->security->getUser());
            }
            $publication->setCreatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($publication);
            $this->entityManager->flush();
            file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] Publication persistée avec ID: " . $publication->getId() . ", texte: " . $publication->getTexte() . "\n", FILE_APPEND);
            return $publication;
        }
        if ($data instanceof Publication) {
            file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] Update Publication existante\n", FILE_APPEND);
            // fallback pour update
            if ($this->security->getUser() && !$data->getUser()) {
                $data->setUser($this->security->getUser());
            }
            if (!$data->getCreatedAt()) {
                $data->setCreatedAt(new \DateTimeImmutable());
            }
            $this->entityManager->persist($data);
            $this->entityManager->flush();
            file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] Publication mise à jour ID: " . $data->getId() . "\n", FILE_APPEND);
            return $data;
        }
        file_put_contents(__DIR__ . '/../../logfile', "[PublicationDataPersister] Donnée non prise en charge\n", FILE_APPEND);
        return $data;
    }
} 