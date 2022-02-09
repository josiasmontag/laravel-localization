<?php

namespace Lunaweb\Localization\Tests;


use Mockery as m;


class MiddlewareTest extends TestCase
{




    public function tearDown(): void
    {
        m::close();
    }

    public function setUp(): void
    {
        parent::setUp();

    }


    public function testMiddleware()
    {


        $this->createRoutes();

        $response = $this->get('/middleware', ['Accept-Language' => null]);
        $response->assertStatus(200);
        $this->assertEquals('en', app()->getLocale());
        $response->assertCookie('locale', 'en', false);

        $this->withUnencryptedCookie('locale', '');

        $response = $this->get('/fr/middleware', ['Accept-Language' => null]);
        $response->assertStatus(200);
        $this->assertEquals('fr', app()->getLocale());

        $this->withUnencryptedCookie('locale', '');

        $response = $this->get('http://localhost.de/middleware', ['Accept-Language' => null]);
        $response->assertStatus(200);
        $this->assertEquals('de', app()->getLocale());


    }


    public function testMiddlewareQueryParameter()
    {


        $this->createRoutes();


        $response = $this->get('/middleware?hl=th');
        $response->assertRedirect('/th/middleware');
        $this->assertEquals('th', app()->getLocale());

        $this->withUnencryptedCookie('locale', '');

        $response = $this->get('/fr/middleware?hl=th');
        $response->assertRedirect('/th/middleware');
        $this->assertEquals('th', app()->getLocale());

        $this->withUnencryptedCookie('locale', 'fr');

        $response = $this->get('/fr/middleware?hl=th');
        $response->assertRedirect('/th/middleware');
        $this->assertEquals('th', app()->getLocale());

        $this->withUnencryptedCookie('locale', 'fr');

        $response = $this->get('/fr/middleware?hl=de');
        $response->assertRedirect('http://localhost.de/middleware');
        $this->assertEquals('de', app()->getLocale());

        $this->withUnencryptedCookie('locale', '');

        $response = $this->get('/th/middleware?hl=th');
        $response->assertRedirect('/th/middleware');
        $this->assertEquals('th', app()->getLocale());


    }


    public function testMiddlewareCookie()
    {


        $this->createRoutes();

        $response = $this->withUnencryptedCookie('locale', 'th')->get('/middleware');
        $response->assertRedirect('/th/middleware');
        $this->assertEquals('th', app()->getLocale());

        $response = $this->withUnencryptedCookie('locale', 'en')->get('/fr/middleware');
        $response->assertRedirect('/middleware');
        $this->assertEquals('en', app()->getLocale());

        $response = $this->withUnencryptedCookie('locale', 'de')->get('/fr/middleware');
        $response->assertRedirect('http://localhost.de/middleware');
        $this->assertEquals('de', app()->getLocale());

    }


    public function testMiddlewareLanguageHeaderDetection()
    {

        $this->createRoutes();

        $response = $this->get('/middleware', ['Accept-Language' => 'fr-FR']);
        $response->assertCookie('locale', 'fr', false);
        $response->assertRedirect('/fr/middleware');
        $this->assertEquals('fr', app()->getLocale());

        $response = $this->withUnencryptedCookie('locale', 'fr')->get('/middleware');
        $response->assertRedirect('/fr/middleware');
        $this->assertEquals('fr', app()->getLocale());

        $this->withUnencryptedCookie('locale', '');

        $response = $this->get('/middleware', ['Accept-Language' => 'da, en-gb;q=0.8, en;q=0.7']);
        $response->assertStatus(200);
        $this->assertEquals('en', app()->getLocale());

        $this->withUnencryptedCookie('locale', '');

        $response = $this->get('/fr/middleware', ['Accept-Language' => 'fr-FR']);
        $response->assertSuccessful();
        $this->assertEquals('fr', app()->getLocale());

        $this->withUnencryptedCookie('locale', '');

        $response = $this->get('/middleware', ['Accept-Language' => 'de-DE,de;q=0.8,en-US;q=0.6,en;q=0.4']);
        $response->assertRedirect('http://localhost.de/middleware');
        $this->assertEquals('de', app()->getLocale());


    }


}
