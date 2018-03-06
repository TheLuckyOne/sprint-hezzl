<?php
/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 06.03.18
 * Time: 20:54
 */

namespace App\Controller;


use App\Entity\Campaign;
use App\Entity\Player;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

class ApiGameController extends RestController
{

    /**
     * @Route("/api/init", name="api_init", methods={"GET"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function initAction(Request $request)
    {
        parent::checkSum();

        $id = $request->get('campaign_id');
        if (!$id) {
            throw new HttpException(500, 'Id is required');
        }

        $campaign = $this->container->get('doctrine')->getRepository(Campaign::class)->find($id);
        if (!$campaign) {
            throw new HttpException(500, 'Campaign not found');
        }

        return $this->view([
            'status' => $campaign->getCampaignType()->getStatus(),
            'custom_setting' => $campaign->getCustomSetting(),
            'time_server' => (new \DateTime())->getTimestamp()
        ], 200);
    }

    /**
     * @Route("/api/info", name="api_info", methods={"GET"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function infoAction(Request $request)
    {
        parent::checkSum();

        $uid = $request->get('uid');
        if (!$uid) {
            throw new HttpException(500, 'Uid is required');
        }

        $playerIdFromRedis = $this->container->get('snc_redis.players_uids')->get($uid);
        $player = null;
        if ($playerIdFromRedis) {
            $player = $this->container->get('doctrine')->getRepository(Player::class)->find($playerIdFromRedis);
        } else {
            throw new HttpException(500, 'Uid is not valid');
        }

        return $this->view($player, 200);
    }

    /**
     * @Route("/api/login", name="api_login", methods={"POST"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function loginAction(Request $request)
    {
        parent::checkSum();

        $campaign_id = $request->get('campaign_id');
        if (!$campaign_id) {
            throw new HttpException(500, 'Campaign id is required');
        }

        $campaign = $this->container->get('doctrine')->getRepository(Campaign::class)->find($campaign_id);

        if (!$campaign) {
            throw new HttpException(500, 'Campaign not found');
        }

        $login = $request->get('login');
        if (!$login) {
            throw new HttpException(500, 'Login is required');
        }

        $login_type = $request->get('login_type'); //Сам не знаю, зачем этот параметр тут. Но ТЗ есть ТЗ.
        if (!$login_type) {
            throw new HttpException(500, 'Login type is required');
        }

        $name = $request->get('name');
        $email = $request->get('email');
        $phone = $request->get('phone');
        $sex = $request->get('sex');
        $city = $request->get('city');
        $birthday = $request->get('birthday');
        $company = $request->get('company');
        $job = $request->get('job');

        $player = $this->container->get('doctrine')->getRepository(Player::class)->findBy(['login' => $login, 'campaign' => $campaign_id]);
        if (!$player) {
            $player = new Player();
            $player->setLogin($login);
            $player->setCampaign($campaign);
            $player->setName($name);
            $player->setEmail($email);
            $player->setPhone($phone);
            $player->setSex($sex);
            $player->setCity($city);
            $player->setBirthday(new \DateTime($birthday));
            $player->setCompany($company);
            $player->setJob($job);
            $player->setScore(0);
            $player->setCoins(0);
            $player->setSystem([]);

            $em = $this->getDoctrine()->getManager();
            $em->persist($player);
            $em->flush();
        } else {
            $player = $player[0];
        }

        $uid = null;
        $operation_token = null;
        try {
            $uid = md5(random_bytes(20));
            $operation_token = md5(random_bytes(20));
        } catch (\Exception $e) {
            throw new HttpException(500, 'Cannot get new tokens');
        }

        $this->container->get('snc_redis.players_uids')->set($uid, $player->getId());
        $this->container->get('snc_redis.players_operations_tokens')->set($operation_token, $player->getId());
        return $this->view([
            'data' => [
                'system' => $player->getSystem(),
                'coins' => $player->getCoins(),
                'score' => $player->getScore(),
                'reg_date' => $player->getCreatedAt(),
                'last_day' => $player->getLastDay()
            ],
            'uid' => $uid,
            'operation_token' => $operation_token
        ], 200);
    }

    /**
     * @Route("/api/submit_score", name="api_submit_score", methods={"PUT"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function submitScoreAction(Request $request)
    {
        parent::checkSum();

        $uid = $request->get('uid');
        if (!$uid) {
            throw new HttpException(500, 'Uid is required');
        }

        $score = $request->get('score');
        if (!$score) {
            throw new HttpException(500, 'Score is required');
        }

        $operation_token = $request->get('operation_token');
        if (!$operation_token) {
            throw new HttpException(500, 'Operation token is required');
        }

        $playerIdFromRedis = $this->container->get('snc_redis.players_uids')->get($uid);
        if (!$playerIdFromRedis) {
            throw new HttpException(500, 'Uid is not valid');
        }

        $playerIdByOperationTokenFromRedis = $this->container->get('snc_redis.players_operations_tokens')->get($operation_token);
        if (!$playerIdByOperationTokenFromRedis) {
            throw new HttpException(500, 'Operation token is not valid');
        }

        if ($playerIdFromRedis != $playerIdByOperationTokenFromRedis) {
            throw new HttpException(500, 'Operation token mismatch');
        }

        $player = $this->container->get('doctrine')->getRepository(Player::class)->find($playerIdFromRedis);
        $player->setScore($player->getScore() + $score);

        $em = $this->getDoctrine()->getManager();
        $em->persist($player);
        $em->flush();

        $this->container->get('snc_redis.players_operations_tokens')->del($operation_token);

        try {
            $operation_token = md5(random_bytes(20));
        } catch (\Exception $e) {
            throw new HttpException(500, 'Cannot get new tokens');
        }

        $this->container->get('snc_redis.players_operations_tokens')->set($operation_token, $player->getId());

        return $this->view([
            'data' => [
                'score' => $player->getScore(),
            ],
            'operation_token' => $operation_token
        ], 200);
    }

    /**
     * @Route("/api/submit_coins", name="api_submit_coins", methods={"PUT"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function submitCoinsAction(Request $request)
    {
        parent::checkSum();

        $uid = $request->get('uid');
        if (!$uid) {
            throw new HttpException(500, 'Uid is required');
        }

        $coins = $request->get('coins');
        if (!$coins) {
            throw new HttpException(500, 'Coins is required');
        }

        $operation_token = $request->get('operation_token');
        if (!$operation_token) {
            throw new HttpException(500, 'Operation token is required');
        }

        $playerIdFromRedis = $this->container->get('snc_redis.players_uids')->get($uid);
        if (!$playerIdFromRedis) {
            throw new HttpException(500, 'Uid is not valid');
        }

        $playerIdByOperationTokenFromRedis = $this->container->get('snc_redis.players_operations_tokens')->get($operation_token);
        if (!$playerIdByOperationTokenFromRedis) {
            throw new HttpException(500, 'Operation token is not valid');
        }

        if ($playerIdFromRedis != $playerIdByOperationTokenFromRedis) {
            throw new HttpException(500, 'Operation token mismatch');
        }

        $player = $this->container->get('doctrine')->getRepository(Player::class)->find($playerIdFromRedis);
        $player->setCoins($player->getCoins() + $coins);

        $em = $this->getDoctrine()->getManager();
        $em->persist($player);
        $em->flush();

        $this->container->get('snc_redis.players_operations_tokens')->del($operation_token);

        try {
            $operation_token = md5(random_bytes(20));
        } catch (\Exception $e) {
            throw new HttpException(500, 'Cannot get new tokens');
        }

        $this->container->get('snc_redis.players_operations_tokens')->set($operation_token, $player->getId());

        return $this->view([
            'data' => [
                'coins' => $player->getCoins(),
            ],
            'operation_token' => $operation_token
        ], 200);
    }

}