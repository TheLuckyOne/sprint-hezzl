<?php
/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 04.03.18
 * Time: 19:45
 */

namespace App\Controller;


use App\Entity\AccountType;
use Symfony\Component\HttpFoundation\Request;

class ApiAccountTypeController extends RestController
{

    public function newAccountsTypesAction(Request $request) {
        $serializer = $this->container->get('jms_serializer');

        $accountTypeData = $request->request->all();
        $accountType = $serializer->fromArray($accountTypeData, AccountType::class);

        $em = $this->getDoctrine()->getManager();
        $em->persist($accountType);
        $em->flush();

        return $this->view($accountType, 200);
    }

}