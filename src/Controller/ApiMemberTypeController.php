<?php
/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 04.03.18
 * Time: 19:45
 *
 * Author: Dmitry Malakhov (abr_mail@mail.ru)
 * Prohibited for commercial use without the prior written consent of author
 *
 * Автор: Дмитрий Малахов (abr_mail@mail.ru)
 * Запрещено использование в коммерческих целях без письменного разрешения автора
 */

namespace App\Controller;


use App\Entity\MemberType;
use Symfony\Component\HttpFoundation\Request;

class ApiMemberTypeController extends RestController
{

    public function newMembersTypesAction(Request $request) {
        $serializer = $this->container->get('jms_serializer');

        $memberTypeData = $request->request->all();
        $memberType = $serializer->fromArray($memberTypeData, MemberType::class);

        $em = $this->getDoctrine()->getManager();
        $em->persist($memberType);
        $em->flush();

        return $this->view($memberType, 200);
    }

}