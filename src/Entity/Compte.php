<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CompteRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type_cpte", type="string")
 * @ORM\DiscriminatorMap({"C" = "Compte", "CC" = "CompteCourant", "CE" = "CompteEpargne"})
 */
class Compte
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string")
     */
    private $codeCompte;

    /**
     * @ORM\Column(type="date")
     */
    private $dateCreation;

    /**
     * @ORM\Column(type="float")
     */
    private $solde;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Client", inversedBy="comptes")
     * @ORM\JoinColumn(name="code_cli",referencedColumnName="code")
     */
    private $client;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Operation", mappedBy="compte")
     */
    private $operations;

    public function __construct()
    {
        $this->operations = new ArrayCollection();
    }

    public function getcodeCompte(): ?string
    {
        return $this->codeCompte;
    }

    public function setcodeCompte(string $codeCompte): ?self
    {
        $this->codeCompte = $codeCompte;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getSolde(): ?float
    {
        return $this->solde;
    }

    public function setSolde(float $solde): self
    {
        $this->solde = $solde;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Collection|Operation[]
     */
    public function getOperations(): Collection
    {
        return $this->operations;
    }

    public function addOperation(Operation $operation): self
    {
        if (!$this->operations->contains($operation)) {
            $this->operations[] = $operation;
            $operation->setCompte($this);
        }

        return $this;
    }

    public function removeOperation(Operation $operation): self
    {
        if ($this->operations->contains($operation)) {
            $this->operations->removeElement($operation);
            // set the owning side to null (unless already changed)
            if ($operation->getCompte() === $this) {
                $operation->setCompte(null);
            }
        }

        return $this;
    }
}
