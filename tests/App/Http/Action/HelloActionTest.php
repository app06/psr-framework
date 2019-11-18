<?php

namespace Tests\App\Http\Action;

use App\Http\Action\HelloAction;
use Framework\Http\Router\Router;
use Framework\Template\PhpRenderer;
use Framework\Template\TemplateRenderer;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequest;

class HelloActionTest extends TestCase
{
    private $renderer;
    private $router;
    protected function setUp(): void
    {
        parent::setUp();
        $this->router = $this->createMock(Router::class);
        $this->renderer = new PhpRenderer('templates', $this->router);
    }

    public function testGuest()
    {
        $action = new HelloAction($this->renderer);
        $response = $action();

        self::assertEquals(200, $response->getStatusCode());
        self::assertStringContainsString('Hello!', $response->getBody()->getContents());
    }
}