<?php
/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 03.03.18
 * Time: 23:42
 */

namespace App\Controller;

use App\Entity\Member;
use App\Entity\Campaign;
use App\Entity\CampaignType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiCampaignController extends RestController
{

    public function getCampaignsAction(Request $request)
    {
        $id = $request->get('id');
        if (!$id) {
            throw new HttpException(500, 'Id is required');
        }

        $campaign = $this->container->get('doctrine')->getRepository(Campaign::class)->find($id);

        if (!$campaign) {
            throw new HttpException(500, 'Campaign not found');
        }

        return $this->view($campaign, 200);
    }

    public function newCampaignsAction(Request $request)
    {
        $serializer = $this->container->get('jms_serializer');

        $campaignData = $request->request->all();
        $member = $this->container->get('doctrine')->getRepository(Member::class)->find($campaignData['member']);
        $campaignType = $this->container->get('doctrine')->getRepository(CampaignType::class)->find($campaignData['campaign_type']);

        unset($campaignData['member']);
        unset($campaignData['campaign_type']);

        $campaign = $serializer->fromArray($campaignData, Campaign::class);
        $campaign->setMember($member);
        $campaign->setCampaignType($campaignType);

        $em = $this->getDoctrine()->getManager();
        $em->persist($campaign);
        $em->flush();

        return $this->view($campaign, 200);
    }

}
