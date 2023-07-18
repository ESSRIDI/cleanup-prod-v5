<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert ;
use Doctrine\ORM\Mapping as ORM;

use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
/**
 * Product
 */
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[Assert\NotBlank()]
    #[Assert\Length(min:5, max: 50)]
    #[ORM\Column(type: 'string', length: 50)]
    private $name;

    #[ORM\Column(type: Types::TEXT)]
    private $description;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $barcode;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $image;
    
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $scent;

    #[ORM\Column(type: 'string', length: 255)]
    private $quality;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'products',  cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private $category;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $createdAt;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $updatedAt;

    #[ORM\Column(type: 'boolean', nullable:true)]
    private $isAvailable;


    #[ORM\Column(type: 'string')]
    private $unity;

    /**
     * Plusieurs produits peuvent etre associée a plusieurs quantités
     */
    #[ORM\ManyToMany(targetEntity: Volume::class, inversedBy: 'products', cascade: ['persist', 'remove'],orphanRemoval:true)]
    #[ORM\JoinTable(name:"products_volumes")]
    private $volumes;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Price::class,orphanRemoval: true)]
    private Collection $prices;

    public function __construct()
    {
       
        $this->volumes = new ArrayCollection();
        $this->prices = new ArrayCollection();
    }
    public function productVolume(Volume $volume): void
    {
        $volume->addProduct($this);
        $this->volumes[] = $volume;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function isIsAvailable(): ?bool
    {
        return $this->isAvailable;
    }

    public function setIsAvailable(bool $isAvailable): self
    {
        $this->isAvailable = $isAvailable;

        return $this;
    }

 



    /**
     * @return Collection<int, Volume>
     */
    public function getVolumes(): Collection
    {
        return $this->volumes;
    }

    public function addVolume(Volume $volume):self
    {
        if (!$this->volumes->contains($volume)) {
            $this->volumes[] = $volume;
           
            $volume->addProduct($this);
        
        }
        return $this;

       
    }

    public function removeVolume(Volume $volume): void
    {
      

            if($this->volumes->removeElement($volume)){
            if($volume->getProducts() === $this){
                $volume->setProduct(null);
            }
          
        }
    }
    // public function setVolumes(?Volume $volume): self
    // {

    //     $this->volumes = $volume;

    //     return $this;
    // }
   
    public function __toString()
    {
        return $this->name;
    }


    /**
     * @return mixed
     */
    public function getUnity()
    {
        return $this->unity;
    }

    /**
     * @param mixed $unity 
     * @return self
     */
    public function setUnity($unity): self
    {
        $this->unity = $unity;
        return $this;
    }

    /**
     * @return Collection<int, Price>
     */
    public function getPrices(): Collection
    {
        return $this->prices;
    }

    public function addPrice(Price $price): self
    {
        if (!$this->prices->contains($price)) {
            $this->prices->add($price);
            $price->setProduct($this);
        }

        return $this;
    }

    public function removePrice(Price $price): self
    {
        if ($this->prices->removeElement($price)) {
            // set the owning side to null (unless already changed)
            if ($price->getProduct() === $this) {
                $price->setProduct(null);
            }
        }

        return $this;
    }

	

	/**
	 * @return mixed
	 */
	public function getScent() {
		return $this->scent;
	}
	
	/**
	 * @param mixed $scent 
	 * @return self
	 */
	public function setScent($scent): self {
		$this->scent = $scent;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getQuality() {
		return $this->quality;
	}
	
	/**
	 * @param mixed $quality 
	 * @return self
	 */
	public function setQuality($quality): self {
		$this->quality = $quality;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBarcode() {
		return $this->barcode;
	}
	
	/**
	 * @param mixed $barcode 
	 * @return self
	 */
	public function setBarcode($barcode): self {
		$this->barcode = $barcode;
		return $this;
	}
}