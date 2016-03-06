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
use Docker\Manager\ContainerManager;

/**
 * Docker container wrapper class
 */
class ContainerInfos
{
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

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
        $assignation['ports'] = $this->container->getConfig()->getExposedPorts();
        $this->getLogsAssignation($manager, $assignation);

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
     *
     * @param  ContainerManager $manager      [description]
     * @param  array            &$assignation [description]
     */
    protected function getLogsAssignation(ContainerManager $manager, array &$assignation)
    {
        try {
            $assignation['logs'] =  $manager->logs($this->container->getId(), [
                'tail' => '100',
                'timestamps' => true,
            ]);
        } catch (\Http\Client\Plugin\Exception\ClientErrorException $e) {
            $assignation['logs'] = [$e->getMessage()];
        }
    }
}
