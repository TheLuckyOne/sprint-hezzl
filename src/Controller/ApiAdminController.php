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

class ApiAdminController extends RestController
{

    /**
     * @Route("/api/admin/info", name="api_admin_info", methods={"GET"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function infoAction(Request $request)
    {
    }

    /**
     * @Route("/api/admin/signup", name="api_admin_signup", methods={"POST"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function signupAction(Request $request)
    {
    }

    /**
     * @Route("/api/admin/login", name="api_admin_login", methods={"GET"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function loginAction(Request $request)
    {
    }

    /**
     * @Route("/api/admin/unlock_screen", name="api_admin_unlock_screen", methods={"GET"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function unlockScreenAction(Request $request)
    {
    }

    /**
     * @Route("/api/admin/refresh_token", name="api_admin_refresh_token", methods={"GET"}) //GET-запрос на обновление данных? Хм...
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function refreshTokenAction(Request $request)
    {
    }

    /**
     * @Route("/api/admin/dashboard_analytics", name="api_admin_dashboard_analytics", methods={"GET"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function dashboardAnalyticsAction(Request $request) {
    }

    /**
     * @Route("/api/admin/dashboard_list_field", name="api_admin_dashboard_list_field", methods={"GET"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function dashboardListFieldAction(Request $request)
    {
    }

    /**
     * @Route("/api/admin/dashboard_list", name="api_admin_dashboard_list", methods={"GET"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function dashboardListAction(Request $request)
    {
    }

    /**
     * @Route("/api/admin/campaign_type", name="api_admin_campaign_type", methods={"GET"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function campaignTypeAction(Request $request)
    {
    }

    /**
     * @Route("/api/admin/campaign_create_fields", name="api_admin_campaign_create_fields", methods={"GET"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function campaignCreateFieldsAction(Request $request)
    {
    }

    /**
     * @Route("/api/admin/campaign_create", name="api_admin_campaign_create", methods={"POST"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function campaignCreateAction(Request $request) //Дублирует /api/campaigns/new, но если очень надо, чего б не продублировать
    {
    }

    /**
     * @Route("/api/admin/campaign_info", name="api_admin_campaign_info", methods={"GET"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function campaignInfoAction(Request $request) //Дублирует /api/campaigns
    {
    }

    /**
     * @Route("/api/admin/campaign_info_update", name="api_admin_campaign_info_update", methods={"PUT"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function campaignInfoUpdateAction(Request $request)
    {
    }

    /**
     * @Route("/api/admin/campaign_status_update", name="api_admin_campaign_status_update", methods={"PUT"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function campaignStatusUpdateAction(Request $request)
    {
    }

    /**
     * @Route("/api/admin/campaign_custom_setting_update", name="api_admin_campaign_custom_setting_update", methods={"PUT"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function campaignCustomSettingUpdateAction(Request $request)
    {
    }

    /**
     * @Route("/api/admin/campaign_status", name="api_admin_campaign_status", methods={"GET"})
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function campaignStatusAction(Request $request)
    {
    }

}