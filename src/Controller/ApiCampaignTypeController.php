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
use App\Entity\CampaignType;
use Symfony\Component\HttpFoundation\Request;

class ApiCampaignTypeController extends RestController
{

    public function newCampaignTypeAction(Request $request) {
        $serializer = $this->container->get('jms_serializer');

        $campaignTypeData = $request->request->all();
        $campaignStatus = $this->getDoctrine()->getRepository(CampaignStatus::class)->find($campaignTypeData['status']);

        unset($campaignTypeData['status']);

        $campaignType = $serializer->fromArray($campaignTypeData, CampaignType::class);
        $campaignType->setStatus($campaignStatus);

        $em = $this->getDoctrine()->getManager();
        $em->persist($campaignType);
        $em->flush();

        return $this->view($campaignType, 200);
    }

}