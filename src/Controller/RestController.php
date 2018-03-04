<?php
/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 03.03.18
 * Time: 23:45
 */

namespace App\Controller;


use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RestController extends FOSRestController
{

    /**
     * @return bool
     * @throws HttpException
     */
    public function checkSum() {
        //TODO
        //throw new HttpException(503,'Checksum is failed');

        return true;
    }

}