<?php
/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 04.03.18
 * Time: 19:41
 */

namespace App\Controller;


use App\Entity\Account;
use App\Entity\AccountType;
use Symfony\Component\HttpFoundation\Request;

class ApiAccountController extends RestController
{

    public function newAccountsAction(Request $request) {
        $serializer = $this->container->get('jms_serializer');

        $accountData = $request->request->all();
        $accountType = $this->getDoctrine()->getRepository(AccountType::class)->find($accountData['type']);

        unset($accountData['type']);

        $account = $serializer->fromArray($accountData, Account::class);
        $account->setType($accountType);

        $em = $this->getDoctrine()->getManager();
        $em->persist($account);
        $em->flush();

        return $this->view($account, 200);
    }

}