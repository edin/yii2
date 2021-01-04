<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\bindings;

use Yii;
use yii\base\InlineAction;
use yii\bindings\ActionParameterBinder;
use yii\bindings\binders\ActiveRecordBinder;
use yii\bindings\BindingContext;
use yii\bindings\ModelBinderInterface;
use yii\console\Application;
use yiiunit\framework\bindings\mocks\ActionBindingController;
use yiiunit\framework\bindings\mocks\Post;
use yiiunit\TestCase;

class ActiveRecordBinderTest extends TestCase
{
    /**
     * @var ActionParameterBinder
     */
    private $parameterBinder;

    /**
     * @var ModelBinderInterface
     */
    private $modelBinder;

    /**
     * @var BindingContext
     */
    private $context = null;

    /**
     * @var ActionBindingController
     */
    private $controller = null;

    protected function setUp()
    {
        parent::setUp();
        $this->parameterBinder = new ActionParameterBinder();
        $this->modelBinder = new ActiveRecordBinder();

        $module = new \yii\base\Module('fake', new Application(['id' => 'app',  'basePath' => __DIR__,]));
        $module->set(yii\web\Request::class, ['class' => yii\web\Request::class]);
        $this->controller = new ActionBindingController('binding', $module);

        $this->mockWebApplication(['controller' => $this->controller]);
    }

    public function testActiveRecordBinderFindOne()
    {
        $action = new InlineAction("action", $this->controller, "actionActiveRecord");

        $result = $this->parameterBinder->bindActionParams($action, ["id" => 100]);
        $args   = $result->arguments;

        /**
         * @var Post
         */
        $instance = $args["model"];

        $this->assertNotNull($instance);
        $this->assertInstanceOf(Post::class, $instance);
        $this->assertSame(true, $instance->findOneCalled);
        $this->assertSame(100, $instance->arguments['findOne']["condition"]);
    }

    public function testActiveRecordBinderSetAttributes()
    {
        $action = new InlineAction("action", $this->controller, "actionActiveRecord");

        $id = 100;

        $condition = [
            "condition" => $id
        ];

        $values = [
            "values" => [
                "title" => "title",
                "content" => "some content"
            ],
            "safeOnly" => true
        ];

        $_SERVER['REQUEST_METHOD'] = "POST";
        Yii::$app->request->setBodyParams($values["values"]);

        $result = $this->parameterBinder->bindActionParams($action, ["id" => $id]);
        $args   = $result->arguments;

        /**
         * @var Post
         */
        $instance = $args["model"];

        $this->assertNotNull($instance);
        $this->assertInstanceOf(Post::class, $instance);
        $this->assertSame(true, $instance->findOneCalled);
        $this->assertSame(true, $instance->setAttributesCalled);
        $this->assertSame($values, $instance->arguments["setAttributes"]);
        $this->assertSame($condition, $instance->arguments["findOne"]);
    }
}
