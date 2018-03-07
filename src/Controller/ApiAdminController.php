<?php
/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 06.03.18
 * Time: 20:54
 */

namespace App\Controller;


use App\Entity\Campaign;
use App\Entity\CampaignType;
use App\Entity\Member;
use App\Entity\MemberType;
use App\Entity\Player;
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

        $currentToken = $request->get('token');
        $member = $this->getMemberByToken($currentToken);
        return $this->view([
            'data' => [
                'name' => $member->getName(),
                'email' => $member->getEmail()
            ],
            'token' => $this->generateAndStoreNewToken($member, $currentToken)
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
            throw new HttpException(500, 'Name is required');
        }

        $email = $request->get('email');
        if ($email === null) {
            throw new HttpException(500, 'Email is required');
        }

        $phone = $request->get('phone');
        if ($phone === null) {
            throw new HttpException(500, 'Phone is required');
        }

        $password = $request->get('password');
        if ($password === null) {
            throw new HttpException(500, 'Password is required');
        }

        $typeId = $request->get('type_id');
        if ($typeId === null) {
            throw new HttpException(500, 'Type id is required');
        }

        $memberType = $this->container->get('doctrine')->getRepository(MemberType::class)->find($typeId);
        if ($memberType === null) {
            throw new HttpException(500, 'Type is not valid');
        }

        $member = $this->container->get('doctrine')->getRepository(Member::class)->findBy(['email' => $email]);
        if (count($member) == 0) {
            $member = $this->container->get('doctrine')->getRepository(Member::class)->findBy(['phone' => $phone]);
        }

        if (count($member) > 0) {
            throw new HttpException(500, 'Member exists');
        }

        $member = new Member();
        $member->setName($name);
        $member->setEmail($email);
        $member->setPhone($phone);
        $member->setPassword($password);
        $member->setType($memberType);
        $member->setSystemField([]);

        $em = $this->getDoctrine()->getManager();
        $em->persist($member);
        $em->flush();

        return $this->view([
            'token' => $this->generateAndStoreNewToken($member)
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
            throw new HttpException(500, 'Email is required');
        }

        $password = $request->get('password');
        if ($password === null) {
            throw new HttpException(500, 'Password is required');
        }

        $member = $this->container->get('doctrine')->getRepository(Member::class)->findBy(['email' => $email, 'password' => $password]);
        if (count($member) == 0) {
            throw new HttpException(500, 'Member not found');
        }

        $member = $member[0];
        return $this->view([
            'data' => [
                'name' => $member->getName(),
                'system_field' => $member->getSystemField()
            ],
            'token' => $this->generateAndStoreNewToken($member)
        ], 200);
    }

    /**
     * @Route("/api/admin/unlock_screen", name="api_admin_unlock_screen", methods={"GET"}) //Хз, что за метод и что от него требуется
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function unlockScreenAction(Request $request)
    {
        $this->checkSum($request);

        $password = $request->get('password');
        if ($password === null) {
            throw new HttpException(500, 'Password is required');
        }

        $currentToken = $request->get('token');
        $member = $this->getMemberByToken($currentToken);
        return $this->view([
            'token' => $this->generateAndStoreNewToken($member, $currentToken)
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

        $currentToken = $request->get('token');
        $member = $this->getMemberByToken($currentToken);
        return $this->view([
            'token' => $this->generateAndStoreNewToken($member, $currentToken)
        ], 200);
    }

    /**
     * @Route("/api/admin/dashboard_analytics", name="api_admin_dashboard_analytics", methods={"GET"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function dashboardAnalyticsAction(Request $request) {
        $this->checkSum($request);

        $currentToken = $request->get('token');
        $member = $this->getMemberByToken($currentToken);

        $campaigns = $this->container->get('doctrine')->getRepository(Campaign::class)->findBy(['member_id' => $member->getId()]);
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
            'token' => $this->generateAndStoreNewToken($member, $currentToken)
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

        $currentToken = $request->get('token');
        $member = $this->getMemberByToken($currentToken);

        $em = $this->getDoctrine()->getManager();
        $columns = $em->getClassMetadata(Campaign::class)->getColumnNames();

        $campaigns = $this->container->get('doctrine')->getRepository(Campaign::class)->findBy([]);
        return $this->view([
            'meta' => [
                'total' => count($campaigns),
            ],
            'fields' => $columns,
            'token' => $this->generateAndStoreNewToken($member, $currentToken)
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
            throw new HttpException(500, 'Count is required');
        }

        $page = $request->get('page'); //Без параметра количества записей на страницу этот параметр бесполезен
        if ($page === null) {
            throw new HttpException(500, 'Page is required');
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
            'token' => $this->generateAndStoreNewToken($member, $currentToken)
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

        $currentToken = $request->get('token');
        $member = $this->getMemberByToken($currentToken);

        $campaignTypes = $this->container->get('doctrine')->getRepository(CampaignType::class)->findBy([]);
        return $this->view([
            'meta' => [
                'total' => count($campaignTypes),
            ],
            'data' => $campaignTypes,
            'token' => $this->generateAndStoreNewToken($member, $currentToken)
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

        $currentToken = $request->get('token');
        $member = $this->getMemberByToken($currentToken);

        $em = $this->getDoctrine()->getManager();
        $columns = $em->getClassMetadata(Campaign::class)->getColumnNames(); //Я не знаю, как типы полей узнать

        return $this->view([
            'meta' => [
                'total' => count($columns),
            ],
            'data' => $columns,
            'token' => $this->generateAndStoreNewToken($member, $currentToken)
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

        $currentToken = $request->get('token');
        $member = $this->getMemberByToken($currentToken);

        $name = $request->get('name');
        if ($name === null) {
            throw new HttpException(500, 'Name is required');
        }

        $campaignTypeId = $request->get('campaign_type_id');
        if ($name === null) {
            throw new HttpException(500, 'Name is required');
        }

        $campaignType = $this->container->get('doctrine')->getRepository(CampaignType::class)->find($campaignTypeId);
        if ($campaignType === null) {
            throw new HttpException(500, 'Campaign type is not valid');
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
            'token' => $this->generateAndStoreNewToken($member, $currentToken)
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

        $currentToken = $request->get('token');
        $member = $this->getMemberByToken($currentToken);

        $campaignId = $request->get('campaign_id');
        if ($campaignId === null) {
            throw new HttpException(500, 'Campaign id is required');
        }

        $campaign = $this->container->get('doctrine')->getRepository(Campaign::class)->find($campaignId);
        if ($campaign === null) {
            throw new HttpException(500, 'Campaign is not valid');
        }

        return $this->view([
            'data' => [
                'name' => $campaign->getName(),
                'type' => $campaign->getCampaignType(),
                'custom_setting' => $campaign->getCustomSetting(),
                'status' => $campaign->getCampaignType()->getStatus()
            ],
            'token' => $this->generateAndStoreNewToken($member, $currentToken)
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

        $currentToken = $request->get('token');
        $member = $this->getMemberByToken($currentToken);

        $campaignId = $request->get('campaign_id');
        if ($campaignId === null) {
            throw new HttpException(500, 'Campaign id is required');
        }

        $name = $request->get('name');
        if ($name === null) {
            throw new HttpException(500, 'Name is required');
        }

        $campaign = $this->container->get('doctrine')->getRepository(Campaign::class)->find($campaignId);
        if ($campaign === null) {
            throw new HttpException(500, 'Campaign is not valid');
        }

        $campaign->setName($name);

        $em = $this->getDoctrine()->getManager();
        $em->persist($campaign);
        $em->flush();

        return $this->view([
            'token' => $this->generateAndStoreNewToken($member, $currentToken)
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
            throw new HttpException(500, 'Campaign id is required');
        }

        $status = $request->get('status');
        if ($status === null) {
            throw new HttpException(500, 'Name is required');
        }

        $campaign = $this->container->get('doctrine')->getRepository(Campaign::class)->find($campaignId);
        if ($campaign === null) {
            throw new HttpException(500, 'Campaign is not valid');
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

        $currentToken = $request->get('token');
        $member = $this->getMemberByToken($currentToken);

        $campaignId = $request->get('campaign_id');
        if ($campaignId === null) {
            throw new HttpException(500, 'Campaign id is required');
        }

        $customSetting = $request->get('custom_setting');
        if ($customSetting === null) {
            throw new HttpException(500, 'Custom setting is required');
        }

        $campaign = $this->container->get('doctrine')->getRepository(Campaign::class)->find($campaignId);
        if ($campaign === null) {
            throw new HttpException(500, 'Campaign is not valid');
        }

        $campaign->setCustomSetting($customSetting);

        $em = $this->getDoctrine()->getManager();
        $em->persist($campaign);
        $em->flush();

        return $this->view([
            'token' => $this->generateAndStoreNewToken($member, $currentToken)
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