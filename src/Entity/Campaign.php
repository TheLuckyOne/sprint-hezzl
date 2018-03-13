<?php
/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 03.03.18
 * Time: 22:34
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
 * @ORM\Entity(repositoryClass="App\Repository\CampaignRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Campaign
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    // add your own fields

    /**
     * @ORM\ManyToOne(targetEntity="Member")
     * @ORM\JoinColumn(nullable=false)
     */
    private $member;

    /**
     * @ORM\ManyToOne(targetEntity="CampaignType")
     * @ORM\JoinColumn(nullable=false)
     */
    private $campaign_type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $login_type;

    /**
     * @ORM\Column(type="text")
     */
    private $custom_setting;

    /**
     * @ORM\Column(type="text")
     */
    private $message_end;

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
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * @param Member $member
     */
    public function setMember($member): void
    {
        $this->member = $member;
    }

    /**
     * @return CampaignType
     */
    public function getCampaignType()
    {
        return $this->campaign_type;
    }

    /**
     * @param CampaignType $campaign_type
     */
    public function setCampaignType($campaign_type): void
    {
        $this->campaign_type = $campaign_type;
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
     * @return integer
     */
    public function getLoginType()
    {
        return $this->login_type;
    }

    /**
     * @param string $login_type
     */
    public function setLoginType($login_type): void
    {
        $this->login_type = $login_type;
    }

    /**
     * @return string
     */
    public function getCustomSetting()
    {
        return $this->custom_setting;
    }

    /**
     * @param string $custom_setting
     */
    public function setCustomSetting($custom_setting): void
    {
        $this->custom_setting = $custom_setting;
    }

    /**
     * @return string
     */
    public function getMessageEnd()
    {
        return $this->message_end;
    }

    /**
     * @param string $message_end
     */
    public function setMessageEnd($message_end): void
    {
        $this->message_end = $message_end;
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
