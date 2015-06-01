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

use Docker\Container;
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
        if ($this->container->exists()) {
            $data = $this->container->getRuntimeInformations();
            if (isset($data['State']['Running'])) {
                return $data['State']['Running'];
            }
        }

        return false;
    }

    public function getDetailsAssignation(ContainerManager $manager, array &$assignation)
    {
        $manager->inspect($this->container);
        $assignation['container'] = $this->container;
        $runtimeInformations = $this->container->getRuntimeInformations();

        if (count($runtimeInformations['HostConfig']['Links']) > 0) {
            $assignation['linkedContainers'] = $runtimeInformations['HostConfig']['Links'];
        }
        if (count($runtimeInformations['HostConfig']['VolumesFrom']) > 0) {
            $assignation['volumesFromContainers'] = $runtimeInformations['HostConfig']['VolumesFrom'];
        }
        if (isset($runtimeInformations['HostConfig']['RestartPolicy'])) {
            $assignation['restartPolicy'] = $runtimeInformations['HostConfig']['RestartPolicy'];
        }

        if (isset($runtimeInformations['State']['FinishedAt'])) {
            $date = explode('.', $runtimeInformations['State']['FinishedAt']);
            if (count($date) > 1) {
                $assignation['FinishedAt'] = new \DateTime($date[0] . 'Z');
            } else {
                $assignation['FinishedAt'] = new \DateTime($runtimeInformations['State']['FinishedAt']);
            }
        }
        if (isset($runtimeInformations['State']['StartedAt'])) {
            $date = explode('.', $runtimeInformations['State']['StartedAt']);
            if (count($date) > 1) {
                $assignation['StartedAt'] = new \DateTime($date[0] . 'Z');
            } else {
                $assignation['StartedAt'] = new \DateTime($runtimeInformations['State']['StartedAt']);
            }

            $now = new \DateTime();
            $assignation['RunningAge'] = $now->diff($assignation['StartedAt'], true);
        }
        if (isset($runtimeInformations['Created'])) {
            $date = explode('.', $runtimeInformations['Created']);
            if (count($date) > 1) {
                $assignation['Created'] = new \DateTime($date[0] . 'Z');
            } else {
                $assignation['Created'] = new \DateTime($runtimeInformations['Created']);
            }

            $now = new \DateTime();
            $assignation['Age'] = $now->diff($assignation['Created'], true);
        }

        $assignation['logs'] =  $manager->logs($this->container, false, true, true, false, 100);

        return $assignation;
    }
}
