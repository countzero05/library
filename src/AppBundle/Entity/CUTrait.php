<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class CUEntity
 * @package AppBundle\Entity
 *
 * @ORM\MappedSuperclass()
 * @ORM\HasLifecycleCallbacks()
 */
trait CUTrait
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false, options={"default" : "now()"})
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false, options={"default" : "now()"})
     */
    private $updated;

    /**
     * @return \DateTime
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    /**
     * @ORM\PrePersist()
     *
     * @return $this
     */
    public function setCreated()
    {
        $this->created = $this->updated = new \DateTime();
        $this->updated = $this->updated = new \DateTime();
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated(): \DateTime
    {
        return $this->updated;
    }

    /**
     * @ORM\PreUpdate()
     *
     * @return $this
     */
    public function setUpdated()
    {
        $this->updated = new \DateTime();
        return $this;
    }

}