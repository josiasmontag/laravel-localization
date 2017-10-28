<?php
namespace Lunaweb\Localization\Tests;



use Mockery as m;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase;



class LocalizationTest extends TestCase
{

    use EnvironmentSetUp;



    public function tearDown()
    {
        m::close();
    }

    public function setUp()
    {
        parent::setUp();

    }




    public function testRouteCreation()
    {


        $this->createRoutes();


        $this->assertTrue(Route::has('th.index'));
        $this->assertTrue(Route::has('fr.index'));
        $this->assertTrue(Route::has('de.index'));
        $this->assertTrue(Route::has('index'));
        $this->assertFalse(Route::has('en.index'));

        $response = $this->get('/');
        $response->assertStatus(200);

        $response = $this->get('/en');
        $response->assertStatus(404);

        $response = $this->get('/th');
        $response->assertStatus(200);

        $response = $this->get('/fr');
        $response->assertStatus(200);

        $response = $this->get('/page');
        $response->assertStatus(200);

        $response = $this->get('/en/page');
        $response->assertStatus(404);

        $response = $this->get('/fr/page');
        $response->assertStatus(200);


        $response = $this->get('http://localhost.de/page');
        $response->assertStatus(200);

    }



    public function testRouteCreationWihtoutHidingDefaultLocale()
    {

        config(['localization.hide_default_locale_in_url' => false]);

        $this->createRoutes();

        $this->assertTrue(Route::has('th.index'));
        $this->assertTrue(Route::has('fr.index'));
        $this->assertTrue(Route::has('de.index'));
        $this->assertTrue(Route::has('index'));
        $this->assertFalse(Route::has('en.index'));



        $response = $this->get('/');
        $response->assertStatus(404);

        $response = $this->get('/en');
        $response->assertStatus(200);

        $response = $this->get('/th');
        $response->assertStatus(200);

        $response = $this->get('/fr');
        $response->assertStatus(200);

        $response = $this->get('/page');
        $response->assertStatus(404);

        $response = $this->get('/en/page');
        $response->assertStatus(200);

        $response = $this->get('/fr/page');
        $response->assertStatus(200);

        $response = $this->get('http://localhost.de/page');
        $response->assertStatus(200);

    }

    public function testGetLocaleUrl()
    {


        $this->createRoutes();

        $this->get('/');
        $this->assertEquals('http://localhost/fr', app('localization')->getLocaleUrl('fr'));
        $this->assertEquals('http://localhost/th', app('localization')->getLocaleUrl('th'));
        $this->assertEquals('http://localhost', app('localization')->getLocaleUrl('en'));
        $this->assertEquals('http://localhost.de', app('localization')->getLocaleUrl('de'));


        $this->get('/page');
        $this->assertEquals('http://localhost/fr/page', app('localization')->getLocaleUrl('fr'));
        $this->assertEquals('http://localhost/th/page', app('localization')->getLocaleUrl('th'));
        $this->assertEquals('http://localhost/page', app('localization')->getLocaleUrl('en'));
        $this->assertEquals('http://localhost.de/page', app('localization')->getLocaleUrl('de'));


        $this->get('/?id=1');
        $this->assertEquals('http://localhost/fr?id=1', app('localization')->getLocaleUrl('fr'));
        $this->assertEquals('http://localhost/th?id=1', app('localization')->getLocaleUrl('th'));
        $this->assertEquals('http://localhost?id=1', app('localization')->getLocaleUrl('en'));
        $this->assertEquals('http://localhost.de?id=1', app('localization')->getLocaleUrl('de'));


        $this->get('/parm/123?id=2');
        $this->assertEquals('http://localhost/fr/parm/123?id=2', app('localization')->getLocaleUrl('fr'));
        $this->assertEquals('http://localhost/th/parm/123?id=2', app('localization')->getLocaleUrl('th'));
        $this->assertEquals('http://localhost/parm/123?id=2', app('localization')->getLocaleUrl('en'));
        $this->assertEquals('http://localhost.de/parm/123?id=2', app('localization')->getLocaleUrl('de'));


        $this->get('/nonlocalized?id=3');
        $this->assertEquals('http://localhost/nonlocalized?id=3&hl=fr', app('localization')->getLocaleUrl('fr'));
        $this->assertEquals('http://localhost/nonlocalized?id=3&hl=th', app('localization')->getLocaleUrl('th'));
        $this->assertEquals('http://localhost/nonlocalized?id=3&hl=en', app('localization')->getLocaleUrl('en'));
        $this->assertEquals('http://localhost/nonlocalized?id=3&hl=de', app('localization')->getLocaleUrl('de'));

    }


    public function testGetLocaleUrlWithNonDefaultLocale()
    {
        $this->createRoutes();

        app()->setLocale('fr');


        $this->get('/');
        $this->assertEquals('http://localhost/fr', app('localization')->getLocaleUrl('fr'));
        $this->assertEquals('http://localhost/th', app('localization')->getLocaleUrl('th'));
        $this->assertEquals('http://localhost', app('localization')->getLocaleUrl('en'));
        $this->assertEquals('http://localhost.de', app('localization')->getLocaleUrl('de'));



        $this->get('/fr');
        $this->assertEquals('http://localhost/fr', app('localization')->getLocaleUrl('fr'));
        $this->assertEquals('http://localhost/th', app('localization')->getLocaleUrl('th'));
        $this->assertEquals('http://localhost', app('localization')->getLocaleUrl('en'));
        $this->assertEquals('http://localhost.de', app('localization')->getLocaleUrl('de'));


    }


}
