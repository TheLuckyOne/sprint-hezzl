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
        if (!$campaign_id) {
            throw new HttpException(500, 'Campaign is required');
        }

        $campaign = $this->container->get('doctrine')->getRepository(Campaign::class)->find($campaign_id);
        if ($campaign === null) {
            throw new HttpException(500, 'Campaign not found');
        }

        return $campaign;
    }

    public function getPlayerByUid(Request $request) {
        $uid = $request->get('uid');
        if (!$uid) {
            throw new HttpException(500, 'Uid is required');
        }

        $playerIdFromRedis = $this->container->get('snc_redis.players_uids')->get($uid);
        if ($playerIdFromRedis) {
            return $this->container->get('doctrine')->getRepository(Player::class)->find($playerIdFromRedis);
        } else {
            throw new HttpException(500, 'Uid is not valid');
        }
    }

}