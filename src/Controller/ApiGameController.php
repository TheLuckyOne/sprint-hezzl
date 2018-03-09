<?php
/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 06.03.18
 * Time: 20:54
 *
 * Author: Dmitry Malakhov (abr_mail@mail.ru)
 * Prohibited for commercial use without the prior written consent of author
 *
 * Автор: Дмитрий Малахов (abr_mail@mail.ru)
 * Запрещено использование в коммерческих целях без письменного разрешения автора
 */

namespace App\Controller;


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
        $currentUid = $request->get('uid');
        $player = $this->getPlayerByUid($currentUid);
        return $this->view([
            'data' => [
                'id' => $player->getId(),
                'name' => $player->getName(),
                'email' => $player->getEmail(),
                'phone' => $player->getPhone(),
                'sex' => $player->getSex(),
                'birthday' => $player->getBirthday(),
                'system' => $player->getSystem(),
                'coins' => $player->getCoins(),
                'score' => $player->getScore(),
                'reg_date' => $player->getCreatedAt(),
                'last_day' => $player->getLastDay()
            ],
        ], 200);
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
        if ($login === null) {
            throw new HttpException(400, 'Login is required');
        }

        $player = $this->container->get('doctrine')->getRepository(Player::class)->findBy(['login' => $login, 'campaign' => $campaign->getId()]);
        if (count($player) == 0) {
            $name = $request->get('name');
            if ($name === null) {
                throw new HttpException(400, 'Name is required');
            }

            $email = $request->get('email');
            if ($email === null) {
                throw new HttpException(400, 'Email is required');
            }

            $phone = $request->get('phone');
            if ($phone === null) {
                throw new HttpException(400, 'Phone is required');
            }

            $sex = $request->get('sex');
            if ($sex === null) {
                throw new HttpException(400, 'Sex is required');
            }

            $player = new Player();
            $player->setLogin($login);
            $player->setCampaign($campaign);
            $player->setName($name);
            $player->setEmail($email);
            $player->setPhone($phone);
            $player->setSex($sex);
            $player->setBirthday(new \DateTime($request->get('birthday')));
            $player->setScore(0);
            $player->setCoins(0);
            $player->setSystem([]);
            $uid = $this->generateNewUid($player);
            $player->setUid($uid);

            $em = $this->getDoctrine()->getManager();
            $em->persist($player);
            $em->flush();

            $this->storeUid($player, $uid);
            $em->persist($player);
            $em->flush();
        } else {
            $player = $player[0];
        }

        return $this->view([
            'data' => [
                'system' => $player->getSystem(),
                'coins' => $player->getCoins(),
                'score' => $player->getScore(),
                'reg_date' => $player->getCreatedAt(),
                'last_day' => $player->getLastDay()
            ],
            'uid' => $uid
        ], 200);
    }

    /**
     * @Route("/api/submit_score", name="api_submit_score", methods={"PUT"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function submitScoreAction(Request $request)
    {
        $this->checkSum($request);

        $score = $request->get('score');
        if ($score === null) {
            throw new HttpException(400, 'Score is required');
        }

        $player = $this->getPlayerByUid($request->get('uid'));
        $player->setScore($player->getScore() + $score);

        $em = $this->getDoctrine()->getManager();
        $em->persist($player);
        $em->flush();

        return $this->view([
            'data' => [
                'score' => $player->getScore(),
            ],
            'uid' => $this->generateAndStoreNewUid($player)
        ], 200);
    }

    /**
     * @Route("/api/submit_coins", name="api_submit_coins", methods={"PUT"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function submitCoinsAction(Request $request)
    {
        $this->checkSum($request);

        $coins = $request->get('coins');
        if ($coins === null) {
            throw new HttpException(400, 'Coins is required');
        }

        $player = $this->getPlayerByUid($request->get('uid'));
        $player->setCoins($player->getCoins() + $coins);

        $em = $this->getDoctrine()->getManager();
        $em->persist($player);
        $em->flush();

        return $this->view([
            'data' => [
                'coins' => $player->getCoins(),
            ],
            'uid' => $this->generateAndStoreNewUid($player)
        ], 200);
    }

    /**
     * @Route("/api/update_system", name="api_update_system", methods={"PUT"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function updateSystemAction(Request $request) {
        $this->checkSum($request);

        $system = $request->get('system');
        if ($system === null) {
            throw new HttpException(400, 'System is required');
        }

        $player = $this->getPlayerByUid($request->get('uid'));
        $player->setSystem($system);

        $em = $this->getDoctrine()->getManager();
        $em->persist($player);
        $em->flush();

        return $this->view([
            'data' => [
                'system' => $player->getSystem(),
            ],
            'uid' => $this->generateAndStoreNewUid($player)
        ], 200);
    }

    /**
     * @Route("/api/rating", name="api_rating", methods={"GET"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function ratingAction(Request $request)
    {
        $this->checkSum($request);

        $count = $request->get('count');
        if ($count === null) {
            throw new HttpException(400, 'Count is required');
        }

        $currentUid = $request->get('uid');
        $player = $this->getPlayerByUid($currentUid);

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
            ],
        ], 200);
    }

}