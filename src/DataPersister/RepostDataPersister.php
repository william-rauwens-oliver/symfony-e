<?php

namespace App\DataPersister;

use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Repost;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RepostDataPersister implements ProcessorInterface
{
    private $em;
    private $security;

    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof Repost) {
            $user = $this->security->getUser();
            if ($user && $data->getUser() === null) {
                $data->setUser($user);
            }
            if (!$data->getCreatedAt()) {
                $data->setCreatedAt(new \DateTimeImmutable());
            }
            // Empêche le doublon
            $existing = $this->em->getRepository(Repost::class)->findOneBy([
                'user' => $data->getUser(),
                'publication' => $data->getPublication(),
            ]);
            if ($existing && ($operation->getName() === 'post' || $operation->getName() === 'put')) {
                throw new BadRequestHttpException('Vous avez déjà retweeté cette publication.');
            }
            $this->em->persist($data);
            $this->em->flush();
        }
        return $data;
    }
} 