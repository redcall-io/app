<?php

namespace Bundles\GoogleTaskBundle\Service;

use Bundles\GoogleTaskBundle\Bag\TaskBag;
use Bundles\GoogleTaskBundle\Enum\Process;
use Bundles\GoogleTaskBundle\Security\Signer;
use Google\Cloud\Tasks\V2\AppEngineHttpRequest;
use Google\Cloud\Tasks\V2\CloudTasksClient;
use Google\Cloud\Tasks\V2\HttpMethod;
use Google\Cloud\Tasks\V2\HttpRequest;
use Google\Cloud\Tasks\V2\Task;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;

class TaskSender
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var TaskBag
     */
    private $taskBag;

    /**
     * @var CloudTasksClient
     */
    private $taskClient;

    public function __construct(RouterInterface $router,
        KernelInterface $kernel,
        TaskBag $taskBag)
    {
        $this->router  = $router;
        $this->kernel  = $kernel;
        $this->taskBag = $taskBag;
    }

    public function fire(string $name, array $context = [], ?Process $process = null)
    {
        if (null === $process) {
            if ('prod' === $this->kernel->getEnvironment()) {
                $process = Process::APP_ENGINE();
            } else {
                $process = Process::HTTP();
            }
        }

        $payload = [
            'name'      => $name,
            'context'   => $context,
            'signature' => Signer::sign($name, $context),
        ];

        switch ($process) {
            case Process::APP_ENGINE():
                $cloudTask = $this->createAppEngineTask($payload);
                break;
            case Process::HTTP():
                $cloudTask = $this->createHttpTask($payload);
                break;
        }

        $this->getClient()->createTask(
            $this->taskBag->getTask($name)->getQueueName(),
            $cloudTask
        );
    }

    private function createAppEngineTask(array $payload) : Task
    {
        $httpRequest = new AppEngineHttpRequest();

        $uri = $this->router->generate('google_task_receiver');

        $httpRequest->setRelativeUri($uri)
                    ->setHttpMethod(HttpMethod::POST)
                    ->setBody($payload);

        $task = new Task();
        $task->setAppEngineHttpRequest($httpRequest);

        return $task;
    }

    private function createHttpTask(array $payload) : Task
    {
        $httpRequest = new HttpRequest();

        $url = $this->router->generate('google_task_receiver', [], RouterInterface::ABSOLUTE_URL);

        $httpRequest->setUrl($url)
                    ->setHttpMethod(HttpMethod::POST)
                    ->setBody($payload);

        $task = new Task();
        $task->setHttpRequest($httpRequest);

        return $task;
    }

    private function getClient() : CloudTasksClient
    {
        if (!$this->taskClient) {
            $this->taskClient = new CloudTasksClient();
        }

        return $this->taskClient;
    }
}