<?php

namespace App\Entity;

use App\Repository\VolumeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Collection;
use phpDocumentor\Reflection\Types\Nullable;

#[ORM\Entity(repositoryClass: VolumeRepository::class)]
class Volume
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:'integer')]
    private  $id ;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: '0')]
    private $volume ;

  
    /**
     * Plusieurs quantites peuvent etre associée a plusieurs produits
     */
    #[ORM\ManyToMany(targetEntity: Product::class,mappedBy: 'volumes' )]
    private $products ;

    #[ORM\OneToMany(mappedBy: 'volume', targetEntity: Price::class, cascade:['persist','remove'])]
    private $price;



    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->price = new ArrayCollection();
       
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVolume(): ?string
    {
        return $this->volume;
    }

    public function setVolume(string $volume): self
    {
        
        $this->volume = $volume;

        return $this;
    }

   
  

  

    public function getProducts()
    {
        return $this->products;
    }

    public function addProduct(Product $product):void
    {
        if (!$this->products->contains($product)) {
         
            $this->products[] = $product;
        }

     
    }
    // public function getProduct(): Product
    // {
    //     return $this->products;
    // }
public function removeProduct(Product $product)
{

    $this->products->removeElement($product);
    // if ($this->products->contains($product)) {
    //     if($product->getVolumes()=== $this){
    //         $product->setVolumes(null);
    //     }
    
    // $this->products->removeElement($product);

    // $product->removeVolume($this);
    }

public function setProduct(?Product $product): self
    {
        $this->products = $product;

        return $this;
    }


  

    public function addPrice(Price $price): self
    {
        if (!$this->price->contains($price)) {
            $this->price->add($price);
            $price->setVolume($this);
        }

        return $this;
    }

    public function removePrice(Price $price): self
    {
        if ($this->price->removeElement($price)) {
            // set the owning side to null (unless already changed)
            if ($price->getVolume() === $this) {
                $price->setVolume(null);
            }
        }

        return $this;
    }


	

	
	

	
	public function getPrice() {
		return $this->price;
	}
	
    public function __toString():string{
        return $this->volume;
    }
}
