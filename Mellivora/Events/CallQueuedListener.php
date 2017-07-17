<?php

namespace Mellivora\Events;

use Mellivora\Container\Container;
use Mellivora\Queue\InteractsWithQueue;
use Mellivora\Support\Contracts\Queue\Job;
use Mellivora\Support\Contracts\Queue\ShouldQueue;

class CallQueuedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The listener class name.
     *
     * @var string
     */
    public $class;

    /**
     * The listener method.
     *
     * @var string
     */
    public $method;

    /**
     * The data to be passed to the listener.
     *
     * @var array
     */
    public $data;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout;

    /**
     * Create a new job instance.
     *
     * @param  string $class
     * @param  string $method
     * @param  array  $data
     * @return void
     */
    public function __construct($class, $method, $data)
    {
        $this->data   = $data;
        $this->class  = $class;
        $this->method = $method;
    }

    /**
     * Handle the queued job.
     *
     * @param  \Mellivora\Container\Container $container
     * @return void
     */
    public function handle(Container $container)
    {
        $this->prepareData();

        $handler = $this->setJobInstanceIfNecessary(
            $this->job, $container->make($this->class)
        );

        call_user_func_array(
            [$handler, $this->method], $this->data
        );
    }

    /**
     * Set the job instance of the given class if necessary.
     *
     * @param  \Mellivora\Support\Contracts\Queue\Job $job
     * @param  mixed                                  $instance
     * @return mixed
     */
    protected function setJobInstanceIfNecessary(Job $job, $instance)
    {
        if (in_array(InteractsWithQueue::class, class_uses_recursive(get_class($instance)))) {
            $instance->setJob($job);
        }

        return $instance;
    }

    /**
     * Call the failed method on the job instance.
     *
     * The event instance and the exception will be passed.
     *
     * @param  \Exception $e
     * @return void
     */
    public function failed($e)
    {
        $this->prepareData();

        $handler = Container::getInstance()->make($this->class);

        $parameters = array_merge($this->data, [$e]);

        if (method_exists($handler, 'failed')) {
            call_user_func_array([$handler, 'failed'], $parameters);
        }
    }

    /**
     * Unserialize the data if needed.
     *
     * @return void
     */
    protected function prepareData()
    {
        if (is_string($this->data)) {
            $this->data = unserialize($this->data);
        }
    }

    /**
     * Get the display name for the queued job.
     *
     * @return string
     */
    public function displayName()
    {
        return $this->class;
    }
}