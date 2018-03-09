<?php
/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 04.03.18
 * Time: 17:20
 *
 * Author: Dmitry Malakhov (abr_mail@mail.ru)
 * Prohibited for commercial use without the prior written consent of author
 *
 * Автор: Дмитрий Малахов (abr_mail@mail.ru)
 * Запрещено использование в коммерческих целях без письменного разрешения автора
 */

namespace App\Controller;

use App\Entity\CampaignStatus;
use Symfony\Component\HttpFoundation\Request;

class ApiCampaignStatusController extends RestController
{

    public function newCampaignStatusAction(Request $request) {
        $serializer = $this->container->get('jms_serializer');

        $campaignStatusData = $request->request->all();
        $campaignStatus = $serializer->fromArray($campaignStatusData, CampaignStatus::class);

        $em = $this->getDoctrine()->getManager();
        $em->persist($campaignStatus);
        $em->flush();

        return $this->view($campaignStatus, 200);
    }

}