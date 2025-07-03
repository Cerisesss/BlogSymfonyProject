<?php

namespace App\Tests\Controller;

use App\Controller\SecurityController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Response;


class SecurityControllerTest extends WebTestCase
{
    public function testLoadLoginPage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorExists('form');
    }
}