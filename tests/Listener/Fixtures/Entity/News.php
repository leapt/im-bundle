<?php

declare(strict_types=1);

namespace Leapt\ImBundle\Tests\Listener\Fixtures\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Leapt\ImBundle\Doctrine\Mapping as LeaptIm;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity]
class News
{
    #[ORM\Id, ORM\Column(type: Types::INTEGER), ORM\GeneratedValue('AUTO')]
    private int $id;

    #[LeaptIm\Mogrify(params: ['thumbnail' => '100x100>'])]
    private ?File $image;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getImage(): ?File
    {
        return $this->image;
    }

    public function setImage(?File $image): void
    {
        $this->image = $image;
    }
}
