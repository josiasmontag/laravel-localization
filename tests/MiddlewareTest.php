<?php
namespace Lunaweb\Localization\Tests;



use Mockery as m;
use Illuminate\Support\Facades\Route;
use  Illuminate\Foundation\Testing\TestCase;
use Orchestra\Testbench\Traits\CreatesApplication;


class MiddlewareTest extends TestCase
{

    use CreatesApplication, EnvironmentSetUp;



    public function tearDown()
    {
        m::close();
    }

    public function setUp()
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

    }


    public function testMiddlewareLanguageHeaderDetection() {

        $this->createRoutes();

        $response = $this->get('/middleware', ['Accept-Language' => 'fr-FR']);
        $response->assertRedirect('/fr/middleware');
        $this->assertEquals('fr', app()->getLocale());

        $response = $this->get('/middleware');
        $response->assertRedirect('/fr/middleware');
        $this->assertEquals('fr', app()->getLocale());

        $this->flushSession();

        $response = $this->get('/middleware', ['Accept-Language' => 'de-DE,de;q=0.8,en-US;q=0.6,en;q=0.4']);
        $response->assertStatus(200);
        $this->assertEquals('en', app()->getLocale());

        $this->flushSession();

        $response = $this->get('/fr/middleware', ['Accept-Language' => 'fr-FR']);
        $response->assertSuccessful();
        $this->assertEquals('fr', app()->getLocale());



    }




}
