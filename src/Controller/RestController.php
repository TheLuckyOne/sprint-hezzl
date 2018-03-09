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
     * @param string $uid
     * @return Member
     */
    public function getMemberByUid($uid) {
        if ($uid === null) {
            throw new HttpException(400, 'Uid is required');
        }

        $memberIdFromRedis = $this->container->get('snc_redis.members_tokens')->get($uid);
        if ($memberIdFromRedis !== null) {
            $member = $this->container->get('doctrine')->getRepository(Member::class)->find($memberIdFromRedis);
            if ($member !== null) {
                return $member;
            }
        }
        throw new HttpException(400, 'Uid is not valid');
    }

    /**
     * @param Player|Member $user
     * @return string
     */
    public function generateAndStoreNewUid($user) {
        $uid = $this->generateNewUid($user);
        $this->storeUid($user, $uid);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $uid;
    }

    /**
     * @param Player|Member $user
     * @return string
     */
    public function generateNewUid($user) {
        $redisStorage = $this->container->get('snc_redis.players_uids');
        if ($user instanceof Member) {
            $redisStorage = $this->container->get('snc_redis.members_tokens');
        }
        try {
            $oldUid = $user->getUid();
            $uid = md5(random_bytes(20));
            if ($oldUid !== null) {
                $redisStorage->del([$oldUid]);
            }
            return $uid;
        } catch (\Exception $e) {
            throw new HttpException(400, 'Cannot generate new uid');
        }
    }

    /**
     * @param Player|Member $user
     * @param string $uid
     */
    public function storeUid($user, $uid) {
        $redisStorage = $this->container->get('snc_redis.players_uids');
        if ($user instanceof Member) {
            $redisStorage = $this->container->get('snc_redis.members_tokens');
        }
        $redisStorage->set($uid, $user->getId());
        $user->setUid($uid);
    }

}