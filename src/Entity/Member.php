<?php
/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 03.03.18
 * Time: 19:47
 *
 * Author: Dmitry Malakhov (abr_mail@mail.ru)
 * Prohibited for commercial use without the prior written consent of author
 *
 * Автор: Дмитрий Малахов (abr_mail@mail.ru)
 * Запрещено использование в коммерческих целях без письменного разрешения автора
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MemberRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Member
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    // add your own fields
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $password;

    /**
     * @ORM\Column(type="jsonb")
     */
    private $system_field;

    /**
     * @ORM\ManyToOne(targetEntity="MemberType")
     * @ORM\JoinColumn(nullable=false)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $uid;

    /**
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private $created_at;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param integer $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @param string $password
     */
    public function setPassword($password): void
    {
        $this->password = sha1($password);
    }

    /**
     * @return array
     */
    public function getSystemField()
    {
        return $this->system_field;
    }

    /**
     * @param array $system_field
     */
    public function setSystemField($system_field): void
    {
        $this->system_field = $system_field;
    }

    /**
     * @return MemberType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param MemberType $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param string $uid
     */
    public function setUid($uid): void
    {
        $this->uid = $uid;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param \DateTime $created_at
     */
    public function setCreatedAt($created_at): void
    {
        $this->created_at = $created_at;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist() {
        $this->created_at = new \DateTime("now");
    }

}
