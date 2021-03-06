<?php
/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 03.03.18
 * Time: 22:33
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
 * @ORM\Entity(repositoryClass="App\Repository\PlayersRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Player
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    // add your own fields

    /**
     * @ORM\ManyToOne(targetEntity="Campaign")
     * @ORM\JoinColumn(nullable=false)
     */
    private $campaign;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $login;

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
     * @ORM\Column(type="boolean")
     */
    private $sex;

    /**
     * @ORM\Column(type="date")
     */
    private $birthday;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private $score;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private $coins;

    /**
     * @ORM\Column(type="json_array")
     */
    private $system;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $last_day;

    /**
     * @ORM\Column(type="string", length=32)
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
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @param string $campaign
     */
    public function setCampaign($campaign): void
    {
        $this->campaign = $campaign;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param string $login
     */
    public function setLogin($login): void
    {
        $this->login = $login;
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
     * @return boolean
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * @param boolean $sex
     */
    public function setSex($sex): void
    {
        $this->sex = $sex;
    }

    /**
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param \DateTime $birthday
     */
    public function setBirthday($birthday): void
    {
        $this->birthday = $birthday;
    }

    /**
     * @return integer
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * @param integer $score
     */
    public function setScore($score): void
    {
        $this->score = $score;
    }

    /**
     * @return integer
     */
    public function getCoins()
    {
        return $this->coins;
    }

    /**
     * @param integer $coins
     */
    public function setCoins($coins): void
    {
        $this->coins = $coins;
    }

    /**
     * @return array
     */
    public function getSystem()
    {
        return $this->system;
    }

    /**
     * @param array $system
     */
    public function setSystem($system): void
    {
        $this->system = $system;
    }

    /**
     * @return \DateTime
     */
    public function getLastDay()
    {
        return $this->last_day;
    }

    /**
     * @param \DateTime $last_day
     */
    public function setLastDay($last_day): void
    {
        $this->last_day = $last_day;
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
