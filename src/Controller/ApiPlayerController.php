<?php
/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 04.03.18
 * Time: 22:43
 */

namespace App\Controller;


use App\Entity\Campaign;
use App\Entity\Player;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiPlayerController extends RestController
{

    public function getPlayersAction(Request $request)
    {
        $id = $request->get('id');
        if (!$id) {
            throw new HttpException(500, 'Id is required');
        }

        $player = $this->container->get('doctrine')->getRepository(Player::class)->find($id);

        if (!$player) {
            throw new HttpException(500, 'Player not found');
        }

        return $this->view($player, 200);
    }

    public function newPlayerAction(Request $request) {
        $serializer = $this->container->get('jms_serializer');

        $playerData = $request->request->all();
        $campaign = $this->getDoctrine()->getRepository(Campaign::class)->find($playerData['campaign']);

        unset($playerData['campaign']);

        $player = $serializer->fromArray($playerData, Player::class);
        $player->setCampaign($campaign);

        $em = $this->getDoctrine()->getManager();
        $em->persist($player);
        $em->flush();

        return $this->view($player, 200);
    }

}