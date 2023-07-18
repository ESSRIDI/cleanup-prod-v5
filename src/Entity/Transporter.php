<?php

namespace App\Entity;

use App\Repository\TransporterRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransporterRepository::class)]
class Transporter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $companyName = null;

    #[ORM\Column(length: 255)]
    private ?string $aboutDelivery = null;

    #[ORM\Column]
    private ?float $price = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): self
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getAboutDelivery(): ?string
    {
        return $this->aboutDelivery;
    }

    public function setAboutDelivery(string $aboutDelivery): self
    {
        $this->aboutDelivery = $aboutDelivery;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function __toString(){
        return '[-strong]'.$this->companyName.'[-/strong]'.'[-br]'.$this->aboutDelivery.'[-br]'.'Frais de livraison : '.$this->price."€";
    }
}
