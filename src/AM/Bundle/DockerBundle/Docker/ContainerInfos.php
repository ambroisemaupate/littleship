<?php
/**
 * Copyright © 2015, Ambroise Maupate
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @file Container.php
 * @author Ambroise Maupate
 */
namespace AM\Bundle\DockerBundle\Docker;

use Docker\API\Model\Container;
use Docker\API\Model\ContainerConfig;
use Docker\API\Model\HostConfig;
use Docker\API\Model\RestartPolicy;
use Docker\Manager\ContainerManager;
use Http\Client\Common\Exception\ClientErrorException;

/**
 * Docker container wrapper class
 */
class ContainerInfos
{
    protected $container;

    /**
     * ContainerInfos constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return bool
     */
    public function isRunning()
    {
        if ($this->container) {
            $state = $this->container->getState();
            if (null !== $state) {
                return $state->getRunning();
            }
        }

        return false;
    }

    /**
     * @param  array  $data
     * @return ContainerConfig
     */
    public static function getContainerConfigFromData(array $data)
    {
        $restartPolicy = new RestartPolicy();
        $restartPolicy->setName($data['restart_policy']);
        $restartPolicy->setMaximumRetryCount(0);

        $hostConfig = new HostConfig();
        $hostConfig->setPublishAllPorts($data['publish_ports']);
        $hostConfig->setRestartPolicy($restartPolicy);
        if (isset($data['links']) &&
            count($data['links']) > 0) {
            $hostConfig->setLinks($data['links']);
        }
        if (isset($data['volumes_from']) && count($data['volumes_from']) > 0) {
            $hostConfig->setVolumesFrom($data['volumes_from']);
        }

        $containerConfig = new ContainerConfig();
        $containerConfig->setExposedPorts(static::getExposedPorts($data['ports']));
        $containerConfig->setImage($data['image']);
        $containerConfig->setNames([$data['name']]);
        $containerConfig->setAttachStdin(false);
        $containerConfig->setAttachStdout(false);
        $containerConfig->setAttachStderr(false);
        $containerConfig->setHostConfig($hostConfig);

        // Test if env is set and not empty before setting it
        if (isset($data['env']) && count($data['env']) > 0) {
            $containerConfig->setEnv($data['env']);
        }

        return $containerConfig;
    }

    /**
     * @param array $ports
     * @return \ArrayObject
     */
    protected static function getExposedPorts(array &$ports)
    {
        $exposedPorts = [];
        foreach ($ports as $port) {
            $exposedPorts[$port] = [];
        }
        return new \ArrayObject($exposedPorts);
    }

    /**
     * @param ContainerManager $manager
     * @param array $assignation
     * @return array
     */
    public function getDetailsAssignation(ContainerManager $manager, array &$assignation)
    {
        $assignation['container'] = $this->container;
        $state = $this->container->getState();

        $this->getHostConfigAssignation($assignation);

        if (false === $state->getRunning()) {
            $date = explode('.', $state->getFinishedAt());
            if (count($date) > 1) {
                $assignation['FinishedAt'] = new \DateTime($date[0] . 'Z');
            } else {
                $assignation['FinishedAt'] = new \DateTime($state->getFinishedAt());
            }
        }
        if (true === $state->getRunning()) {
            $date = explode('.', $state->getStartedAt());
            if (count($date) > 1) {
                $assignation['StartedAt'] = new \DateTime($date[0] . 'Z');
            } else {
                $assignation['StartedAt'] = new \DateTime($state->getStartedAt());
            }

            $now = new \DateTime();
            $assignation['RunningAge'] = $now->diff($assignation['StartedAt'], true);
        }

        $date = explode('.', $this->container->getCreated());
        if (count($date) > 1) {
            $assignation['Created'] = new \DateTime($date[0] . 'Z');
        } else {
            $assignation['Created'] = new \DateTime($this->container->getCreated());
        }

        $now = new \DateTime();
        $assignation['Age'] = $now->diff($assignation['Created'], true);
        $assignation['image'] = $this->container->getConfig()->getImage();
        $assignation['running'] = $state->getRunning();

        if ($state->getRunning()) {
            $assignation['ports'] = $this->container->getNetworkSettings()->getPorts();
        } else {
            $assignation['ports'] = $this->container->getHostConfig()->getPortBindings();
        }

        //$this->getLogsAssignation($manager, $assignation);

        return $assignation;
    }
    /**
     * @param  array  &$assignation [description]
     */
    protected function getHostConfigAssignation(array &$assignation)
    {
        $hostConfig = $this->container->getHostConfig();

        if (null !== $hostConfig) {
            if (count($hostConfig->getLinks()) > 0) {
                $assignation['linkedContainers'] = $hostConfig->getLinks();
            }
            if (count($hostConfig->getVolumesFrom()) > 0) {
                $assignation['volumesFromContainers'] = $hostConfig->getVolumesFrom();
            }
            $assignation['restartPolicy'] = $hostConfig->getRestartPolicy();
        }
    }

    /**
     * @param  ContainerManager $manager      [description]
     * @param  array            &$assignation [description]
     */
    protected function getLogsAssignation(ContainerManager $manager, array &$assignation)
    {
        try {
            $assignation['logs'] =  $manager->logs($this->container->getId(), [
                'tail' => '100',
                'stdout' => true,
                'stderr' => true,
                'timestamps' => true,
            ]);
        } catch (ClientErrorException $e) {
            $assignation['logs'] = [$e->getResponse()->getBody()->getContents()];
        }
    }
}
