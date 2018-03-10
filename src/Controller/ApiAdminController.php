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


use App\Entity\Campaign;
use App\Entity\CampaignType;
use App\Entity\Member;
use App\Entity\MemberType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

class ApiAdminController extends RestController
{

    /**
     * @Route("/api/admin/info", name="api_admin_info", methods={"GET"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function infoAction(Request $request)
    {
        $this->checkSum($request);

        $currentUid = $request->get('uid');
        $member = $this->getMemberByUid($currentUid);
        return $this->view([
            'data' => [
                'name' => $member->getName(),
                'email' => $member->getEmail()
            ],
        ], 200);
    }

    /**
     * @Route("/api/admin/signup", name="api_admin_signup", methods={"POST"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function signupAction(Request $request)
    {
        $this->checkSum($request);

        $name = $request->get('name');
        if ($name === null) {
            throw new HttpException(400, ['message' => 'Name is required', 'code' => 10]);
        }

        $email = $request->get('email');
        if ($email === null) {
            throw new HttpException(400, ['message' => 'Email is required', 'code' => 11]);
        }

        $phone = $request->get('phone');
        if ($phone === null) {
            throw new HttpException(400, ['message' => 'Phone is required', 'code' => 12]);
        }

        $password = $request->get('password');
        if ($password === null) {
            throw new HttpException(400, ['message' => 'Password is required', 'code' => 18]);
        }

        $typeId = $request->get('type_id');
        if ($typeId === null) {
            throw new HttpException(400, ['message' => 'Type id is required', 'code' => 19]);
        }

        $memberType = $this->container->get('doctrine')->getRepository(MemberType::class)->find($typeId);
        if ($memberType === null) {
            throw new HttpException(400, ['message' => 'Type is not valid', 'code' => 20]);
        }

        $member = $this->container->get('doctrine')->getRepository(Member::class)->findBy(['email' => $email]);
        if (count($member) == 0) {
            $member = $this->container->get('doctrine')->getRepository(Member::class)->findBy(['phone' => $phone]);
        }

        if (count($member) > 0) {
            throw new HttpException(400, ['message' => 'Member exists', 'code' => 21]);
        }

        $member = new Member();
        $member->setName($name);
        $member->setEmail($email);
        $member->setPhone($phone);
        $member->setPassword($password);
        $member->setType($memberType);
        $member->setSystemField([]);
        $uid = $this->generateNewUid($member);
        $member->setUid($uid);

        $em = $this->getDoctrine()->getManager();
        $em->persist($member);
        $em->flush();

        $this->storeUid($member, $uid);
        $em->persist($member);
        $em->flush();

        return $this->view([
            'uid' => $uid
        ], 200);
    }

    /**
     * @Route("/api/admin/login", name="api_admin_login", methods={"GET"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function loginAction(Request $request)
    {
        $this->checkSum($request);

        $email = $request->get('email');
        if ($email === null) {
            throw new HttpException(400, ['message' => 'Email is required', 'code' => 11]);
        }

        $password = $request->get('password');
        if ($password === null) {
            throw new HttpException(400, ['message' => 'Password is required', 'code' => 18]);
        }

        $member = $this->container->get('doctrine')->getRepository(Member::class)->findBy(['email' => $email, 'password' => sha1($password)]);
        if (count($member) == 0) {
            throw new HttpException(400, ['message' => 'Member not found', 'code' => 22]);
        }

        $member = $member[0];
        return $this->view([
            'data' => [
                'name' => $member->getName(),
                'system_field' => $member->getSystemField()
            ],
            'uid' => $this->generateAndStoreNewUid($member)
        ], 200);
    }

    /**
     * @Route("/api/admin/unlock_screen", name="api_admin_unlock_screen", methods={"GET"}) //Хз, чем отличается от refresh_token
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function unlockScreenAction(Request $request)
    {
        $this->checkSum($request);

        $password = $request->get('password');
        if ($password === null) {
            throw new HttpException(400, ['message' => 'Password is required', 'code' => 18]);
        }

        $member = $this->getMemberByUid($request->get('uid'));
        return $this->view([
            'uid' => $this->generateAndStoreNewUid($member)
        ], 200);
    }

    /**
     * @Route("/api/admin/refresh_token", name="api_admin_refresh_token", methods={"GET"}) //GET-запрос на обновление данных? Хм...
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function refreshTokenAction(Request $request)
    {
        $this->checkSum($request);

        $member = $this->getMemberByUid($request->get('uid'));
        return $this->view([
            'uid' => $this->generateAndStoreNewUid($member)
        ], 200);
    }

    /**
     * @Route("/api/admin/dashboard_analytics", name="api_admin_dashboard_analytics", methods={"GET"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function dashboardAnalyticsAction(Request $request) {
        $this->checkSum($request);

        $currentUid = $request->get('uid');
        $member = $this->getMemberByUid($currentUid);

        $campaigns = $this->container->get('doctrine')->getRepository(Campaign::class)->findBy(['member' => $member]);
        if (count($campaigns) == 0) {
            throw new HttpException(400, ['message' => 'Campaigns did not find', 'code' => 23]);
        }

        $players_amount = 0;
        foreach ($campaigns as $campaign) { //Один фиг, я не знаю, как тут join-ы реализовать
            $players = $this->container->get('doctrine')->getRepository(Campaign::class)->findBy(['campaign_id' => $campaign->getId()]);
            $players_amount += count($players);
        }

        return $this->view([
            'data' => [
                'total' => count($campaigns),
                'players' => count($players)
            ],
        ], 200);
    }

    /**
     * @Route("/api/admin/dashboard_list_field", name="api_admin_dashboard_list_field", methods={"GET"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function dashboardListFieldAction(Request $request)
    {
        $this->checkSum($request);

        $currentUid = $request->get('uid');
        $member = $this->getMemberByUid($currentUid);

        $em = $this->getDoctrine()->getManager();
        $columns = $em->getClassMetadata(Campaign::class)->getColumnNames();

        $campaigns = $this->container->get('doctrine')->getRepository(Campaign::class)->findBy([]);
        return $this->view([
            'meta' => [
                'total' => count($campaigns),
            ],
            'fields' => $columns,
        ], 200);
    }

    /**
     * @Route("/api/admin/dashboard_list", name="api_admin_dashboard_list", methods={"GET"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function dashboardListAction(Request $request)
    {
        /*$this->checkSum($request);

        $currentToken = $request->get('token');
        $member = $this->getMemberByToken($currentToken);

        $count = $request->get('count');
        if ($count === null) {
            throw new HttpException(400, ['message' => 'Count is required', 'code' => ]);
        }

        $page = $request->get('page'); //Без параметра количества записей на страницу этот параметр бесполезен
        if ($page === null) {
            throw new HttpException(400, ['message' => 'Page is required', 'code' => ]);
        }

        $filter = $request->get('filter');

        if ($filter === null) {
            $filter = [];

            $em = $this->getDoctrine()->getManager();
            $columns = $em->getClassMetadata('App\Entity\Campaign')->getColumnNames();

            foreach ($columns as $column) {
                $filter[$column] = null; //Хз что подразумевается под "значением поля" в фильтре
            }
        }

        $sort = $request->get('sort');
        if ($sort === null) {
            $sort = [];
        }

        return $this->view([
            'meta' => [
                'total' => ,
                'page' => 1,
                'current' => 1
            ],
            'data' => [

            ],
        ], 200);*/ //Нафиг... Сложно
    }

    /**
     * @Route("/api/admin/campaign_type", name="api_admin_campaign_type", methods={"GET"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function campaignTypeAction(Request $request)
    {
        $this->checkSum($request);

        $currentUid = $request->get('uid');
        $this->getMemberByUid($currentUid);

        $campaignTypes = $this->container->get('doctrine')->getRepository(CampaignType::class)->findBy([]);
        return $this->view([
            'meta' => [
                'total' => count($campaignTypes),
            ],
            'data' => $campaignTypes,
        ], 200);
    }

    /**
     * @Route("/api/admin/campaign_create_fields", name="api_admin_campaign_create_fields", methods={"GET"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function campaignCreateFieldsAction(Request $request)
    {
        $this->checkSum($request);

        $currentUid = $request->get('uid');
        $member = $this->getMemberByUid($currentUid);

        $em = $this->getDoctrine()->getManager();
        $columns = $em->getClassMetadata(Campaign::class)->getColumnNames(); //Я не знаю, как типы полей узнать

        return $this->view([
            'meta' => [
                'total' => count($columns),
            ],
            'data' => $columns,
        ], 200);
    }

    /**
     * @Route("/api/admin/campaign_create", name="api_admin_campaign_create", methods={"POST"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function campaignCreateAction(Request $request) //Дублирует /api/campaigns/new, но если очень надо, чего б не продублировать
    {
        $this->checkSum($request);

        $member = $this->getMemberByUid($request->get('uid'));

        $name = $request->get('name');
        if ($name === null) {
            throw new HttpException(400, ['message' => 'Name is required', 'code' => 10]);
        }

        $campaignTypeId = $request->get('campaign_type_id');
        if ($campaignTypeId === null) {
            throw new HttpException(400, ['message' => 'Campaign type id is required', 'code' => 24]);
        }

        $campaignType = $this->container->get('doctrine')->getRepository(CampaignType::class)->find($campaignTypeId);
        if ($campaignType === null) {
            throw new HttpException(400, ['message' => 'Campaign type is not valid', 'code' => 25]);
        }

        $campaign = new Campaign();
        $campaign->setName($name);
        $campaign->setMember($member);
        $campaign->setCampaignType($campaignType);
        $campaign->setCustomSetting('');
        $campaign->setLoginType(1); //В ТЗ указаний нет, подставляем чего-нть рандомное
        $campaign->setMessageEnd('');

        $em = $this->getDoctrine()->getManager();
        $em->persist($campaign);
        $em->flush();

        return $this->view([
            'data' => [
                'id' => $campaign->getId(),
                'status' => $campaign->getCampaignType()->getStatus()
            ],
            'uid' => $this->generateAndStoreNewUid($member)
        ], 200);
    }

    /**
     * @Route("/api/admin/campaign_info", name="api_admin_campaign_info", methods={"GET"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function campaignInfoAction(Request $request) //Дублирует /api/campaigns
    {
        $this->checkSum($request);

        $currentUid = $request->get('uid');
        $this->getMemberByUid($currentUid);

        $campaignId = $request->get('campaign_id');
        if ($campaignId === null) {
            throw new HttpException(400, ['message' => 'Campaign id is required', 'code' => 1]);
        }

        $campaign = $this->container->get('doctrine')->getRepository(Campaign::class)->find($campaignId);
        if ($campaign === null) {
            throw new HttpException(400, ['message' => 'Campaign not found', 'code' => 2]);
        }

        return $this->view([
            'data' => [
                'name' => $campaign->getName(),
                'type' => $campaign->getCampaignType(),
                'custom_setting' => $campaign->getCustomSetting(),
                'status' => $campaign->getCampaignType()->getStatus()
            ],
        ], 200);
    }

    /**
     * @Route("/api/admin/campaign_info_update", name="api_admin_campaign_info_update", methods={"PUT"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function campaignInfoUpdateAction(Request $request)
    {
        $this->checkSum($request);

        $member = $this->getMemberByUid($request->get('uid'));

        $campaignId = $request->get('campaign_id');
        if ($campaignId === null) {
            throw new HttpException(400, ['message' => 'Campaign id is required', 'code' => 1]);
        }

        $name = $request->get('name');
        if ($name === null) {
            throw new HttpException(400, ['message' => 'Name is required', 'code' => 10]);
        }

        $campaign = $this->container->get('doctrine')->getRepository(Campaign::class)->find($campaignId);
        if ($campaign === null) {
            throw new HttpException(400, ['message' => 'Campaign not found', 'code' => 2]);
        }

        $campaign->setName($name);

        $em = $this->getDoctrine()->getManager();
        $em->persist($campaign);
        $em->flush();

        return $this->view([
            'uid' => $this->generateAndStoreNewUid($member)
        ], 200);
    }

    /**
     * @Route("/api/admin/campaign_status_update", name="api_admin_campaign_status_update", methods={"PUT"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function campaignStatusUpdateAction(Request $request)
    {
        //У компаний нет статусов, они есть у типов компаний. Если изменяем статус, он изменится сразу у нескольких компаний

        /*$this->checkSum($request);

        $currentToken = $request->get('token');
        $member = $this->getMemberByToken($currentToken);

        $campaignId = $request->get('campaign_id');
        if ($campaignId === null) {
            throw new HttpException(400, ['message' => 'Campaign id is required', 'code' => ]);
        }

        $status = $request->get('status');
        if ($status === null) {
            throw new HttpException(400, ['message' => 'Name is required', 'code' => ]);
        }

        $campaign = $this->container->get('doctrine')->getRepository(Campaign::class)->find($campaignId);
        if ($campaign === null) {
            throw new HttpException(400, ['message' => 'Campaign is not valid', 'code' => ]);
        }

        $campaign->setName($name);

        $em = $this->getDoctrine()->getManager();
        $em->persist($campaign);
        $em->flush();

        return $this->view([
            'token' => $this->generateAndStoreNewToken($member, $currentToken)
        ], 200);*/
    }

    /**
     * @Route("/api/admin/campaign_custom_setting_update", name="api_admin_campaign_custom_setting_update", methods={"PUT"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function campaignCustomSettingUpdateAction(Request $request)
    {
        $this->checkSum($request);

        $member = $this->getMemberByUid($request->get('uid'));

        $campaignId = $request->get('campaign_id');
        if ($campaignId === null) {
            throw new HttpException(400, ['message' => 'Campaign id is required', 'code' => 1]);
        }

        $customSetting = $request->get('custom_setting');
        if ($customSetting === null) {
            throw new HttpException(400, ['message' => 'Custom setting is required', 'code' => 26]);
        }

        $campaign = $this->container->get('doctrine')->getRepository(Campaign::class)->find($campaignId);
        if ($campaign === null) {
            throw new HttpException(400, ['message' => 'Campaign not found', 'code' => 2]);
        }

        $campaign->setCustomSetting($customSetting);

        $em = $this->getDoctrine()->getManager();
        $em->persist($campaign);
        $em->flush();

        return $this->view([
            'uid' => $this->generateAndStoreNewUid($member)
        ], 200);
    }

    /**
     * @Route("/api/admin/campaign_status", name="api_admin_campaign_status", methods={"GET"}) //Какие статусы считать доступными, а какие нет?
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function campaignStatusAction(Request $request)
    {
    }

}