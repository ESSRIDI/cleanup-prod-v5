<?php

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $productImage = null;

    #[ORM\Column(length: 255)]
    private ?string $productName = null;

    #[ORM\Column(length: 255)]
    private ?string $productVolume = null;

    #[ORM\Column]
    private ?float $productPrice = null;

    #[ORM\Column]
    private ?float $productPriceWithTva = null;

    #[ORM\Column]
    private ?int $productQuantity = null;

    #[ORM\Column]
    private ?float $productTotalPrice = null;

    #[ORM\ManyToOne(inversedBy: 'orderItem')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $orderClient = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductImage(): ?string
    {
        return $this->productImage;
    }

    public function setProductImage(string $productImage): self
    {
        $this->productImage = $productImage;

        return $this;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function setProductName(string $productName): self
    {
        $this->productName = $productName;

        return $this;
    }

    public function getProductVolume(): ?string
    {
        return $this->productVolume;
    }

    public function setProductVolume(string $productVolume): self
    {
        $this->productVolume = $productVolume;

        return $this;
    }

    public function getProductPrice(): ?float
    {
        return $this->productPrice;
    }

    public function setProductPrice(float $productPrice): self
    {
        $this->productPrice = $productPrice;

        return $this;
    }

    public function getProductQuantity(): ?int
    {
        return $this->productQuantity;
    }

    public function setProductQuantity(int $productQuantity): self
    {
        $this->productQuantity = $productQuantity;

        return $this;
    }

    public function getProductTotalPrice(): ?float
    {
        return $this->productTotalPrice;
    }

    public function setProductTotalPrice(float $productTotalPrice): self
    {
        $this->productTotalPrice = $productTotalPrice;

        return $this;
    }

    public function getOrderClient(): ?Order
    {
        return $this->orderClient;
    }

    public function setOrderClient(?Order $orderClient): self
    {
        $this->orderClient = $orderClient;

        return $this;
    }

	/**
	 * @return 
	 */
	public function getProductPriceWithTva(): ?float {
		return $this->productPriceWithTva;
	}
	
	/**
	 * @param  $productPriceWithTva 
	 * @return self
	 */
	public function setProductPriceWithTva(?float $productPriceWithTva): self {
		$this->productPriceWithTva = $productPriceWithTva;
		return $this;
	}
}
