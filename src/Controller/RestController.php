<?php
/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 03.03.18
 * Time: 23:45
 */

namespace App\Controller;


use App\Entity\Campaign;
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
        //throw new HttpException(500,'Checksum is failed');
    }

    /**
     * @param Request $request
     * @return Campaign
     */
    public function extractCampaign(Request $request) {
        $campaign_id = $request->get('campaign_id');
        if ($campaign_id === null) {
            throw new HttpException(500, 'Campaign id is required');
        }

        $campaign = $this->container->get('doctrine')->getRepository(Campaign::class)->find($campaign_id);
        if ($campaign === null) {
            throw new HttpException(500, 'Campaign not found');
        }

        return $campaign;
    }

    /**
     * @param Request $request
     * @return Player
     */
    public function getPlayerByUid(Request $request) {
        $uid = $request->get('uid');
        if ($uid === null) {
            throw new HttpException(500, 'Uid is required');
        }

        $playerIdFromRedis = $this->container->get('snc_redis.players_uids')->get($uid);
        if ($playerIdFromRedis !== null) {
            $player = $this->container->get('doctrine')->getRepository(Player::class)->find($playerIdFromRedis);
            return $player;
        } else {
            throw new HttpException(500, 'Uid is not valid');
        }
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
            throw new HttpException(500, 'Cannot generate new uid');
        }
    }

}