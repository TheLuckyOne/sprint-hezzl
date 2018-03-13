<?php
/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 04.03.18
 * Time: 19:41
 *
 * Author: Dmitry Malakhov (abr_mail@mail.ru)
 * Prohibited for commercial use without the prior written consent of author
 *
 * Автор: Дмитрий Малахов (abr_mail@mail.ru)
 * Запрещено использование в коммерческих целях без письменного разрешения автора
 */

namespace App\Controller;


use App\Entity\Member;
use App\Entity\MemberType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiMemberController extends RestController
{

    public function getMembersAction(Request $request)
    {
        $id = $request->get('id');
        if ($id === null) {
            throw new HttpException(400, ['message' => 'Id is required', 'code' => 6]);
        }

        $member = $this->container->get('doctrine')->getRepository(Member::class)->find($id);

        if ($member === null) {
            throw new HttpException(400, ['message' => 'Member not found', 'code' => 8]);
        }

        return $this->view($member, 200);
    }

    public function newMembersAction(Request $request) {
        $serializer = $this->container->get('jms_serializer');

        $memberData = $request->request->all();
        $memberType = $this->getDoctrine()->getRepository(MemberType::class)->find($memberData['type']);

        unset($memberData['type']);

        $member = $serializer->fromArray($memberData, Member::class);
        $member->setType($memberType);
        $member->setPassword($member->getPassword());
        $uid = $this->generateNewUid($member);
        $member->setUid($uid);

        $em = $this->getDoctrine()->getManager();
        $em->persist($member);
        $em->flush();

        $this->storeUid($member, $uid);

        return $this->view($member, 200);
    }

}