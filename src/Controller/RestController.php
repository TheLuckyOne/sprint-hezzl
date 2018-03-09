<?php
/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 03.03.18
 * Time: 23:45
 *
 * Author: Dmitry Malakhov (abr_mail@mail.ru)
 * Prohibited for commercial use without the prior written consent of author
 *
 * Автор: Дмитрий Малахов (abr_mail@mail.ru)
 * Запрещено использование в коммерческих целях без письменного разрешения автора
 */

namespace App\Controller;


use App\Entity\Campaign;
use App\Entity\Member;
use App\Entity\Player;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RestController extends FOSRestController
{

    /**
     * @param Request $request
     */
    public function checkSum(Request $request) {
        //TODO
        /*$checksum = $request->get('checksum');
        if ($checksum === null) {
            throw new HttpException(400, 'Checksum is failed');
        }*/

        //throw new HttpException(400,'Checksum is failed');
    }

    /**
     * @param Request $request
     * @return Campaign
     */
    public function extractCampaign(Request $request) {
        $campaign_id = $request->get('campaign_id');
        if ($campaign_id === null) {
            throw new HttpException(400, 'Campaign id is required');
        }

        $campaign = $this->container->get('doctrine')->getRepository(Campaign::class)->find($campaign_id);
        if ($campaign === null) {
            throw new HttpException(400, 'Campaign not found');
        }

        return $campaign;
    }

    /**
     * @param string $uid
     * @return Player
     */
    public function getPlayerByUid($uid) {
        if ($uid === null) {
            throw new HttpException(400, 'Uid is required');
        }

        $playerIdFromRedis = $this->container->get('snc_redis.players_uids')->get($uid);
        if ($playerIdFromRedis !== null) {
            $player = $this->container->get('doctrine')->getRepository(Player::class)->find($playerIdFromRedis);
            if ($player !== null) {
                return $player;
            }
        }
        throw new HttpException(400, 'Uid is not valid');
    }

    /**
     * @param string $token
     * @return Member
     */
    public function getMemberByToken($token) {
        if ($token === null) {
            throw new HttpException(400, 'Token is required');
        }

        $memberIdFromRedis = $this->container->get('snc_redis.members_tokens')->get($token);
        if ($memberIdFromRedis !== null) {
            $member = $this->container->get('doctrine')->getRepository(Member::class)->find($memberIdFromRedis);
            if ($member !== null) {
                return $member;
            }
        }
        throw new HttpException(400, 'Token is not valid');
    }

    /**
     * @param Player $player
     * @param string $oldUid
     * @return string
     */
    public function generateAndStoreNewUid(Player $player, $oldUid = null) {
        try {
            $uid = md5(random_bytes(20));
            if ($oldUid !== null) {
                $this->container->get('snc_redis.players_uids')->del([$oldUid]);
            }
            $this->container->get('snc_redis.players_uids')->set($uid, $player->getId());
            return $uid;
        } catch (\Exception $e) {
            throw new HttpException(400, 'Cannot generate new uid');
        }
    }

    /**
     * @param Member $member
     * @param string $oldToken
     * @return string
     */
    public function generateAndStoreNewToken(Member $member, $oldToken = null) {
        try {
            $token = md5(random_bytes(20));
            if ($oldToken !== null) {
                $this->container->get('snc_redis.members_tokens')->del([$oldToken]);
            }
            $this->container->get('snc_redis.members_tokens')->set($token, $member->getId());
            return $token;
        } catch (\Exception $e) {
            throw new HttpException(400, 'Cannot generate new token');
        }
    }
}