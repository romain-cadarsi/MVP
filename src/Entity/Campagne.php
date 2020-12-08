<?php

namespace App\Entity;

use App\Repository\CampagneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\This;
use Symfony\Component\Validator\Constraints\Date;

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

    /**
     * @ORM\OneToMany(targetEntity=Participation::class, mappedBy="Campagne", orphanRemoval=true)
     */
    private $participations;

    /**
     * @ORM\ManyToOne(targetEntity=Commercant::class, inversedBy="campagnes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $commercant;

    /**
     * @ORM\OneToMany(targetEntity=ImagesAdditionnelles::class, mappedBy="campagne", orphanRemoval=true)
     */
    private $imagesAdditionnelles;

    /**
     * @ORM\OneToMany(targetEntity=Commentary::class, mappedBy="campagne")
     */
    private $commentaries;


    public function __construct()
    {
        $this->participations = new ArrayCollection();
        $this->imagesAdditionnelles = new ArrayCollection();
        $this->commentaries = new ArrayCollection();
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

    public function getFinCampagne(){
        return date_add($this->debutCampagne,date_interval_create_from_date_string($this->getDureeCampagne() . ' days'));
    }

    public function isValid(){
        return $this->getFinCampagne() > new \DateTime();

    }
    /**
     * @return Collection|Participation[]
     */
    public function getParticipations(): Collection
    {
        return $this->participations;
    }

    public function addParticipation(Participation $participation): self
    {
        if (!$this->participations->contains($participation)) {
            $this->participations[] = $participation;
            $participation->setCampagne($this);
        }

        return $this;
    }

    public function removeParticipation(Participation $participation): self
    {
        if ($this->participations->removeElement($participation)) {
            // set the owning side to null (unless already changed)
            if ($participation->getCampagne() === $this) {
                $participation->setCampagne(null);
            }
        }

        return $this;
    }

    public function getCommercant(): ?Commercant
    {
        return $this->commercant;
    }

    public function setCommercant(?Commercant $commercant): self
    {
        $this->commercant = $commercant;

        return $this;
    }

    public function getPourcentageParticipation()
    {
        return $this->getNombreParticipations() / $this->getNombreParticipants() * 100;
    }

    public function getPourcentageParticipation80()
    {
        return ($this->getNombreParticipations() / $this->getNombreParticipants() * 100) * 0.8;
    }

    public function getNombreParticipations(){
        $totalCount = 0 ;
        foreach ($this->getParticipations() as $participation){
            $totalCount+= $participation->getQuantity();
        }
        return $totalCount;
    }

    public function getDiscount()
    {
        $v = $this->getValeurProduit();
        $p = $this->getPrixPromotion();
        return intval((($v-$p) / $v )* 100);
    }

    /**
     * @return Collection|ImagesAdditionnelles[]
     */
    public function getImagesAdditionnelles(): Collection
    {
        return $this->imagesAdditionnelles;
    }

    public function addImagesAdditionnelle(ImagesAdditionnelles $imagesAdditionnelle): self
    {
        if (!$this->imagesAdditionnelles->contains($imagesAdditionnelle)) {
            $this->imagesAdditionnelles[] = $imagesAdditionnelle;
            $imagesAdditionnelle->setCampagne($this);
        }

        return $this;
    }

    public function removeImagesAdditionnelle(ImagesAdditionnelles $imagesAdditionnelle): self
    {
        if ($this->imagesAdditionnelles->removeElement($imagesAdditionnelle)) {
            // set the owning side to null (unless already changed)
            if ($imagesAdditionnelle->getCampagne() === $this) {
                $imagesAdditionnelle->setCampagne(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Commentary[]
     */
    public function getCommentaries(): Collection
    {
        return $this->commentaries;
    }

    public function addCommentary(Commentary $commentary): self
    {
        if (!$this->commentaries->contains($commentary)) {
            $this->commentaries[] = $commentary;
            $commentary->setCampagne($this);
        }

        return $this;
    }

    public function removeCommentary(Commentary $commentary): self
    {
        if ($this->commentaries->removeElement($commentary)) {
            // set the owning side to null (unless already changed)
            if ($commentary->getCampagne() === $this) {
                $commentary->setCampagne(null);
            }
        }

        return $this;
    }

}
