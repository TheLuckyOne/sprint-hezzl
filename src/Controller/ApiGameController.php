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
        $this->checkSum($request);
        $campaign = $this->extractCampaign($request);

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
        $this->checkSum($request);
        return $this->view($this->getPlayerByUid($request), 200);
    }

    /**
     * @Route("/api/login", name="api_login", methods={"POST"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function loginAction(Request $request)
    {
        $this->checkSum($request);
        $campaign = $this->extractCampaign($request);

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
        $birthday = $request->get('birthday');

        $player = $this->container->get('doctrine')->getRepository(Player::class)->findBy(['login' => $login, 'campaign' => $campaign->getId()]);
        if (!$player) {
            $player = new Player();
            $player->setLogin($login);
            $player->setCampaign($campaign);
            $player->setName($name);
            $player->setEmail($email);
            $player->setPhone($phone);
            $player->setSex($sex);
            $player->setBirthday(new \DateTime($birthday));
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
        if (!$player) {
            throw new HttpException(500, 'Player did not found');
        }

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

        //Скажу честно, я не успел придумать, как централизировать данный код. Поэтому да, копипасты много.
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
        if (!$player) {
            throw new HttpException(500, 'Player did not found');
        }

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

    /**
     * @Route("/api/update_system", name="api_update_system", methods={"PUT"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function updateSystemAction(Request $request) {
        parent::checkSum();

        $uid = $request->get('uid');
        if (!$uid) {
            throw new HttpException(500, 'Uid is required');
        }

        $system = $request->get('system');
        if (!$system) {
            throw new HttpException(500, 'System is required');
        }

        $operation_token = $request->get('operation_token');
        if (!$operation_token) {
            throw new HttpException(500, 'Operation token is required');
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
        if (!$player) {
            throw new HttpException(500, 'Player did not found');
        }

        $player->setSystem($system);

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

    /**
     * @Route("/api/rating", name="api_rating", methods={"GET"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function ratingAction(Request $request)
    {
        parent::checkSum();

        $uid = $request->get('uid');
        if (!$uid) {
            throw new HttpException(500, 'Uid is required');
        }

        $count = $request->get('count');
        if (!$count) {
            throw new HttpException(500, 'Count is required');
        }

        $playerIdFromRedis = $this->container->get('snc_redis.players_uids')->get($uid);
        if (!$playerIdFromRedis) {
            throw new HttpException(500, 'Uid is not valid');
        }

        $player = $this->container->get('doctrine')->getRepository(Player::class)->find($playerIdFromRedis);
        if (!$player) {
            throw new HttpException(500, 'Player did not found');
        }

        $top = $this->container->get('doctrine')->getRepository(Player::class)->findBy([], ['score' => 'DESC'], $count);
        $top_result = [];
        $pos = 0;
        foreach ($top as $playerInTop) {
            $top_result[] = [
                'rank' => ++$pos,
                'id' => $playerInTop->getId(),
                'name' => $playerInTop->getName(),
                'score' => $playerInTop->getScore(),
                'current' => $playerInTop->getId() == $player->getId()
            ];
        }

        return $this->view([
            'data' => [
                'top' => $top_result
            ]
        ], 200);
    }

}