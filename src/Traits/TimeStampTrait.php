<?php 

namespace App\Traits;
use Doctrine\ORM\Mapping as ORM;
trait TimeStampTrait{
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
    #[ORM\PrePersist]
    public function onPrePersist()
    {
       $this->createdAt=new \DateTimeImmutable();
       $this->updatedAt=new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate()
    {
       $this->updatedAt=new \DateTimeImmutable();
    }
}