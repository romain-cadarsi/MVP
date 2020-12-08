<?php

namespace App\Entity;

use App\Repository\CommentaryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\This;

/**
 * @ORM\Entity(repositoryClass=CommentaryRepository::class)
 */
class Commentary
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=5000)
     */
    private $commentary;

    /**
     * @ORM\ManyToOne(targetEntity=Commentary::class, inversedBy="commentaries")
     */
    private $linkedCommentary;

    /**
     * @ORM\OneToMany(targetEntity=Commentary::class, mappedBy="linkedCommentary")
     */
    private $commentaries;

    /**
     * @ORM\ManyToOne(targetEntity=Participant::class)
     */
    private $participant;

    /**
     * @ORM\ManyToOne(targetEntity=Commercant::class)
     */
    private $commercant;

    /**
     * @ORM\Column(type="datetime")
     */
    private $datetime;

    /**
     * @ORM\ManyToOne(targetEntity=Campagne::class, inversedBy="commentaries")
     */
    private $campagne;

    public function __construct()
    {
        $this->commentaries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getCommentary(): ?string
    {
        return $this->commentary;
    }

    public function setCommentary(string $commentary): self
    {
        $this->commentary = $commentary;

        return $this;
    }

    public function getLinkedCommentary(): ?self
    {
        return $this->linkedCommentary;
    }

    public function setLinkedCommentary(?self $linkedCommentary): self
    {
        $this->linkedCommentary = $linkedCommentary;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getCommentaries(): Collection
    {
        return $this->commentaries;
    }

    public function addCommentary(self $commentary): self
    {
        if (!$this->commentaries->contains($commentary)) {
            $this->commentaries[] = $commentary;
            $commentary->setLinkedCommentary($this);
        }

        return $this;
    }

    public function removeCommentary(self $commentary): self
    {
        if ($this->commentaries->removeElement($commentary)) {
            // set the owning side to null (unless already changed)
            if ($commentary->getLinkedCommentary() === $this) {
                $commentary->setLinkedCommentary(null);
            }
        }

        return $this;
    }

    public function getParticipant(): ?Participant
    {
        return $this->participant;
    }

    public function setParticipant(?Participant $participant): self
    {
        $this->participant = $participant;

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

    public function getUser(){
        if(!$this->getParticipant()){
            return $this->getCommercant();
        }
        return $this->getParticipant();
    }

    public function getDatetime(): ?\DateTimeInterface
    {
        return $this->datetime;
    }

    public function setDatetime(\DateTimeInterface $datetime): self
    {
        $this->datetime = $datetime;

        return $this;
    }

    public function getCampagne(): ?Campagne
    {
        return $this->campagne;
    }

    public function setCampagne(?Campagne $campagne): self
    {
        $this->campagne = $campagne;

        return $this;
    }

}
