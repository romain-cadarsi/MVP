<?php

namespace App\Entity;

use App\Repository\CampagneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CampagneRepository::class)
 */
class Campagne
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $titre;

    /**
     * @ORM\ManyToOne(targetEntity=Image::class)
     */
    private $logo;

    /**
     * @ORM\Column(type="string", length=10000, nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity=Image::class)
     */
    private $imagesProduit;

    /**
     * @ORM\Column(type="float")
     */
    private $prixPromotion;

    /**
     * @ORM\Column(type="float")
     */
    private $valeurProduit;

    /**
     * @ORM\Column(type="integer")
     */
    private $nombreParticipants;

    /**
     * @ORM\Column(type="integer")
     */
    private $dureeCampagne;

    /**
     * @ORM\Column(type="date")
     */
    private $debutCampagne;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $telephone;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ville;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $moyen;

    /**
     * @ORM\Column(type="datetime")
     */
    private $datetimeRetrait;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nomVendeur;

    public function __construct()
    {
        $this->imagesProduit = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getLogo(): ?Image
    {
        return $this->logo;
    }

    public function setLogo(?Image $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|Image[]
     */
    public function getImagesProduit(): Collection
    {
        return $this->imagesProduit;
    }

    public function addImagesProduit(Image $imagesProduit): self
    {
        if (!$this->imagesProduit->contains($imagesProduit)) {
            $this->imagesProduit[] = $imagesProduit;
        }

        return $this;
    }

    public function removeImagesProduit(Image $imagesProduit): self
    {
        $this->imagesProduit->removeElement($imagesProduit);

        return $this;
    }

    public function getPrixPromotion(): ?float
    {
        return $this->prixPromotion;
    }

    public function setPrixPromotion(float $prixPromotion): self
    {
        $this->prixPromotion = $prixPromotion;

        return $this;
    }

    public function getValeurProduit(): ?float
    {
        return $this->valeurProduit;
    }

    public function setValeurProduit(float $valeurProduit): self
    {
        $this->valeurProduit = $valeurProduit;

        return $this;
    }

    public function getNombreParticipants(): ?int
    {
        return $this->nombreParticipants;
    }

    public function setNombreParticipants(int $nombreParticipants): self
    {
        $this->nombreParticipants = $nombreParticipants;

        return $this;
    }

    public function getDureeCampagne(): ?int
    {
        return $this->dureeCampagne;
    }

    public function setDureeCampagne(int $dureeCampagne): self
    {
        $this->dureeCampagne = $dureeCampagne;

        return $this;
    }

    public function getDebutCampagne(): ?\DateTimeInterface
    {
        return $this->debutCampagne;
    }

    public function setDebutCampagne(\DateTimeInterface $debutCampagne): self
    {
        $this->debutCampagne = $debutCampagne;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getMoyen(): ?string
    {
        return $this->moyen;
    }

    public function setMoyen(string $moyen): self
    {
        $this->moyen = $moyen;

        return $this;
    }

    public function getDatetimeRetrait(): ?\DateTimeInterface
    {
        return $this->datetimeRetrait;
    }

    public function setDatetimeRetrait(\DateTimeInterface $datetimeRetrait): self
    {
        $this->datetimeRetrait = $datetimeRetrait;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getNomVendeur(): ?string
    {
        return $this->nomVendeur;
    }

    public function setNomVendeur(string $nomVendeur): self
    {
        $this->nomVendeur = $nomVendeur;

        return $this;
    }
}
