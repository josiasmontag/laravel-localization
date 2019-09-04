<?php

namespace Lunaweb\Localization\Tests;


use Mockery as m;
use Illuminate\Foundation\Testing\TestCase;
use Orchestra\Testbench\Concerns\CreatesApplication;


class MiddlewareTest extends TestCase
{

    use CreatesApplication, EnvironmentSetUp;


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
        $response->assertSessionHas('locale', 'en');

        $this->flushSession();

        $response = $this->get('/fr/middleware', ['Accept-Language' => null]);
        $response->assertStatus(200);
        $this->assertEquals('fr', app()->getLocale());

        $this->flushSession();

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

        $this->flushSession();

        $response = $this->get('/fr/middleware?hl=th');
        $response->assertRedirect('/th/middleware');
        $this->assertEquals('th', app()->getLocale());


        $this->session(['locale' => 'fr']);

        $response = $this->get('/fr/middleware?hl=th');
        $response->assertRedirect('/th/middleware');
        $this->assertEquals('th', app()->getLocale());

        $this->session(['locale' => 'fr']);

        $response = $this->get('/fr/middleware?hl=de');
        $response->assertRedirect('http://localhost.de/middleware');
        $this->assertEquals('de', app()->getLocale());


        $this->flushSession();

        $response = $this->get('/th/middleware?hl=th');
        $response->assertRedirect('/th/middleware');
        $this->assertEquals('th', app()->getLocale());


    }


    public function testMiddlewareSession()
    {


        $this->createRoutes();

        $this->session(['locale' => 'th']);

        $response = $this->get('/middleware');
        $response->assertRedirect('/th/middleware');
        $this->assertEquals('th', app()->getLocale());

        $this->session(['locale' => 'en']);

        $response = $this->get('/fr/middleware');
        $response->assertRedirect('/middleware');
        $this->assertEquals('en', app()->getLocale());

        $this->session(['locale' => 'de']);

        $response = $this->get('/fr/middleware');
        $response->assertRedirect('http://localhost.de/middleware');
        $this->assertEquals('de', app()->getLocale());

    }


    public function testMiddlewareLanguageHeaderDetection()
    {

        $this->createRoutes();

        $response = $this->get('/middleware', ['Accept-Language' => 'fr-FR']);
        $response->assertRedirect('/fr/middleware');
        $this->assertEquals('fr', app()->getLocale());

        $response = $this->get('/middleware');
        $response->assertRedirect('/fr/middleware');
        $this->assertEquals('fr', app()->getLocale());

        $this->flushSession();

        $response = $this->get('/middleware', ['Accept-Language' => 'da, en-gb;q=0.8, en;q=0.7']);
        $response->assertStatus(200);
        $this->assertEquals('en', app()->getLocale());

        $this->flushSession();

        $response = $this->get('/fr/middleware', ['Accept-Language' => 'fr-FR']);
        $response->assertSuccessful();
        $this->assertEquals('fr', app()->getLocale());

        $this->flushSession();

        $response = $this->get('/middleware', ['Accept-Language' => 'de-DE,de;q=0.8,en-US;q=0.6,en;q=0.4']);
        $response->assertRedirect('http://localhost.de/middleware');
        $this->assertEquals('de', app()->getLocale());


    }


}
