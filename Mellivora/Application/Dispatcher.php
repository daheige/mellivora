<?php

namespace Mellivora\Application;

use Mellivora\Support\Arr;
use Mellivora\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\NotFoundException;

/**
 * 一个通用的路由分发器
 *
 * 模拟实现了 mvc 模式下的 controller 分发工作
 */
class Dispatcher
{

    /**
     * @var \Mellivora\Application\Container
     */
    protected $container;

    /**
     * @param \Mellivora\Application\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * 刷新 container 中注册的 request/response 组件
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     */
    protected function refreshContainer(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) {
        // 需要在 container 中删除 request/response
        unset($this->container['request'], $this->container['response']);

        // 重新指定 request/response
        $this->container['request']  = $request;
        $this->container['response'] = $response;
    }

    /**
     * 检测 controller 的 class 类型
     *
     * @param  array                                                         $args
     * @throws \Slim\Exception\NotFoundException|\UnexpectedValueException
     * @return string
     */
    protected function detectControllerClass(array $args)
    {
        $class = str_replace('\\\\', '\\', sprintf(
            '\%s\%s\%sController',
            $args['namespace'],
            Str::studly($args['module']),
            Str::studly($args['controller'])
        ));

        if (!class_exists($class)) {
            throw new NotFoundException(
                $this->container['request'], $this->container['response']);
        }

        // controller 类型检测
        if (!is_subclass_of($class, Controller::class)) {
            throw new UnexpectedValueException(
                $class . ' must return instance of ' . Controller::class);
        }

        return $class;
    }

    /**
     * Invoke a route callable with request, response, and all route parameters
     * as an array of arguments.
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request
     * @param  \Psr\Http\Message\ResponseInterface      $response
     * @param  array                                    $args
     * @return mixed
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $this->refreshContainer($request, $response);

        // 检测，并实例化 controller
        $class   = $this->detectControllerClass($args);
        $handler = new $class($this->container);

        try {
            /**
             * 调用初始化方法，当初始化为 false 时
             * 中断 action 的执行，并返回 response 实例
             */
            if (method_exists($handler, 'initialize') && $handler->initialize() === false) {
                return $response;
            }

            $method = $args['action'] . 'Action';

            // 移除不需要用到的值，以便 action 调用
            unset(
                $args['namespace'],
                $args['module'],
                $args['controller'],
                $args['action']
            );

            // call action
            $return = $handler->$method(...array_values($args));
        } catch (\Exception $e) {
            /**
             * 当 controller 中存在 exceptionHandler 方法时
             * 调用该方法来对异常进行统一处理
             */
            if (method_exists($handler, 'exceptionHandler')) {
                $return = $handler->exceptionHandler($e);
            } else {
                throw $e;
            }
        }

        /**
         * 根据 return 的结果进行 response 格式化处理
         */
        if (is_array($return)) {
            $response = $response->withJson($return);
        } elseif (!$return instanceof ResponseInterface) {
            $response = $response->write((string) $return);
        } else {
            $response = $return;
        }

        /**
         * 当 controller 中存在 finalize 方法时
         * 调用该方法，对响应结果进行再处理
         * 如果该方法 return 返回一个 response 的结果
         * 则使用 response 做为最终响应结果
         */
        if (method_exists($handler, 'finalize')) {
            $finalize = $handler->finalize($response);
            if ($finalize instanceof ResponseInterface) {
                $response = $finalize;
            }
        }

        return $response;
    }
}
