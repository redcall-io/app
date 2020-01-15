<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StructureRepository")
 */
class Structure
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastVolunteerImport;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Volunteer", mappedBy="organization", orphanRemoval=true)
     */
    private $volunteers;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Structure")
     */
    private $parentStructure;

    public function __construct()
    {
        $this->volunteers = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getCode(): ?int
    {
        return $this->code;
    }

    /**
     * @param int $code
     *
     * @return Structure
     */
    public function setCode(int $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return Structure
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Structure
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getLastVolunteerImport(): ?\DateTimeInterface
    {
        return $this->lastVolunteerImport;
    }

    /**
     * @param \DateTimeInterface|null $lastVolunteerImport
     *
     * @return Structure
     */
    public function setLastVolunteerImport(?\DateTimeInterface $lastVolunteerImport): self
    {
        $this->lastVolunteerImport = $lastVolunteerImport;

        return $this;
    }

    /**
     * @return Collection|Volunteer[]
     */
    public function getVolunteers(): Collection
    {
        return $this->volunteers;
    }

    /**
     * @param Volunteer $volunteer
     *
     * @return Structure
     */
    public function addVolunteer(Volunteer $volunteer): self
    {
        if (!$this->volunteers->contains($volunteer)) {
            $this->volunteers[] = $volunteer;
            $volunteer->setStructure($this);
        }

        return $this;
    }

    /**
     * @param Volunteer $volunteer
     *
     * @return Structure
     */
    public function removeVolunteer(Volunteer $volunteer): self
    {
        if ($this->volunteers->contains($volunteer)) {
            $this->volunteers->removeElement($volunteer);
            // set the owning side to null (unless already changed)
            if ($volunteer->getStructure() === $this) {
                $volunteer->setStructure(null);
            }
        }

        return $this;
    }

    /**
     * @return Structure|null
     */
    public function getParentStructure(): ?self
    {
        return $this->parentStructure;
    }

    /**
     * @param self|null $parentStructure
     *
     * @return Structure
     */
    public function setParentStructure(?self $parentStructure): self
    {
        $this->parentStructure = $parentStructure;

        return $this;
    }
}